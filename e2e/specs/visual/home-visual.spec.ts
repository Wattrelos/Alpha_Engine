import { test, expect } from '../../fixtures/test-fixtures';

test.describe('Módulo Visual - Regressão Visual da Home e Estrutura Principal', () => {
  test('deve manter integridade visual do Header e navegação superior', async ({ homePage, page }) => {
    await homePage.open();

    const header = page.locator('.egen-header');
    await expect(header).toBeVisible();

    await expect(header).toHaveScreenshot('header-desktop.png', {
      timeout: 10000,
    });
  });

  test('deve manter integridade visual do Rodapé institucional', async ({ homePage, page }) => {
    await homePage.open();

    const footer = page.locator('.ag-footer').first();
    await footer.scrollIntoViewIfNeeded();
    await expect(footer).toBeVisible();

    await expect(footer).toHaveScreenshot('footer-desktop.png', {
      timeout: 10000,
    });
  });
});
