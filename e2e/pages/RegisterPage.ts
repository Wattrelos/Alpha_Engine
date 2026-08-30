import { Page, Locator, expect } from '@playwright/test';
import { BasePage } from './BasePage';

export interface RegisterFormData {
  firstname: string;
  lastname: string;
  email: string;
  telephone?: string;
  password: string;
  confirmPassword?: string;
  agree?: boolean;
}

/**
 * Page Object para a Página de Cadastro de Clientes (/cadastro).
 */
export class RegisterPage extends BasePage {
  readonly form: Locator;
  readonly firstnameInput: Locator;
  readonly lastnameInput: Locator;
  readonly emailInput: Locator;
  readonly telephoneInput: Locator;
  readonly passwordInput: Locator;
  readonly confirmInput: Locator;
  readonly agreeCheckbox: Locator;
  readonly submitButton: Locator;
  readonly errorFirstname: Locator;
  readonly errorLastname: Locator;
  readonly errorEmail: Locator;
  readonly errorPassword: Locator;
  readonly errorConfirm: Locator;

  constructor(page: Page, lang = 'pt-br') {
    super(page, lang);
    this.form = page.locator('#form-register');
    this.firstnameInput = page.locator('#input-firstname');
    this.lastnameInput = page.locator('#input-lastname');
    this.emailInput = page.locator('#input-email');
    this.telephoneInput = page.locator('#input-telephone');
    this.passwordInput = page.locator('#input-password');
    this.confirmInput = page.locator('#input-confirm');
    this.agreeCheckbox = page.locator('#input-agree');
    this.submitButton = page.locator('#form-register button[type="submit"]');

    this.errorFirstname = page.locator('#error-firstname');
    this.errorLastname = page.locator('#error-lastname');
    this.errorEmail = page.locator('#error-email');
    this.errorPassword = page.locator('#error-password');
    this.errorConfirm = page.locator('#error-confirm');
  }

  /**
   * Abre a página de cadastro
   */
  async open() {
    await this.goto('/cadastro');
    await expect(this.form).toBeVisible();
  }

  /**
   * Preenche os dados do formulário de cadastro
   */
  async fillRegister(data: RegisterFormData) {
    if (data.firstname) await this.firstnameInput.fill(data.firstname);
    if (data.lastname) await this.lastnameInput.fill(data.lastname);
    if (data.email) await this.emailInput.fill(data.email);
    if (data.telephone && (await this.telephoneInput.isVisible())) {
      await this.telephoneInput.fill(data.telephone);
    }
    if (data.password) await this.passwordInput.fill(data.password);
    if (data.confirmPassword) {
      await this.confirmInput.fill(data.confirmPassword);
    } else if (data.password) {
      await this.confirmInput.fill(data.password);
    }

    if (data.agree !== false && (await this.agreeCheckbox.isVisible())) {
      await this.agreeCheckbox.check();
    }
  }

  /**
   * Submete o formulário de cadastro
   */
  async submit() {
    await this.submitButton.click();
  }
}
