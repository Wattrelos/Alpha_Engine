import { Page, Locator, expect } from '@playwright/test';
import { BasePage } from './BasePage';

/**
 * Page Object para a Página de Busca e Listagem de Produtos.
 */
export class SearchPage extends BasePage {
  readonly searchInput: Locator;
  readonly productCards: Locator;
  readonly sortSelect: Locator;
  readonly limitSelect: Locator;
  readonly manufacturerFilterInput: Locator;
  readonly noResultsMessage: Locator;

  constructor(page: Page, lang = 'pt-br') {
    super(page, lang);
    this.searchInput = page.locator('.egen-search-bar input[name="search"], input[name="busca"]');
    this.productCards = page.locator('.egen-prod-card');
    this.sortSelect = page.locator('select[name="sort"], select[name="order"]');
    this.limitSelect = page.locator('select[name="limit"]');
    this.manufacturerFilterInput = page.locator('#manufacturer-search-input');
    this.noResultsMessage = page.locator('.egen-no-results, .text-center:has-text("Nenhum")');
  }

  /**
   * Abre a página de busca passando a query diretamente
   */
  async open(query = '') {
    await this.goto(`/busca${query ? `?search=${encodeURIComponent(query)}` : ''}`);
  }

  /**
   * Obtém a quantidade de produtos visíveis na listagem
   */
  async getProductCount(): Promise<number> {
    return this.productCards.count();
  }

  /**
   * Clica no primeiro produto retornado
   */
  async clickFirstProduct() {
    await this.productCards.first().locator('a').first().click();
  }

  /**
   * Adiciona um produto ao carrinho diretamente pelo card da listagem
   */
  async addProductToCart(index = 0) {
    const card = this.productCards.nth(index);
    await expect(card).toBeVisible();
    await card.scrollIntoViewIfNeeded();
    const addBtn = card.locator('.egen-prod-btn-cart, button[title*="carrinho" i]').first();
    await addBtn.click({ force: true });
  }
}
