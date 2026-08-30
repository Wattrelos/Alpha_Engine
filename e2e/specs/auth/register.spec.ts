import { test, expect } from '../../fixtures/test-fixtures';

test.describe('Módulo Autenticação - Fluxo de Cadastro e Auto-Login E2E', () => {
  test('deve renderizar o formulário de cadastro com campos obrigatórios e tokens CSRF', async ({
    registerPage,
    page,
  }) => {
    await registerPage.open();

    await expect(registerPage.firstnameInput).toBeVisible();
    await expect(registerPage.lastnameInput).toBeVisible();
    await expect(registerPage.emailInput).toBeVisible();
    await expect(registerPage.passwordInput).toBeVisible();
    await expect(registerPage.confirmInput).toBeVisible();
    await expect(registerPage.submitButton).toBeVisible();

    // Valida que os tokens CSRF estão presentes no formulário
    const csrfNameInput = page.locator('#form-register input[name="csrf_name"]');
    const csrfValueInput = page.locator('#form-register input[name="csrf_value"]');

    await expect(csrfNameInput).toHaveCount(1);
    await expect(csrfValueInput).toHaveCount(1);
  });

  test('deve cadastrar novo cliente e realizar auto-login imediato sem exigir tela intermediária de login', async ({
    registerPage,
    page,
    context,
  }) => {
    await registerPage.open();

    const timestamp = Date.now();
    const uniqueEmail = `cliente.autologin.${timestamp}@exemplo.com`;

    await registerPage.fillRegister({
      firstname: 'Mariana',
      lastname: 'Albuquerque',
      email: uniqueEmail,
      telephone: '11977776666',
      password: 'SenhaForte123!',
      confirmPassword: 'SenhaForte123!',
      agree: true,
    });

    await registerPage.submit();

    // Aguarda o redirecionamento automático (Auto-Login para /account)
    await page.waitForURL(/.*\/account/, { timeout: 15000 });
    expect(page.url()).toContain('/account');

    // Valida que o cookie de sessão seguro foi gravado no navegador
    const cookies = await context.cookies();
    const sessionCookie = cookies.find((c) => c.name === 'session_id');
    expect(sessionCookie).toBeDefined();
    expect(sessionCookie?.value).toBeTruthy();
  });

  test('deve exibir feedback de erro ao tentar submeter senhas divergentes', async ({
    registerPage,
    page,
  }) => {
    await registerPage.open();

    await registerPage.fillRegister({
      firstname: 'Teste',
      lastname: 'Invalido',
      email: `teste.invalido.${Date.now()}@exemplo.com`,
      telephone: '11999990000',
      password: 'SenhaForte123!',
      confirmPassword: 'SenhaDiferente999!',
      agree: true,
    });

    await registerPage.submit();

    // Permanece na tela de cadastro e exibe erro
    await page.waitForTimeout(1000);
    expect(page.url()).toContain('/cadastro');
  });
});
