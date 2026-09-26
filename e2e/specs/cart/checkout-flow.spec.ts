import { test, expect } from '../../fixtures/test-fixtures';

test.describe('Fluxo Completo de Compras E2E (End-to-End)', () => {
  test('deve realizar a jornada completa de compras desde a busca até o pedido confirmado', async ({
    homePage,
    searchPage,
    cartPage,
    checkoutPage,
    orderSuccessPage,
    mocks,
    page,
  }) => {
    // Intercepta e mocka chamadas de CEP (ViaCEP) com dados válidos de teste
    await mocks.mockViaCepSuccess(page, '01001000', {
      logradouro: 'Praça da Sé',
      bairro: 'Sé',
      localidade: 'São Paulo',
      uf: 'SP',
    });

    // 1. Cliente entra no site
    await homePage.open();
    await expect(page).toHaveTitle(/Alpha|meusite/i);

    // 2. Clica na barra de busca, digita "ceramica" e pesquisa
    await homePage.search('ceramica');
    await expect(page).toHaveURL(/.*\/busca\?search=ceramica/);

    // 3. Abre a página de resultados da pesquisa e exibe os produtos
    const productCount = await searchPage.getProductCount();
    expect(productCount).toBeGreaterThanOrEqual(2);

    // 4. Escolhe o primeiro produto e clica em "Adicionar ao carrinho"
    await searchPage.addProductToCart(0);
    await page.waitForTimeout(1000); // Aguarda toast e atualização do carrinho

    // 5. Escolhe outro produto e clica em "Adicionar ao carrinho"
    await searchPage.addProductToCart(1);
    await page.waitForTimeout(1000);

    // 6. Clica no botão "Carrinho" e acessa a página do carrinho
    await cartPage.open();
    await expect(page).toHaveURL(/.*\/carrinho/);
    await expect(cartPage.cartItems.first()).toBeVisible();

    // 7. Tela do Carrinho: Digita o CEP, calcula e visualiza opções de frete
    await cartPage.calculateShipping('01001-000');
    await expect(cartPage.shippingResults).toBeVisible();

    // 8. Clica em "Finalizar compra"
    await cartPage.proceedToCheckout();
    await expect(page).toHaveURL(/.*\/checkout/, { timeout: 10000 });

    // 9. Tela de Cadastro ou Login: Escolhe "Quero me cadastrar"
    await checkoutPage.chooseIdentity('register');

    const timestamp = Date.now();
    const userEmail = `cliente.teste.${timestamp}@exemplo.com`;

    // Preenche dados cadastrais
    await checkoutPage.fillRegisterForm({
      firstname: 'Carlos',
      lastname: 'Silva',
      email: userEmail,
      telephone: '11987654321',
      password: 'SenhaForte123!',
      confirmPassword: 'SenhaForte123!',
    });

    // Clica em "Continuar" para avançar à Etapa 2
    await checkoutPage.continueFromStep1();

    // 10. Tela Endereço de Entrega: Seleciona entregar no endereço informado
    await checkoutPage.chooseAddressOption('same-address');

    // Preenche os dados de endereço com CEP simulado
    await checkoutPage.fillBillingAddress({
      firstname: 'Carlos',
      lastname: 'Silva',
      recipient: 'Carlos Silva Destinatário',
      postcode: '01001-000',
      street: 'Praça da Sé',
      number: '100',
      complement: 'Apto 42',
      neighborhood: 'Sé',
      city: 'São Paulo',
      zone: 'SP',
    });

    // Clica em "Avançar" para ir para a etapa de Pagamento
    await checkoutPage.continueFromStep2();

    // 11. Tela Finalizar Pedido: Seleciona o método de pagamento
    await checkoutPage.selectPaymentMethod('cod');

    // Clica em "Finalizar Compra"
    await checkoutPage.submitOrder();

    // 12. Tela Pedido Realizado com Sucesso!
    await orderSuccessPage.expectSuccess();

    // 13. Clica em "Voltar para a Página Inicial"
    await orderSuccessPage.returnToHome();
  });
});
