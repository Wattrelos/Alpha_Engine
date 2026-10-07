import { test, expect } from '../../fixtures/test-fixtures';

test.describe('Módulo 4: Portal do Prestador de Serviços (Alpha Pro - UC_PRV_001 a UC_PRV_003)', () => {

  test('deve redirecionar usuário anônimo para o login ao tentar acessar o portal do prestador', async ({ page }) => {
    // Acessa rota raiz do prestador (testa também fallback de idioma)
    const response = await page.goto('/prestador/oportunidades');
    await expect(page).toHaveURL(/\/pt-br\/login/);
  });

  test('deve exibir os links do Portal do Prestador na navegação do topo e no rodapé', async ({ homePage, page }) => {
    await homePage.open();

    // Valida link no rodapé
    const footerLink = page.locator('.ag-footer a[href*="prestador/oportunidades"]');
    await expect(footerLink).toBeVisible();
    await expect(footerLink).toContainText('Portal do Prestador');

    // Valida link no menu da top-nav
    const topNavLink = page.locator('.egen-top-nav a[href*="prestador/oportunidades"]');
    await expect(topNavLink).toHaveCount(1);
  });

  test('deve responder com sucesso na API de busca de produtos para a ferramenta Takeoff', async ({ request }) => {
    const res = await request.get('/pt-br/api/produtos/buscar-takeoff?q=cimento');
    expect(res.status()).toBe(200);

    const json = await res.json();
    expect(Array.isArray(json)).toBe(true);
    if (json.length > 0) {
      expect(json[0]).toHaveProperty('id');
      expect(json[0]).toHaveProperty('name');
      expect(json[0]).toHaveProperty('formatted_price');
    }
  });

  test('deve autenticar o prestador, listar oportunidades no raio e permitir envio de proposta e uso da Takeoff Tool', async ({ loginPage, page }) => {
    // 1. Login com o prestador de teste cadastrado
    await loginPage.open();
    await loginPage.login('prestador.teste@agsonhos.com.br', 'Teste@123');

    // Aguarda conclusão do login e redirecionamento
    await page.waitForURL(url => !url.pathname.includes('/login'), { timeout: 10000 });

    // 2. Acesso ao Feed de Oportunidades no Raio (UC_PRV_001)
    await page.goto('/pt-br/prestador/oportunidades');
    await expect(page.locator('h1.rfq-hero-title')).toContainText('Portal do Prestador de Serviços');

    // Valida que ao menos uma oportunidade está visível
    const oppCard = page.locator('[id^="opportunity-card-"]').first();
    await expect(oppCard).toBeVisible();

    // 3. Submeter Proposta Comercial de Mão de Obra (UC_PRV_002)
    const bidBtn = page.locator('a[id^="btn-bid-"]').first();
    if (await bidBtn.isVisible()) {
      await bidBtn.click();
      await expect(page.locator('h1.rfq-hero-title')).toContainText('Enviar Proposta Comercial');

      // Preenche os dados da proposta
      await page.fill('#labor_price', '3200.00');
      await page.fill('#estimated_duration_days', '12');
      await page.fill('#proposal_notes', 'Proposta automatizada de teste E2E com equipe técnica especializada.');

      // Submete o formulário
      await page.click('#btn-submit-bid');

      // Retorna para a página de oportunidades com feedback
      await expect(page).toHaveURL(/\/pt-br\/prestador\/oportunidades/, { timeout: 10000 });
    }

    // 4. Acessar a Ferramenta de Levantamento de Materiais (Takeoff Tool - UC_PRV_003)
    await page.goto('/pt-br/prestador/projetos/3/takeoff');
    await expect(page.locator('h1.rfq-hero-title')).toContainText('Takeoff Tool');
    await expect(page.locator('#takeoff-table-body')).toBeVisible();

    // Valida que os itens existentes estão na tabela
    const rows = page.locator('#takeoff-table-body tr');
    await expect(rows.first()).toBeVisible();

    // Adiciona um novo insumo manualmente
    await page.fill('#item-name-input', 'Espaçador Nivelador de Piso 1.5mm');
    await page.selectOption('#unit-select', 'un');
    await page.fill('#quantity-input', '500');
    await page.fill('#unit-price-input', '0.35');
    await page.fill('#item-notes-input', 'Para assentamento uniforme');

    // Clica para inserir no BoQ
    await page.click('#btn-add-takeoff-item');

    // Valida recálculo do total do BoQ
    await expect(page.locator('#takeoff-total-display')).toContainText('R$');
  });

});
