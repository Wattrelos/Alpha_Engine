import { Page, Locator, expect } from '@playwright/test';
import { BasePage } from './BasePage';

/**
 * Page Object para a Página de Autenticação / Login.
 */
export class LoginPage extends BasePage {
  readonly form: Locator;
  readonly emailInput: Locator;
  readonly passwordInput: Locator;
  readonly submitButton: Locator;
  readonly alertDanger: Locator;
  readonly forgotPasswordLink: Locator;

  constructor(page: Page, lang = 'pt-br') {
    super(page, lang);
    this.form = page.locator('#form-login');
    this.emailInput = page.locator('#input-email, input[name="email"]');
    this.passwordInput = page.locator('#input-password, input[name="password"]');
    this.submitButton = page.locator('#form-login button[type="submit"]');
    this.alertDanger = page.locator('.alert-danger, .egen-alert--danger, .error, .egen-error-message');
    this.forgotPasswordLink = page.locator('a[href*="recuperar-senha"], a[href*="forgotten"]');
  }

  /**
   * Abre a página de login
   */
  async open() {
    await this.goto('/login');
    await expect(this.form).toBeVisible();
  }

  /**
   * Preenche o formulário e submete
   */
  async login(email: string, pass: string) {
    await this.emailInput.fill(email);
    await this.passwordInput.fill(pass);
    await this.submitButton.click();
  }
}
