import { Page, Locator, expect } from '@playwright/test';
import { BasePage } from './BasePage';

/**
 * Page Object para a Página Inicial (Home) da Alpha Engine.
 */
export class HomePage extends BasePage {
  readonly header: Locator;
  readonly logo: Locator;
  readonly topNav: Locator;
  readonly searchInput: Locator;
  readonly searchButton: Locator;
  readonly cartLink: Locator;
  readonly loginLink: Locator;
  readonly registerLink: Locator;
  readonly footer: Locator;

  constructor(page: Page, lang = 'pt-br') {
    super(page, lang);
    this.header = page.locator('.egen-header');
    this.logo = page.locator('.egen-logo');
    this.topNav = page.locator('.egen-top-nav');
    this.searchInput = page.locator('.egen-search-bar input[name="search"]');
    this.searchButton = page.locator('.egen-search-bar button[type="submit"]');
    this.cartLink = page.locator('a[href*="/carrinho"]');
    this.loginLink = page.locator('a[href*="/login"]');
    this.registerLink = page.locator('a[href*="/cadastro"]');
    this.footer = page.locator('.egen-footer, footer');
  }

  /**
   * Abre a Home Page
   */
  async open() {
    await this.goto('');
    await expect(this.header).toBeVisible();
  }

  /**
   * Executa uma busca por palavra-chave a partir da barra do topo
   */
  async search(query: string) {
    await this.searchInput.fill(query);
    await this.searchButton.click();
    await this.page.waitForLoadState('domcontentloaded');
  }
}
