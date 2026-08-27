import { Page, Locator, expect } from '@playwright/test';
import { BasePage } from './BasePage';

/**
 * Page Object para a Página de Detalhe do Produto (PDP).
 */
export class ProductDetailPage extends BasePage {
  readonly formPurchase: Locator;
  readonly quantityInput: Locator;
  readonly buyButton: Locator;
  readonly priceElement: Locator;
  readonly increaseQtyBtn: Locator;
  readonly decreaseQtyBtn: Locator;

  constructor(page: Page, lang = 'pt-br') {
    super(page, lang);
    this.formPurchase = page.locator('#form-product-purchase');
    this.quantityInput = page.locator('#input-quantity');
    this.buyButton = page.locator('#button-cart, .egen-btn-buy');
    this.priceElement = page.locator('.egen-product-price-card__price-normal, .egen-product-price');
    this.increaseQtyBtn = page.locator('.egen-product-qty__btn:has-text("+")');
    this.decreaseQtyBtn = page.locator('.egen-product-qty__btn:has-text("-")');
  }

  /**
   * Abre a página de um produto pelo slug ou ID
   */
  async open(productIdentifier: string | number) {
    await this.goto(`/produto/${productIdentifier}`);
  }

  /**
   * Adiciona o produto ao carrinho e aguarda a resposta
   */
  async addToCart(quantity = 1) {
    if (quantity > 1) {
      await this.quantityInput.fill(quantity.toString());
    }
    
    // Dispara o clique no botão de compra
    await this.buyButton.click();
  }
}
