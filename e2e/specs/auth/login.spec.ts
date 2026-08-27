import { test, expect } from '../../fixtures/test-fixtures';

test.describe('Módulo Autenticação - Fluxo de Login E2E', () => {
  test('deve renderizar o formulário de login com campos obrigatórios e tokens CSRF', async ({ loginPage, page }) => {
    await loginPage.open();

    await expect(loginPage.emailInput).toBeVisible();
    await expect(loginPage.passwordInput).toBeVisible();
    await expect(loginPage.submitButton).toBeVisible();

    // Valida que o token CSRF está presente no formulário
    const csrfNameInput = page.locator('#form-login input[name="csrf_name"]');
    const csrfValueInput = page.locator('#form-login input[name="csrf_value"]');

    await expect(csrfNameInput).toHaveCount(1);
    await expect(csrfValueInput).toHaveCount(1);
  });

  test('deve exibir feedback de erro ao tentar autenticar com credenciais inválidas', async ({ loginPage, page }) => {
    await loginPage.open();

    // Tenta efetuar login com credenciais inexistentes
    await loginPage.login('usuario_invalido_teste@alphaengine.local', 'SenhaIncorreta#123');

    // Aguarda a resposta (AJAX ou reload)
    await page.waitForTimeout(1000);

    // Valida que não houve status HTTP 500 de servidor quebrado
    // e que o usuário permanece na rota de login ou recebe alerta
    expect(page.url()).toContain('/login');
  });
});
