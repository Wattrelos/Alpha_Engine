import { test, expect } from '../../fixtures/test-fixtures';

test.describe('Módulo Responsivo - Layout e Navegação Mobile', () => {
  test('deve renderizar layout mobile sem quebras visuais e com header adaptado', async ({ page }) => {
    await page.goto('/pt-br');

    // Valida que o container principal está visível na tela
    const mainHeader = page.locator('.egen-header');
    await expect(mainHeader).toBeVisible();

    // Valida que o campo de busca ou botão de departamentos responde na visualização atual
    const searchForm = page.locator('.egen-search-bar');
    await expect(searchForm).toBeVisible();
  });
});
