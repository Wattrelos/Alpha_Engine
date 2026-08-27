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

  constructor(page: Page, lang = 'pt-br') {
    super(page, lang);
    this.emptyCartMessage = page.getByText(/carrinho de compras está vazio/i).first();
    this.cartItems = page.locator('.cart-item, .cart-product-row, table.table tbody tr');
    this.subtotalValue = page.locator('.cart-total-row[data-total-code="sub_total"] .cart-total-value');
    this.totalValue = page.locator('.cart-total-row[data-total-code="total"] .cart-total-value');
    this.checkoutButton = page.locator('a[href*="/checkout"]');
    this.continueShoppingButton = page.locator('a[href*="/busca"], a[href="/"]');
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
}
