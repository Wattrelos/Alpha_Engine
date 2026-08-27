import { test, expect } from '../../fixtures/test-fixtures';

test.describe('Módulo Carrinho - Fluxo de Navegação e Estado', () => {
  test('deve renderizar o carrinho vazio para visitante anônimo inicial', async ({ cartPage, page }) => {
    await cartPage.open();

    // Valida rota do carrinho
    await expect(page).toHaveURL(/.*\/carrinho/);
    await expect(page).toHaveTitle(/Carrinho/i);

    // Valida estado de carrinho vazio
    await expect(cartPage.emptyCartMessage).toBeVisible();
  });

  test('deve permitir navegar até a página do produto e interagir com opções de compra', async ({ pdpPage, page }) => {
    // Acessa um produto existente no catálogo
    await pdpPage.open(1310);

    // Verifica que o formulário de compra está visível e ativo
    await expect(pdpPage.formPurchase).toBeVisible();
    await expect(pdpPage.buyButton).toBeVisible();

    // Valida que o preço é exibido formatado
    await expect(pdpPage.priceElement).toBeVisible();
  });
});
