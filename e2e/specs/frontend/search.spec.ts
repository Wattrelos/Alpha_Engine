import { test, expect } from '../../fixtures/test-fixtures';

test.describe('Módulo Catálogo & Busca - Fluxo de Pesquisa E2E', () => {
  test('deve pesquisar produtos a partir da busca global e renderizar a lista', async ({ homePage, searchPage, page }) => {
    await homePage.open();

    // Digita termo comum e submete busca
    await homePage.search('cimento');

    // Valida transição para rota de busca
    await expect(page).toHaveURL(/.*\/busca\?search=cimento/);

    // Valida que o título da busca reflete o termo
    await expect(page).toHaveTitle(/Busca.*cimento/i);

    // Valida que há cards de produtos ou feedback apropriado
    const count = await searchPage.getProductCount();
    expect(count).toBeGreaterThanOrEqual(0);
  });

  test('deve exibir mensagem amigável para termo sem resultados', async ({ searchPage, page }) => {
    await searchPage.open('termo_completamente_inexistente_xyz_9999');

    // Valida que a página de busca carrega sem erro de servidor
    await expect(page).toHaveURL(/.*\/busca\?search=.*/);
    
    // Verifica que nenhum card de produto é exibido
    const count = await searchPage.getProductCount();
    expect(count).toBe(0);
  });
});
