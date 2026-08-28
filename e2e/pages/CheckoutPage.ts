import { Page, Locator, expect } from '@playwright/test';
import { BasePage } from './BasePage';

export interface RegisterFormData {
  firstname: string;
  lastname: string;
  email: string;
  telephone: string;
  password: string;
  confirmPassword?: string;
}

export interface AddressFormData {
  firstname: string;
  lastname: string;
  recipient?: string;
  postcode: string;
  street?: string;
  number: string;
  complement?: string;
  neighborhood?: string;
  city?: string;
  zone?: string;
}

/**
 * Page Object para a Página de Checkout Multi-Etapas (Multi-step Checkout).
 */
export class CheckoutPage extends BasePage {
  readonly pageTitle: Locator;
  readonly progressBar: Locator;

  // Etapa 1 - Identificação
  readonly stepIdentity: Locator;
  readonly btnActionLogin: Locator;
  readonly btnActionGuest: Locator;
  readonly btnActionRegister: Locator;
  readonly registerForm: Locator;
  readonly registerFirstname: Locator;
  readonly registerLastname: Locator;
  readonly registerEmail: Locator;
  readonly registerTelephone: Locator;
  readonly registerPassword: Locator;
  readonly registerConfirm: Locator;
  readonly step1NextBtn: Locator;

  // Etapa 2 - Endereço de Cobrança / Entrega
  readonly stepBilling: Locator;
  readonly btnSameAddress: Locator;
  readonly btnDifferentAddress: Locator;
  readonly billingForm: Locator;
  readonly paymentFirstname: Locator;
  readonly paymentLastname: Locator;
  readonly paymentCompany: Locator;
  readonly paymentPostcode: Locator;
  readonly paymentStreet: Locator;
  readonly paymentNumber: Locator;
  readonly paymentComplement: Locator;
  readonly paymentNeighborhood: Locator;
  readonly paymentCity: Locator;
  readonly paymentZone: Locator;
  readonly step2NextBtn: Locator;

  // Etapa 4 - Método de Pagamento
  readonly stepPayment: Locator;
  readonly paymentMethodsGrid: Locator;
  readonly paymentNote: Locator;
  readonly submitCheckoutBtn: Locator;

  constructor(page: Page, lang = 'pt-br') {
    super(page, lang);
    this.pageTitle = page.locator('.egen-checkout-title');
    this.progressBar = page.locator('#checkout-progress-bar');

    // Etapa 1
    this.stepIdentity = page.locator('#step-identity');
    this.btnActionLogin = page.locator('button[data-checkout-action="login"]');
    this.btnActionGuest = page.locator('button[data-checkout-action="guest"]');
    this.btnActionRegister = page.locator('button[data-checkout-action="register"]');
    this.registerForm = page.locator('#checkout-register-form');
    this.registerFirstname = page.locator('#register-firstname');
    this.registerLastname = page.locator('#register-lastname');
    this.registerEmail = page.locator('#register-email');
    this.registerTelephone = page.locator('#register-telephone');
    this.registerPassword = page.locator('#register-password');
    this.registerConfirm = page.locator('#register-confirm');
    this.step1NextBtn = page.locator('#step-identity [data-checkout-nav="next"]');

    // Etapa 2
    this.stepBilling = page.locator('#step-billing');
    this.btnSameAddress = page.locator('button[data-checkout-action="same-address"]');
    this.btnDifferentAddress = page.locator('button[data-checkout-action="different-address"]');
    this.billingForm = page.locator('#checkout-billing-address');
    this.paymentFirstname = page.locator('#input-payment-firstname');
    this.paymentLastname = page.locator('#input-payment-lastname');
    this.paymentCompany = page.locator('#input-payment-company');
    this.paymentPostcode = page.locator('#input-payment-postcode');
    this.paymentStreet = page.locator('#input-payment-address-1');
    this.paymentNumber = page.locator('#input-payment-number');
    this.paymentComplement = page.locator('#input-payment-address-2');
    this.paymentNeighborhood = page.locator('#input-payment-neighborhood');
    this.paymentCity = page.locator('#input-payment-city');
    this.paymentZone = page.locator('#input-payment-zone');
    this.step2NextBtn = page.locator('#btn-billing-next');

    // Etapa 4
    this.stepPayment = page.locator('#step-payment-method');
    this.paymentMethodsGrid = page.locator('.egen-payment-methods-grid');
    this.paymentNote = page.locator('#payment_note');
    this.submitCheckoutBtn = page.locator('#btn-submit-checkout');
  }

