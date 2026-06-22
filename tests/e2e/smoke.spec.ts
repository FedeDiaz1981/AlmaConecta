import { expect, test } from '@playwright/test';

test.describe('Alma Conecta smoke', () => {
  test('landing loads and points to portal and register', async ({ page }) => {
    await page.goto('/');

    await expect(page).toHaveTitle(/Alma Conecta/i);
    await expect(page.getByRole('heading', { level: 1, name: /encontr/i })).toBeVisible();

    const portalLinks = page.getByRole('link', { name: 'Ir al portal' });
    await expect(portalLinks.first()).toBeVisible();
    await expect(portalLinks.first()).toHaveAttribute('href', /\/portal$/);

    const registerButtons = page.getByRole('button', { name: 'Registrarse' });
    await expect(registerButtons.first()).toBeVisible();
  });

  test('portal loads', async ({ page }) => {
    await page.goto('/portal');

    await expect(page).toHaveURL(/\/portal$/);
    await expect(page.locator('body')).toBeVisible();
  });

  test('login page renders', async ({ page }) => {
    await page.goto('/login');

    const loginForm = page.locator('form').filter({
      has: page.getByRole('button', { name: 'Ingresar' }),
    }).first();

    await expect(page.getByRole('heading', { name: /ingres/i })).toBeVisible();
    await expect(loginForm.locator('input[name="email"]').first()).toBeVisible();
    await expect(loginForm.locator('input[name="password"]').first()).toBeVisible();
  });

  test('register page renders both account types', async ({ page }) => {
    await page.goto('/register');

    await expect(page.getByRole('heading', { name: /creá tu cuenta/i })).toBeVisible();
    await expect(page.getByLabel('Soy profesional')).toBeVisible();
    await expect(page.getByLabel('Busco profesional')).toBeVisible();
  });

  test('demo admin can log in and land on admin dashboard', async ({ page }) => {
    await page.goto('/login');
    const loginForm = page.locator('form').filter({
      has: page.getByRole('button', { name: 'Ingresar' }),
    }).first();
    await loginForm.locator('input[name="email"]').fill('admin.demo@almaconecta.com');
    await loginForm.locator('input[name="password"]').fill('Admin1234!');
    await loginForm.getByRole('button', { name: 'Ingresar' }).click();

    await expect(page.getByRole('heading', { name: /panel de administración/i })).toBeVisible();
  });

  test('demo provider can log in and land on provider dashboard', async ({ page }) => {
    await page.goto('/login');
    const loginForm = page.locator('form').filter({
      has: page.getByRole('button', { name: 'Ingresar' }),
    }).first();
    await loginForm.locator('input[name="email"]').fill('provider.demo@almaconecta.com');
    await loginForm.locator('input[name="password"]').fill('Provider1234!');
    await loginForm.getByRole('button', { name: 'Ingresar' }).click();

    await expect(page.getByRole('heading', { name: /mi perfil profesional/i })).toBeVisible();
  });

  test('demo client can log in and land on client dashboard', async ({ page }) => {
    await page.goto('/login');
    const loginForm = page.locator('form').filter({
      has: page.getByRole('button', { name: 'Ingresar' }),
    }).first();
    await loginForm.locator('input[name="email"]').fill('client.demo@almaconecta.com');
    await loginForm.locator('input[name="password"]').fill('Client1234!');
    await loginForm.getByRole('button', { name: 'Ingresar' }).click();

    await expect(page.getByRole('heading', { name: /mi cuenta/i })).toBeVisible();
  });
});
