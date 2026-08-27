import { test, expect } from '../../fixtures/test-fixtures';

test.describe('Módulo Visual - Componentes e Formulários', () => {
  test('deve manter padrão visual do formulário de autenticação (Login)', async ({ loginPage, page }) => {
    await loginPage.open();

    const form = page.locator('#form-login');
    await expect(form).toBeVisible();

    await expect(form).toHaveScreenshot('login-form.png', {
      timeout: 10000,
    });
  });

  test('deve manter padrão visual do card de produto na listagem', async ({ searchPage, page }) => {
    await searchPage.open('cimento');

    const firstCard = page.locator('.egen-prod-card').first();
    await expect(firstCard).toBeVisible();

    await expect(firstCard).toHaveScreenshot('product-card.png', {
      timeout: 10000,
    });
  });
});
