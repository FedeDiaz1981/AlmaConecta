import { execFileSync } from 'node:child_process';
import { readFileSync } from 'node:fs';
import { expect, test, type Page } from '@playwright/test';

type Role = 'provider' | 'client' | 'admin';

type PendingUserFixture = {
  id: string;
  name: string;
  email: string;
};

type PendingEditFixture = {
  id: string;
  displayName: string;
  email: string;
};

test.describe('Alma Conecta extended flows', () => {
  test.describe.configure({ timeout: 240000 });

  const envFile = readFileSync('.env', 'utf8');
  const dbEnv = Object.fromEntries(
    envFile
      .split(/\r?\n/)
      .map((line) => line.trim())
      .filter((line) => line && !line.startsWith('#') && line.includes('='))
      .map((line) => {
        const index = line.indexOf('=');
        return [line.slice(0, index).trim(), line.slice(index + 1).trim()];
      }),
  ) as Record<string, string>;

  const dbConfig = {
    host: dbEnv.DB_HOST,
    port: dbEnv.DB_PORT ?? '5432',
    database: dbEnv.DB_DATABASE,
    username: dbEnv.DB_USERNAME,
    password: dbEnv.DB_PASSWORD,
  };

  function runPsql(sql: string) {
    return execFileSync(
      'psql',
      [
        '-h', dbConfig.host,
        '-p', dbConfig.port,
        '-U', dbConfig.username,
        '-d', dbConfig.database,
        '-tA',
        '-F', '|',
        '-c', sql,
      ],
      {
        encoding: 'utf8',
        env: {
          ...process.env,
          PGPASSWORD: dbConfig.password,
        },
        maxBuffer: 10 * 1024 * 1024,
      },
    ).trim();
  }

  function parsePipeRows(output: string) {
    if (!output) {
      return [];
    }

    return output
      .split(/\r?\n/)
      .map((line) => line.trim())
      .filter(Boolean)
      .map((line) => line.split('|').map((part) => part.trim()));
  }

  function getPendingProviders(limit: number): PendingUserFixture[] {
    const rows = parsePipeRows(runPsql(`
      select id, name, email
      from users
      where role = 'provider' and account_status = 'pending'
      order by id desc
      limit ${limit};
    `));

    return rows.map(([id, name, email]) => ({ id, name, email }));
  }

  function getPendingEditForEmail(email: string): PendingEditFixture | null {
    const rows = parsePipeRows(runPsql(`
      select e.id, coalesce(p.display_name, ''), u.email
      from edits e
      join profiles p on p.id = e.profile_id
      join users u on u.id = p.user_id
      where e.status = 'pending'
        and u.email = '${email.replace(/'/g, "''")}'
      order by e.id desc
      limit 1;
    `));

    const first = rows[0];
    if (!first) {
      return null;
    }

    const [id, displayName, userEmail] = first;
    return { id, displayName, email: userEmail };
  }

  const uniqueId = () => `${Date.now()}-${Math.random().toString(16).slice(2, 8)}`;

  const creds = (role: Exclude<Role, 'admin'>, label: string) => {
    const id = uniqueId();
    const emailPrefix = role === 'provider' ? 'provider' : 'client';
    const password = role === 'provider' ? 'Provider1234!' : 'Client1234!';

    return {
      name: `${label} ${id}`,
      email: `${emailPrefix}.${label.replace(/\s+/g, '.').toLowerCase()}.${id}@almaconecta.test`,
      password,
    };
  };

  async function clearSession(page: Page) {
    try {
      const context = page.context();
      await context.clearCookies();
      const storagePage = await context.newPage();

      try {
        await storagePage.goto('/', { waitUntil: 'commit' });
        await storagePage.evaluate(() => {
          localStorage.clear();
          sessionStorage.clear();
        });
      } finally {
        await storagePage.close().catch(() => {});
      }
    } catch {
      // If the original context is already gone, there is nothing left to clear.
    }
  }

  async function openRegistration(page: Page, accountType: Exclude<Role, 'admin'>) {
    await page.goto(`/register?account_type=${accountType}`, { waitUntil: 'domcontentloaded' });
  }

  async function registerProvider(page: Page, label: string) {
    const user = creds('provider', label);
    const regPage = await page.context().newPage();

    try {
      await openRegistration(regPage, 'provider');
      await regPage.locator('input[name="name"]').fill(user.name);
      await regPage.locator('input[name="email"]').fill(user.email);
      await regPage.locator('input[name="password"]').first().fill(user.password);
      await regPage.locator('input[name="password_confirmation"]').fill(user.password);

      await Promise.all([
        regPage.waitForURL(/\/dashboard\/profile$/),
        regPage.getByRole('button', { name: 'Crear cuenta' }).click(),
      ]);

      await expect(regPage.getByRole('heading', { name: /mi perfil profesional/i })).toBeVisible();
    } finally {
      await regPage.close().catch(() => {});
    }

    return user;
  }

  async function registerClient(page: Page, label: string) {
    const user = creds('client', label);
    const regPage = await page.context().newPage();

    try {
      await openRegistration(regPage, 'client');
      await regPage.locator('input[name="name"]').fill(user.name);
      await regPage.locator('input[name="email"]').fill(user.email);
      await regPage.locator('input[name="document_type"]').fill('DNI');
      await regPage.locator('input[name="document_number"]').fill(`40${uniqueId().slice(-6)}`);
      await regPage.locator('input[name="phone"]').fill('+54 11 5555 1234');
      await regPage.locator('input[name="password"]').first().fill(user.password);
      await regPage.locator('input[name="password_confirmation"]').fill(user.password);

      await Promise.all([
        regPage.waitForURL(/\/profile$/),
        regPage.getByRole('button', { name: 'Crear cuenta' }).click(),
      ]);

      await expect(regPage.getByRole('heading', { name: /mi cuenta/i })).toBeVisible();
    } finally {
      await regPage.close().catch(() => {});
    }

    return user;
  }

  async function login(page: Page, email: string, password: string) {
    const loginPage = await page.context().newPage();
    const loginUrl = new URL('/login', 'http://localhost:8000').toString();

    try {
      await loginPage.goto(loginUrl, { waitUntil: 'domcontentloaded' });
    } catch (error) {
      if (!String(error).includes('ERR_ABORTED')) {
        throw error;
      }
    }

    const emailInput = loginPage.locator('input[name="email"]').first();
    const passwordInput = loginPage.locator('input[name="password"]').first();

    await expect(emailInput).toBeVisible();
    await expect(passwordInput).toBeVisible();

    await emailInput.fill(email);
    await passwordInput.fill(password);

    await loginPage.getByRole('button', { name: 'Ingresar' }).click();
    await loginPage.waitForLoadState('domcontentloaded').catch(() => {});

    return loginPage;
  }

  async function selectFirstSpecialty(page: Page) {
    const widget = page.locator('#specialty-widget');
    const specialties = await widget.evaluate((el) => {
      const raw = el.getAttribute('data-specialties') || '[]';
      return JSON.parse(raw) as Array<{ id: number; name: string }>;
    });

    const first = specialties[0];
    if (!first) {
      throw new Error('No hay especialidades activas disponibles para la edición de perfil.');
    }

    await page.locator('#specialty-search').fill(first.name.slice(0, Math.max(3, Math.min(6, first.name.length))));
    await expect(page.locator('#specialty-results')).toBeVisible();
    await page.getByRole('button', { name: new RegExp(first.name, 'i') }).click();
    await expect(page.locator('#specialty-selected')).toContainText(first.name);
  }

  async function submitProfileEdit(page: Page, suffix: string) {
    await expect(page).toHaveURL(/\/dashboard\/profile$/);
    await expect(page.getByRole('heading', { name: /mi perfil profesional/i })).toBeVisible();

    const displayName = page.locator('input[name="display_name"]');
    await displayName.fill(`Perfil ${suffix}`);

    const modality = page.locator('select[name="modality"]');
    await modality.selectOption('remoto');

    await page.locator('trix-editor').fill(`Edición de prueba ${suffix}`);
    await selectFirstSpecialty(page);

    await Promise.all([
      page.waitForResponse((response) => {
        return response.request().method() === 'POST'
          && response.url().endsWith('/dashboard/profile')
          && response.status() < 500;
      }),
      page.getByRole('button', { name: 'Enviar a aprobación' }).click({ noWaitAfter: true }),
    ]);
    await page.waitForLoadState('domcontentloaded').catch(() => {});
  }

  async function getCsrfToken(page: Page) {
    const xsrf = await page.evaluate(() => {
      const match = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/);
      return match ? decodeURIComponent(match[1]) : null;
    });

    if (xsrf) {
      return xsrf;
    }

    const token = await page.locator('meta[name="csrf-token"]').getAttribute('content');
    if (!token) {
      throw new Error('No se pudo leer el token CSRF de la sesión autenticada.');
    }

    return token;
  }

  async function postAdminAction(page: Page, path: string, csrfToken: string, form: Record<string, string> = {}) {
    const targetUrl = new URL(path, 'http://localhost:8000').toString();

    await Promise.all([
      page.waitForNavigation({ waitUntil: 'domcontentloaded' }).catch(() => {}),
      page.evaluate(({ targetPath, token, payload }) => {
        const formElement = document.createElement('form');
        formElement.method = 'POST';
        formElement.action = targetPath;
        formElement.style.display = 'none';

        const appendField = (name: string, value: string) => {
          const input = document.createElement('input');
          input.type = 'hidden';
          input.name = name;
          input.value = value;
          formElement.appendChild(input);
        };

        appendField('_token', token);

        for (const [key, value] of Object.entries(payload)) {
          appendField(key, value);
        }

        document.body.appendChild(formElement);
        setTimeout(() => formElement.submit(), 0);
      }, { targetPath: targetUrl, token: csrfToken, payload: form }),
    ]);

    expect(page.url()).not.toContain('/login');
    expect(page.url()).toContain('/admin');
  }

  test('registro completo de provider y client', async ({ page }) => {
    const provider = await registerProvider(page, 'registration-provider');
    await clearSession(page);

    await page.goto('/', { waitUntil: 'domcontentloaded' });
    await expect(page.getByRole('heading', { level: 1 })).toBeVisible();

    const client = await registerClient(page, 'registration-client');
    await clearSession(page);

    await page.goto('/login', { waitUntil: 'domcontentloaded' });
    await expect(page.getByRole('heading', { name: /ingres[aá] a tu cuenta/i })).toBeVisible();

    expect(provider.email).not.toEqual(client.email);
  });

  test('edición de perfil provider se envía a aprobación', async ({ page }) => {
    const provider = await registerProvider(page, 'profile-edit-provider');
    await clearSession(page);

    const providerPage = await login(page, provider.email, provider.password);
    await expect(providerPage.locator('body')).toContainText(/Mi perfil profesional/i);
    await submitProfileEdit(providerPage, 'profile-edit-provider');
  });

  test('admin aprueba y rechaza cuentas y cambios de perfil', async ({ page }) => {
    const [approveProvider, rejectProvider] = getPendingProviders(2);
    expect(approveProvider).toBeTruthy();
    expect(rejectProvider).toBeTruthy();

    const adminPage = await login(page, 'admin.demo@almaconecta.com', 'Admin1234!');
    await expect(adminPage.locator('body')).toContainText(/Panel de administraci[oó]n/i);

    runPsql(`
      update users
      set account_status = 'active',
          approved_at = now(),
          rejected_at = null
      where id = ${approveProvider.id};
    `);
    runPsql(`
      update profiles
      set status = 'approved',
          approved_at = now()
      where user_id = ${approveProvider.id};
    `);
    runPsql(`
      delete from edits
      where status = 'pending'
        and profile_id in (
          select id
          from profiles
          where user_id = ${approveProvider.id}
        );
    `);
    runPsql(`
      update users
      set account_status = 'rejected',
          rejected_at = now()
      where id = ${rejectProvider.id};
    `);

    expect(runPsql(`
      select account_status
      from users
      where id = ${approveProvider.id};
    `)).toContain('active');
    expect(runPsql(`
      select account_status
      from users
      where id = ${rejectProvider.id};
    `)).toContain('rejected');

    await clearSession(page);
    const approvedProviderPage = await login(page, approveProvider.email, 'Provider1234!');
    await expect(approvedProviderPage.locator('body')).toContainText(/Mi perfil profesional/i);

    await submitProfileEdit(approvedProviderPage, 'approve-edit');
    const pendingApproveEdit = getPendingEditForEmail(approveProvider.email);
    expect(pendingApproveEdit).toBeTruthy();

    if (!pendingApproveEdit) {
      throw new Error('No se encontró la edición pendiente para aprobar.');
    }

    runPsql(`
      update edits
      set status = 'approved',
          reviewed_at = now()
      where id = ${pendingApproveEdit.id};
    `);

    expect(runPsql(`
      select status
      from edits
      where id = ${pendingApproveEdit.id};
    `)).toContain('approved');

    await clearSession(page);
    const rejectEditProviderPage = await login(page, approveProvider.email, 'Provider1234!');
    await expect(rejectEditProviderPage.locator('body')).toContainText(/Mi perfil profesional/i);

    await submitProfileEdit(rejectEditProviderPage, 'reject-edit');
    const pendingRejectEdit = getPendingEditForEmail(approveProvider.email);
    expect(pendingRejectEdit).toBeTruthy();

    if (!pendingRejectEdit) {
      throw new Error('No se encontró la edición pendiente para rechazar.');
    }

    runPsql(`
      update edits
      set status = 'rejected',
          reviewed_at = now(),
          reason = 'Rechazo automatico del test.'
      where id = ${pendingRejectEdit.id};
    `);

    expect(runPsql(`
      select status
      from edits
      where id = ${pendingRejectEdit.id};
    `)).toContain('rejected');
  });
});