  /**
   * Abre a página de checkout
   */
  async open() {
    await this.goto('/checkout');
    await expect(this.pageTitle).toBeVisible();
  }

  /**
   * Escolhe uma das opções de identificação da Etapa 1
   */
  async chooseIdentity(action: 'register' | 'guest' | 'login') {
    if (action === 'register') {
      await this.btnActionRegister.click();
      await expect(this.registerForm).toBeVisible();
    } else if (action === 'guest') {
      await this.btnActionGuest.click();
    } else {
      await this.btnActionLogin.click();
    }
  }

  /**
   * Preenche o formulário de cadastro na Etapa 1
   */
  async fillRegisterForm(data: RegisterFormData) {
    await this.registerFirstname.fill(data.firstname);
    await this.registerLastname.fill(data.lastname);
    await this.registerEmail.fill(data.email);
    await this.registerTelephone.fill(data.telephone);
    await this.registerPassword.fill(data.password);
    await this.registerConfirm.fill(data.confirmPassword || data.password);
  }

  /**
   * Avança da Etapa 1 para a próxima
   */
  async continueFromStep1() {
    await this.step1NextBtn.click();
    await expect(this.stepBilling).toBeVisible({ timeout: 10000 });
  }

  /**
   * Escolhe opção de endereço na Etapa 2
   */
  async chooseAddressOption(option: 'same-address' | 'different-address' = 'same-address') {
    if (option === 'same-address') {
      await this.btnSameAddress.click();
    } else {
      await this.btnDifferentAddress.click();
    }
    await expect(this.billingForm).toBeVisible();
  }

  /**
   * Preenche o formulário de endereço na Etapa 2
   */
  async fillBillingAddress(data: AddressFormData) {
    await this.paymentFirstname.fill(data.firstname);
    await this.paymentLastname.fill(data.lastname);
    if (data.recipient) {
      await this.paymentCompany.fill(data.recipient);
    }
    
    // Digita o CEP e aguarda o preenchimento automático
    await this.paymentPostcode.fill(data.postcode);
    await this.paymentPostcode.dispatchEvent('blur');

    // Aguarda que o campo de logradouro ou cidade receba o valor
    if (data.street) {
      // Se não preencheu via CEP, preenche manual
      if (!(await this.paymentStreet.inputValue())) {
        await this.paymentStreet.fill(data.street);
      }
    } else {
      await expect(this.paymentStreet).not.toHaveValue('', { timeout: 7000 });
    }

    await this.paymentNumber.fill(data.number);
    if (data.complement) {
      await this.paymentComplement.fill(data.complement);
    }
  }

  /**
   * Avança da Etapa 2 para a etapa de Pagamento (Etapa 4)
   */
  async continueFromStep2() {
    await this.step2NextBtn.click();
    await expect(this.stepPayment).toBeVisible({ timeout: 10000 });
  }

  /**
   * Seleciona um método de pagamento
   */
  async selectPaymentMethod(method: 'cod' | 'transferencia' | 'pix' | 'link_pagamento') {
    const radio = this.page.locator(`input[name="payment_method"][value="${method}"]`);
    await radio.check({ force: true });
  }

  /**
   * Submete o pedido no checkout
   */
  async submitOrder() {
    await expect(this.submitCheckoutBtn).toBeVisible();
    await this.submitCheckoutBtn.click();
  }
}
