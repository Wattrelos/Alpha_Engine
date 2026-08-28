import { test, expect } from '../../fixtures/test-fixtures';

test.describe('Módulo Visual - Regressão Visual do Fluxo de Compras (VRT)', () => {
  test('deve manter integridade e consistência visual em todas as etapas do funil de compras', async ({
    homePage,
    searchPage,
    cartPage,
    checkoutPage,
    orderSuccessPage,
    mocks,
    page,
  }) => {
    // Intercepta chamadas de CEP com mock para visual estável e determinístico
    await mocks.mockViaCepSuccess(page, '01001000', {
      logradouro: 'Praça da Sé',
      bairro: 'Sé',
      localidade: 'São Paulo',
      uf: 'SP',
    });

    // ── ETAPA 1: Busca e Resultados ──
    await searchPage.open('ceramica');
    await expect(searchPage.productCards.first()).toBeVisible({ timeout: 10000 });

    // Snapshot visual do Card de Produto na Busca
    await expect(searchPage.productCards.first()).toHaveScreenshot('checkout-01-search-product-card.png', {
      timeout: 10000,
    });

    // Adiciona produtos ao carrinho
    await searchPage.addProductToCart(0);
    await page.waitForTimeout(1000);
    await searchPage.addProductToCart(1);
    await page.waitForTimeout(1000);

    // ── ETAPA 2: Carrinho e Frete ──
    await cartPage.open();
    await expect(cartPage.cartItems.first()).toBeVisible({ timeout: 10000 });

    await cartPage.calculateShipping('01001-000');
    await expect(cartPage.shippingResults).toBeVisible({ timeout: 10000 });

    // Snapshot visual do Resumo do Carrinho com Frete Calculado
    const cartSummary = page.locator('.cart-summary').first();
    await expect(cartSummary).toBeVisible();
    await expect(cartSummary).toHaveScreenshot('checkout-02-cart-summary-with-shipping.png', {
      timeout: 10000,
    });

    // Avança para o checkout
    await cartPage.proceedToCheckout();
    await expect(page).toHaveURL(/.*\/checkout/);

    // ── ETAPA 3: Checkout - Identificação e Formulário de Cadastro ──
    await checkoutPage.chooseIdentity('register');

    const timestamp = Date.now();
    await checkoutPage.fillRegisterForm({
      firstname: 'Mariana',
      lastname: 'Oliveira',
      email: `mariana.oliveira.${timestamp}@exemplo.com`,
      telephone: '11999998888',
      password: 'SenhaVisual123!',
      confirmPassword: 'SenhaVisual123!',
    });

    // Snapshot visual do formulário de identificação e cadastro mascarando o campo de e-mail dinâmico
    const stepIdentity = page.locator('#step-identity');
    await expect(stepIdentity).toHaveScreenshot('checkout-03-step-register.png', {
      timeout: 10000,
      mask: [page.locator('#register-email')],
    });

    await checkoutPage.continueFromStep1();

    // ── ETAPA 4: Checkout - Endereço de Entrega e Autocomplete de CEP ──
    await checkoutPage.chooseAddressOption('same-address');
    await checkoutPage.fillBillingAddress({
      firstname: 'Mariana',
      lastname: 'Oliveira',
      recipient: 'Mariana Oliveira',
      postcode: '01001-000',
      street: 'Praça da Sé',
      number: '500',
      complement: 'Bloco B - Apt 12',
      neighborhood: 'Sé',
      city: 'São Paulo',
      zone: 'SP',
    });

    // Snapshot visual do formulário de endereço preenchido
    const stepBilling = page.locator('#step-billing');
    await expect(stepBilling).toHaveScreenshot('checkout-04-step-billing-address.png', {
      timeout: 10000,
    });

    await checkoutPage.continueFromStep2();

    // ── ETAPA 5: Checkout - Método de Pagamento ──
    await checkoutPage.selectPaymentMethod('cod');

    const stepPayment = page.locator('#step-payment-method');
    await expect(stepPayment).toHaveScreenshot('checkout-05-step-payment.png', {
      timeout: 10000,
    });

    // Submete a compra
    await checkoutPage.submitOrder();

    // ── ETAPA 6: Confirmação e Tela de Sucesso ──
    await orderSuccessPage.expectSuccess();

    // Snapshot visual da caixa de sucesso do pedido mascarando o texto dinâmico do número do pedido
    await expect(orderSuccessPage.successCard).toHaveScreenshot('checkout-06-order-success.png', {
      timeout: 10000,
      mask: [orderSuccessPage.successDesc],
    });
  });
});
