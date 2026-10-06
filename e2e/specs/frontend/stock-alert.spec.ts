import { test, expect } from '../../fixtures/test-fixtures';

test.describe('Módulo Catálogo - Alerta de Estoque ("Avise-me quando chegar" - ADR 0008)', () => {
  test('deve ocultar o botão de compra e exibir "Avise-me quando chegar" para produto com estoque zerado', async ({ pdpPage, page }) => {
    // Produto ID 1 possui quantity = 0 no banco de dados de teste
    await pdpPage.open(1);

    // Valida que o container de compra normal está oculto
    const buyRow = page.locator('#product-action-row-buy');
    await expect(buyRow).toBeHidden();

    // Valida que o bloco de alerta de reposição está visível
    await expect(pdpPage.stockAlertBox).toBeVisible();
    await expect(pdpPage.stockAlertBtnOpen).toBeVisible();
    await expect(pdpPage.stockAlertBtnOpen).toContainText('Avise-me quando chegar');
  });

  test('deve abrir o modal ao clicar no botão e exibir os campos de captura com aceite LGPD', async ({ pdpPage, page }) => {
    await pdpPage.open(1);

    // Clica para abrir o modal
    await pdpPage.stockAlertBtnOpen.click();

    // Valida abertura do modal com classe active
    await expect(pdpPage.stockAlertModal).toHaveClass(/active/);
    await expect(page.locator('.stock-alert-modal-title')).toContainText('Avise-me quando chegar');

    // Valida visibilidade dos campos essenciais
    await expect(pdpPage.stockAlertNameInput).toBeVisible();
    await expect(pdpPage.stockAlertEmailInput).toBeVisible();
    await expect(pdpPage.stockAlertPhoneInput).toBeVisible();
    await expect(pdpPage.stockAlertConsentCheckbox).toBeChecked();
    await expect(pdpPage.stockAlertSubmitBtn).toBeVisible();

    // Fecha o modal pelo botão X
    await pdpPage.stockAlertCloseBtn.click();
    await expect(pdpPage.stockAlertModal).not.toHaveClass(/active/);
  });

  test('deve cadastrar o alerta com sucesso e apresentar confirmação visual ao usuário', async ({ pdpPage, page }) => {
    await pdpPage.open(1);
    await pdpPage.stockAlertBtnOpen.click();

    const timestamp = Date.now();
    const testEmail = `lead.e2e.${timestamp}@agsonhos.com.br`;

    // Preenche o formulário
    await pdpPage.stockAlertNameInput.fill('Cliente E2E Playwright');
    await pdpPage.stockAlertEmailInput.fill(testEmail);
    await pdpPage.stockAlertPhoneInput.fill('11977778888');

    // Monitora a resposta HTTP da API de submissão
    const responsePromise = page.waitForResponse(response =>
      response.url().includes('/catalog/stock-alert/subscribe') && response.status() === 200
    );

    // Submete o formulário
    await pdpPage.stockAlertSubmitBtn.click();

    const response = await responsePromise;
    expect(response.ok()).toBeTruthy();

    const json = await response.json();
    expect(json.success).toBe(true);

    // Valida mensagem de feedback na interface
    await expect(pdpPage.stockAlertFeedback).toBeVisible();
    await expect(pdpPage.stockAlertFeedback).toHaveClass(/success/);
    await expect(pdpPage.stockAlertFeedback).toContainText('avisaremos você por e-mail');
  });

  test('deve exibir mensagem de cancelamento ao acessar a rota de opt-out', async ({ page }) => {
    // Acessa rota de cancelamento com token de exemplo
    await page.goto('/pt-br/catalog/stock-alert/unsubscribe?token=token_invalido_teste');

    await expect(page).toHaveTitle(/Cancelamento de Alerta de Estoque/i);
    const container = page.locator('.unsub-container');
    await expect(container).toBeVisible();
    await expect(container.locator('.unsub-title')).toBeVisible();
    await expect(container.locator('.unsub-btn')).toContainText('Página Inicial');
  });
});
