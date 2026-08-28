import { Page, Locator, expect } from '@playwright/test';
import { BasePage } from './BasePage';

/**
 * Page Object para a Página do Carrinho de Compras.
 */
export class CartPage extends BasePage {
  readonly emptyCartMessage: Locator;
  readonly cartItems: Locator;
  readonly subtotalValue: Locator;
  readonly totalValue: Locator;
  readonly checkoutButton: Locator;
  readonly continueShoppingButton: Locator;
  readonly shippingCepInput: Locator;
  readonly shippingCalculateBtn: Locator;
  readonly shippingResults: Locator;

  constructor(page: Page, lang = 'pt-br') {
    super(page, lang);
    this.emptyCartMessage = page.getByText(/carrinho de compras está vazio/i).first();
    this.cartItems = page.locator('.cart-item, .cart-product-row, table.table tbody tr');
    this.subtotalValue = page.locator('.cart-total-row[data-total-code="sub_total"] .cart-total-value');
    this.totalValue = page.locator('.cart-total-row[data-total-code="total"] .cart-total-value');
    this.checkoutButton = page.locator('#btn-checkout, a[href*="/checkout"], .cart-btn-checkout').first();
    this.continueShoppingButton = page.locator('#btn-continue-shopping, a[href*="/busca"], a[href="/"]').first();
    this.shippingCepInput = page.locator('#shipping-cep, .shipping-cep-input').first();
    this.shippingCalculateBtn = page.locator('#btn-calculate-shipping, .egen-shipping-simulator__btn').first();
    this.shippingResults = page.locator('#shipping-results, .egen-shipping-simulator__results').first();
  }

  /**
   * Abre a página do carrinho
   */
  async open() {
    await this.goto('/carrinho');
  }

  /**
   * Verifica se o carrinho está vazio
   */
  async isEmpty(): Promise<boolean> {
    return this.emptyCartMessage.isVisible();
  }

  /**
   * Preenche o CEP e calcula o frete no carrinho
   */
  async calculateShipping(cep: string) {
    await this.shippingCepInput.fill(cep);
    await this.shippingCalculateBtn.click();
    await expect(this.shippingResults).toBeVisible({ timeout: 10000 });
  }

  /**
   * Clica no botão para avançar para a página de checkout
   */
  async proceedToCheckout() {
    await expect(this.checkoutButton).toBeVisible();
    await this.checkoutButton.click();
  }
}
