import { Page, Locator, expect } from '@playwright/test';
import { BasePage } from './BasePage';

/**
 * Page Object para a Página de Pedido Realizado com Sucesso (/checkout/sucesso).
 */
export class OrderSuccessPage extends BasePage {
  readonly successCard: Locator;
  readonly successTitle: Locator;
  readonly successDesc: Locator;
  readonly returnHomeBtn: Locator;

  constructor(page: Page, lang = 'pt-br') {
    super(page, lang);
    this.successCard = page.locator('.cart-success-box');
    this.successTitle = page.locator('.cart-success-title');
    this.successDesc = page.locator('.cart-success-desc');
    this.returnHomeBtn = page.locator('.cart-success-box a.egen-btn-primary, a:has-text("Voltar para a Página Inicial")');
  }

  /**
   * Valida se a página de sucesso foi carregada com sucesso
   */
  async expectSuccess() {
    await expect(this.page).toHaveURL(/.*\/checkout\/sucesso/);
    await expect(this.successCard).toBeVisible({ timeout: 15000 });
    await expect(this.successTitle).toContainText(/Pedido Realizado com Sucesso/i);
  }

  /**
   * Clica no botão para retornar à página inicial
   */
  async returnToHome() {
    await this.returnHomeBtn.click();
    await expect(this.page).toHaveURL(/\/pt-br\/?$/);
  }
}
