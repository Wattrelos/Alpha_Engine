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

  test('deve criar e publicar um novo pedido de orçamento (RFQ) sem erro de CSRF', async ({ loginPage, page }) => {
    // 1. Autentica como cliente
    await loginPage.open();
    await loginPage.login('prestador.teste@agsonhos.com.br', 'Teste@123');
    await page.waitForURL(url => !url.pathname.includes('/login'), { timeout: 10000 });

    // 2. Acessa o formulário de novo projeto
    await page.goto('/pt-br/projetos/novo');
    await expect(page.locator('h1.rfq-hero-title')).toContainText('Solicitar Orçamento de Projeto');

    // 3. Preenche os campos obrigatórios
    await page.fill('#title', 'Reforma Completa de Fachada E2E');
    await page.selectOption('#category', 'pintura');
    await page.fill('#budget_expectation', '4500.00');
    await page.fill('#desired_deadline_days', '20');
    await page.fill('#description', 'Pintura externa e impermeabilização da fachada com materiais de primeira linha.');
    await page.fill('#address_cep', '01310-100');
    await page.fill('#address_city', 'São Paulo');
    await page.fill('#address_state', 'SP');

    // 4. Submete o formulário clicando em "Publicar Pedido de Orçamento"
    await page.click('#btn-submit-rfq');

    // 5. Valida que NÃO houve erro 400 de CSRF
    await expect(page.locator('text=400 - Requisição Rejeitada (CSRF)')).not.toBeVisible();
    await expect(page.locator('text=Sua sessão expirou')).not.toBeVisible();

    // 6. Confirma redirecionamento para a lista de projetos do cliente
    await expect(page).toHaveURL(/\/pt-br\/account\/projetos/, { timeout: 10000 });
  });

  test('deve adicionar produto ao orçamento a partir da página de detalhes do produto', async ({ loginPage, page }) => {
    page.on('console', msg => console.log('PAGE LOG:', msg.text()));
    page.on('response', async res => {
      if (res.url().includes('projetos')) {
        console.log('RESPONSE:', res.url(), res.status(), await res.text().catch(() => ''));
      }
    });

    // 1. Autentica como cliente
    await loginPage.open();
    await loginPage.login('prestador.teste@agsonhos.com.br', 'Teste@123');
    await page.waitForURL(url => !url.pathname.includes('/login'), { timeout: 10000 });

    // 2. Acessa a PDP de um produto com estoque
    await page.goto('/pt-br/produto/2');
    const addToQuoteBtn = page.locator('#btn-add-to-quote-pdp');
    await expect(addToQuoteBtn).toBeVisible();

    // 3. Clica em "Adicionar ao Orçamento"
    await addToQuoteBtn.click({ force: true });

    // 4. Modal de escolha do projeto deve abrir
    const modalWrapper = page.locator('#egen-quote-modal-wrapper');
    await expect(modalWrapper).toBeVisible();

    // 5. Configura listener para capturar o alert de confirmação
    let dialogMessage = '';
    page.once('dialog', async dialog => {
      dialogMessage = dialog.message();
      console.log('DIALOG MESSAGE:', dialogMessage);
      await dialog.accept();
    });

    // 6. Clica no primeiro projeto listado no modal
    const projectItem = modalWrapper.locator('.egen-quote-project-item').first();
    await expect(projectItem).toBeVisible();
    await projectItem.click({ force: true });

    // 7. Valida que o alerta informa sucesso e não erro de comunicação
    await expect.poll(() => dialogMessage).toContain('adicionado com sucesso');
    expect(dialogMessage).not.toContain('Erro ao enviar requisição para o orçamento');
  });

});


