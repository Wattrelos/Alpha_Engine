import { test, expect } from '../../fixtures/test-fixtures';

test.describe('Validação da Opção de Configuração de Compras de Visitantes', () => {
  test('deve exibir as opções de identificação e o botão de visitante no checkout quando habilitado', async ({
    homePage,
    searchPage,
    cartPage,
    checkoutPage,
    page,
  }) => {
    // 1. Acessa a home e adiciona um item ao carrinho
    await homePage.open();
    await homePage.search('ceramica');
    await searchPage.addProductToCart(0);
    await page.waitForTimeout(500);

    // 2. Vai para o carrinho e prossegue para o checkout
    await cartPage.open();
    await cartPage.proceedToCheckout();
    await expect(page).toHaveURL(/.*\/checkout/);

    // 3. Valida que a Etapa 1 exibe as opções de identificação
    await expect(checkoutPage.stepIdentity).toBeVisible();
    await expect(checkoutPage.btnActionLogin).toBeVisible();
    await expect(checkoutPage.btnActionRegister).toBeVisible();
    await expect(checkoutPage.btnActionGuest).toBeVisible();
  });
});
