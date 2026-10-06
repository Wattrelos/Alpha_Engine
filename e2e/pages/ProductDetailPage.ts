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
  readonly stockAlertBox: Locator;
  readonly stockAlertBtnOpen: Locator;
  readonly stockAlertModal: Locator;
  readonly stockAlertNameInput: Locator;
  readonly stockAlertEmailInput: Locator;
  readonly stockAlertPhoneInput: Locator;
  readonly stockAlertConsentCheckbox: Locator;
  readonly stockAlertSubmitBtn: Locator;
  readonly stockAlertFeedback: Locator;
  readonly stockAlertCloseBtn: Locator;

  constructor(page: Page, lang = 'pt-br') {
    super(page, lang);
    this.formPurchase = page.locator('#form-product-purchase');
    this.quantityInput = page.locator('#input-quantity');
    this.buyButton = page.locator('#button-cart, .egen-btn-buy');
    this.priceElement = page.locator('.egen-product-price-card__price-normal, .egen-product-price');
    this.increaseQtyBtn = page.locator('.egen-product-qty__btn:has-text("+")');
    this.decreaseQtyBtn = page.locator('.egen-product-qty__btn:has-text("-")');

    // Locators do Alerta de Reposição de Estoque (ADR 0008)
    this.stockAlertBox = page.locator('#product-action-stock-alert');
    this.stockAlertBtnOpen = page.locator('#btn-open-stock-alert');
    this.stockAlertModal = page.locator('#stock-alert-modal');
    this.stockAlertNameInput = page.locator('#stock-alert-name');
    this.stockAlertEmailInput = page.locator('#stock-alert-email');
    this.stockAlertPhoneInput = page.locator('#stock-alert-phone');
    this.stockAlertConsentCheckbox = page.locator('#stock-alert-consent-privacy');
    this.stockAlertSubmitBtn = page.locator('#stock-alert-btn-submit');
    this.stockAlertFeedback = page.locator('#stock-alert-feedback');
    this.stockAlertCloseBtn = page.locator('#stock-alert-btn-close');
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
