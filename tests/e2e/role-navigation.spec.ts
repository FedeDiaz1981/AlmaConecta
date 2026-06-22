import { expect, test, type Page } from '@playwright/test';

const baseURL = process.env.PLAYWRIGHT_BASE_URL ?? 'http://localhost:8000';

const demoUsers = {
  admin: {
    email: 'admin.demo@almaconecta.com',
    password: 'Admin1234!',
  },
  provider: {
    email: 'provider.demo@almaconecta.com',
    password: 'Provider1234!',
  },
  client: {
    email: 'client.demo@almaconecta.com',
    password: 'Client1234!',
  },
} as const;

async function login(page: Page, role: keyof typeof demoUsers, expectedUrl: RegExp) {
  await page.goto('/login', { waitUntil: 'domcontentloaded' });

  const loginForm = page.locator('form').filter({
    has: page.getByRole('button', { name: 'Ingresar' }),
  }).first();

  await loginForm.locator('input[name="email"]').fill(demoUsers[role].email);
  await loginForm.locator('input[name="password"]').fill(demoUsers[role].password);

  await Promise.all([
    page.waitForURL(expectedUrl, { waitUntil: 'commit' }),
    loginForm.getByRole('button', { name: 'Ingresar' }).click(),
  ]);
}

async function apiLogin(page: Page, role: keyof typeof demoUsers) {
  await page.goto('/login', { waitUntil: 'domcontentloaded' });
  const token = await page.locator('input[name="_token"]').first().inputValue();

  const response = await page.context().request.post('/login', {
    maxRedirects: 0,
    form: {
      _token: token,
      email: demoUsers[role].email,
      password: demoUsers[role].password,
    },
  });

  if (!response.ok() && response.status() >= 500) {
    throw new Error(`Login request failed with status ${response.status()}`);
  }
}

