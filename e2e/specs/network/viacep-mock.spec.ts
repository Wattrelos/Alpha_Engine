import { test, expect } from '../../fixtures/test-fixtures';

test.describe('Módulo Rede & Mocks - Simulador de Frete e Integração ViaCEP', () => {
  test('deve preencher e exibir opções de frete instantaneamente com CEP válido mockado', async ({ pdpPage, page, mocks }) => {
    // Intercepta a requisição ao ViaCEP com dados mockados (São Paulo - SP)
    await mocks.mockViaCepSuccess(page, '01001000', {
      localidade: 'São Paulo',
      uf: 'SP',
    });

    await pdpPage.open(1310);

    const cepInput = page.locator('#shipping-cep');
    const calcButton = page.locator('#btn-calculate-shipping');
    const resultsContainer = page.locator('#shipping-results');

    await expect(cepInput).toBeVisible();
    await cepInput.fill('01001000');
    await calcButton.click();

    // Valida que o container de resultados de frete é exibido com a localidade mockada
    await expect(resultsContainer).toBeVisible();
    await expect(resultsContainer).toContainText(/São Paulo - SP/i);
  });

  test('deve tratar adequadamente CEP inexistente sem exibir opções inválidas', async ({ pdpPage, page, mocks }) => {
    // Intercepta a requisição ao ViaCEP retornando erro de CEP não encontrado
    await mocks.mockViaCepNotFound(page, '99999999');

    await pdpPage.open(1310);

    const cepInput = page.locator('#shipping-cep');
    const calcButton = page.locator('#btn-calculate-shipping');
    const resultsContainer = page.locator('#shipping-results');

    await cepInput.fill('99999999');
    await calcButton.click();

    // Aguarda a resolução da requisição
    await page.waitForTimeout(500);

    // O container de opções de frete não deve ser aberto para CEP inválido
    await expect(resultsContainer).not.toBeVisible();
  });

  test('deve manter resiliência do frontend mesmo com falha de conexão na API externa', async ({ pdpPage, page, mocks }) => {
    // Intercepta e aborta as chamadas para o serviço externo simulando queda de internet / timeout
    await mocks.mockViaCepFailure(page, 'abort');

    await pdpPage.open(1310);

    const cepInput = page.locator('#shipping-cep');
    const calcButton = page.locator('#btn-calculate-shipping');

    await cepInput.fill('13010000');
    await calcButton.click();

    // Garante que o botão volta a ficar ativo após o fallback
    await expect(calcButton).toBeEnabled();
  });
});
