import { Page, Locator, expect } from '@playwright/test';

/**
 * Classe Base para todos os Page Objects da Alpha Engine.
 * Fornece métodos utilitários de navegação, manipulação de sessão,
 * detecção de erros de console e validação de cabeçalhos de segurança.
 */
export class BasePage {
  readonly page: Page;
  readonly defaultLang: string;

  constructor(page: Page, defaultLang = 'pt-br') {
    this.page = page;
    this.defaultLang = defaultLang;
  }

  /**
   * Navega para um caminho relativo levando em consideração o prefixo de idioma.
   */
  async goto(path = '', options?: Parameters<Page['goto']>[1]) {
    const cleanPath = path.startsWith('/') ? path : `/${path}`;
    const localizedPath = `/${this.defaultLang}${cleanPath === '/' ? '' : cleanPath}`;
    return this.page.goto(localizedPath, { waitUntil: 'domcontentloaded', ...options });
  }

  /**
   * Obtém o token CSRF embutido nas meta tags da página.
   */
  async getCsrfTokens(): Promise<{ name: string | null; value: string | null }> {
    const name = await this.page.locator('meta[name="csrf-name"]').getAttribute('content');
    const value = await this.page.locator('meta[name="csrf-value"]').getAttribute('content');
    return { name, value };
  }

  /**
   * Aguarda que a rede esteja ociosa e os componentes JavaScript carregados.
   */
  async waitForReady() {
    await this.page.waitForLoadState('networkidle');
  }

  /**
   * Obtém os cabeçalhos de resposta HTTP da última requisição.
   */
  async getLastResponseHeaders() {
    const response = await this.page.reload();
    return response?.headers() || {};
  }

  /**
   * Monitora erros não tratados no console do navegador (JavaScript uncaught errors).
   */
  captureConsoleErrors(): string[] {
    const errors: string[] = [];
    this.page.on('pageerror', (err) => errors.push(err.message));
    return errors;
  }

  /**
   * Valida se a página possui um título e charset válidos.
   */
  async assertBasicSeo() {
    await expect(this.page).toHaveTitle(/.+/);
    const metaCharset = this.page.locator('meta[charset]');
    await expect(metaCharset).toHaveCount(1);
  }
}