test.describe('Alma Conecta role navigation', () => {
  test.describe.configure({ timeout: 180000 });

  test('guest recorre landing, portal, busqueda y alta de cuenta', async ({ page }) => {
    await page.goto('/', { waitUntil: 'domcontentloaded' });
    await expect(page).toHaveTitle(/Alma Conecta/i);
    await expect(page.getByRole('heading', { level: 1 })).toBeVisible();

    await page.getByRole('link', { name: 'Ir al portal' }).first().click();
    await expect(page).toHaveURL(/\/portal$/);
    await expect(page.locator('body')).toBeVisible();

    await page.goto('/', { waitUntil: 'domcontentloaded' });
    await page.getByRole('button', { name: 'Registrarse' }).first().click();
    await expect(page.getByRole('dialog', { name: /eleg[ií] cómo quer[eé]s sumarte/i })).toBeVisible();

    await page.getByRole('button', { name: /quiero publicar mi espacio/i }).click();
    await expect(page).toHaveURL(/\/register\?account_type=provider$/);
    await expect(page.getByRole('heading', { name: /cre[aá] tu cuenta/i })).toBeVisible();
    await expect(page.getByLabel('Soy profesional')).toBeVisible();

    await page.goto('/', { waitUntil: 'domcontentloaded' });
    await page.getByRole('button', { name: 'Registrarse' }).first().click();
    await page.getByRole('button', { name: /busco un profesional/i }).click();
    await expect(page).toHaveURL(/\/register\?account_type=client$/);
    await expect(page.getByLabel('Busco profesional')).toBeVisible();

    await page.goto('/search?q=terapia', { waitUntil: 'domcontentloaded' });
    await expect(page).toHaveTitle(/Resultados de b[uú]squeda/i);
    await expect(page.locator('body')).toBeVisible();

    await page.goto('/login', { waitUntil: 'domcontentloaded' });
    await expect(page.getByRole('heading', { name: /ingres[aá] a tu cuenta/i })).toBeVisible();

    await page.goto('/forgot-password', { waitUntil: 'domcontentloaded' });
    await expect(page.getByLabel(/email/i)).toBeVisible();
    await expect(page.getByRole('button', { name: /email password reset link/i })).toBeVisible();
  });

  test('admin recorre su panel completo', async ({ page }) => {
    await page.goto('/login', { waitUntil: 'domcontentloaded' });
    const token = await page.locator('input[name="_token"]').first().inputValue();

    const response = await page.context().request.post('/login', {
      maxRedirects: 0,
      form: {
        _token: token,
        email: demoUsers.admin.email,
        password: demoUsers.admin.password,
      },
    });

    expect(response.status()).toBe(302);
    expect(response.headers()['location']).toContain('/admin');

    const routes = [
      { path: '/admin/approvals', contains: 'Pendientes de aprob' },
      { path: '/admin/users', contains: 'Cuentas de usuarios' },
      { path: '/admin/edits', contains: 'Cambios pendientes' },
      { path: '/admin/reports', contains: 'Cuentas reportadas' },
      { path: '/admin/specialties', contains: 'Especialidades' },
      { path: '/admin/specialties/create', contains: 'Nueva especialidad' },
      { path: '/admin/specialties/bulk', contains: 'Carga masiva de especialidades' },
      { path: '/profile', contains: 'Mi cuenta' },
    ];

    for (const route of routes) {
      const response = await page.context().request.get(route.path);
      expect(response.ok()).toBeTruthy();
      expect(await response.text()).toContain(route.contains);
    }
  });

  test('provider recorre su area profesional', async ({ page }) => {
    await login(page, 'provider', /\/dashboard\/profile$/);

    await expect(page).toHaveURL(/\/dashboard\/profile$/);
    await expect(page.getByRole('heading', { name: /mi perfil profesional/i })).toBeVisible();

    const providerPages = [
      { path: '/dashboard/profile', url: /\/dashboard\/profile$/, heading: /mi perfil profesional/i },
      { path: '/profile', url: /\/profile$/, heading: /mi perfil/i },
      { path: '/search?q=reiki', url: /\/search\?q=reiki$/, title: /Resultados de b[uú]squeda/i },
    ];

    for (const item of providerPages) {
      const navPage = await page.context().newPage();
      await navPage.goto(item.path, { waitUntil: 'commit' });
      await expect(navPage.locator('body')).toBeVisible();
      await expect(navPage).toHaveURL(item.url);

      if ('heading' in item) {
        await expect(navPage.getByRole('heading', { name: item.heading })).toBeVisible();
      }

      if ('title' in item) {
        await expect(navPage).toHaveTitle(item.title);
      }

      await navPage.close();
    }

    const dashboardPage = await page.context().newPage();
    await dashboardPage.goto('/dashboard', { waitUntil: 'commit' });
    await expect(dashboardPage).toHaveURL(/\/dashboard\/profile$/);
    await expect(dashboardPage.getByRole('heading', { name: /mi perfil profesional/i })).toBeVisible();
    await dashboardPage.close();
  });

  test('client recorre su area y vuelve al portal', async ({ page }) => {
    await login(page, 'client', /\/profile$/);

    await expect(page).toHaveURL(/\/profile$/);
    await expect(page.getByRole('heading', { name: /mi cuenta/i })).toBeVisible();

    const clientPages = [
      { path: '/profile', url: /\/profile$/, heading: /mi cuenta/i },
      { path: '/dashboard', url: /\/profile$/, heading: /mi cuenta/i },
      { path: '/search?q=meditacion', url: /\/search\?q=meditacion$/, title: /Resultados de b[uú]squeda/i },
    ];

    for (const item of clientPages) {
      const navPage = await page.context().newPage();
      await navPage.goto(item.path, { waitUntil: 'commit' });
      await expect(navPage.locator('body')).toBeVisible();
      await expect(navPage).toHaveURL(item.url);

      if ('heading' in item) {
        await expect(navPage.getByRole('heading', { name: item.heading })).toBeVisible();
      }

      if ('title' in item) {
        await expect(navPage).toHaveTitle(item.title);
      }

      await navPage.close();
    }

    const portalPage = await page.context().newPage();
    await portalPage.goto('/', { waitUntil: 'domcontentloaded' });
    await expect(portalPage.getByRole('heading', { level: 1 })).toBeVisible();
    await portalPage.getByRole('link', { name: 'Ir al portal' }).first().click();
    await expect(portalPage).toHaveURL(/\/portal$/);
    await portalPage.close();
  });
});
