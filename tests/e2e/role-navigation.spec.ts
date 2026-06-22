import { expect, test, type Page } from '@playwright/test';

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
    await login(page, 'admin', /\/admin\/?$/);

    await expect(page).toHaveURL(/\/admin\/?$/);
    await expect(page.getByRole('heading', { name: /panel de administraci[oó]n/i })).toBeVisible();

    const adminPages = [
      { path: '/dashboard', url: /\/admin\/?$/, heading: /panel de administraci[oó]n/i },
      { path: '/admin/approvals', url: /\/admin\/approvals$/, heading: /pendientes de aprobaci[oó]n/i },
      { path: '/admin/users', url: /\/admin\/users$/, heading: /cuentas de usuarios/i },
      { path: '/admin/edits', url: /\/admin\/edits$/, heading: /cambios pendientes/i },
      { path: '/admin/reports', url: /\/admin\/reports$/, heading: /cuentas reportadas/i },
      { path: '/admin/specialties', url: /\/admin\/specialties$/, title: /Especialidades/i, table: /Nombre/i },
      { path: '/admin/specialties/create', url: /\/admin\/specialties\/create$/, heading: /nueva especialidad/i },
      { path: '/admin/specialties/bulk', url: /\/admin\/specialties\/bulk$/, heading: /carga masiva de especialidades/i },
      { path: '/profile', url: /\/profile$/, heading: /mi perfil/i },
    ];

    for (const item of adminPages) {
      const navPage = await page.context().newPage();
      await navPage.goto(item.path, { waitUntil: 'commit' });
      await expect(navPage).toHaveURL(item.url);
      if ('heading' in item) {
        await expect(navPage.getByRole('heading', { name: item.heading })).toBeVisible();
      }

      if ('title' in item) {
        await expect(navPage).toHaveTitle(item.title);
      }

      if ('table' in item) {
        await expect(navPage.getByRole('columnheader', { name: item.table })).toBeVisible();
      }
      await navPage.close();
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
