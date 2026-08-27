import { test, expect } from '../../fixtures/test-fixtures';

test.describe('Módulo Frontend - Página Inicial (Home)', () => {
  test('deve carregar a Home Page com título, idioma e elementos estruturais', async ({ homePage, page }) => {
    // Escuta erros uncaught de JS no navegador
    const consoleErrors: string[] = [];
    page.on('pageerror', (err) => consoleErrors.push(err.message));

    await homePage.open();

    // Valida título da página
    await expect(page).toHaveTitle(/Alpha|AgSonhos/i);

    // Valida visibilidade dos componentes essenciais
    await expect(homePage.header).toBeVisible();
    await expect(homePage.logo).toBeVisible();
    await expect(homePage.searchInput).toBeVisible();
    await expect(homePage.footer).toBeVisible();

    // Valida meta tags CSRF de segurança
    const csrf = await homePage.getCsrfTokens();
    expect(csrf.name).toBeTruthy();
    expect(csrf.value).toBeTruthy();

    // Garante que não houve quebra catastrófica de script JS
    expect(consoleErrors).toHaveLength(0);
  });

  test('deve possuir estrutura de acessibilidade básica (WCAG)', async ({ homePage, makeAxeBuilder }) => {
    await homePage.open();
    
    // Executa análise de acessibilidade com axe-core
    const accessibilityScanResults = await makeAxeBuilder()
      .include('.egen-header')
      .analyze();

    // Verifica que não há violações críticas no header
    const criticalViolations = accessibilityScanResults.violations.filter(
      (v) => v.impact === 'critical'
    );
    expect(criticalViolations).toEqual([]);
  });
});
