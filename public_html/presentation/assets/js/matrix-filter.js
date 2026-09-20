/**
 * Beta Engine - INTERATIVIDADE DA MATRIZ DE RASTREABILIDADE
 * Filtro por texto, destaque cruzado bidirecional e painel dinâmico de detalhes
 */

(function () {
  'use strict';

  // Base de descrições resumidas para exibição no painel dinâmico
  const RN_DESCRIPTIONS = {
    'RN001': 'Variações e Unidades de Venda: Cálculo fracionado (m², caixas, unidades com arredondamento seguro para cima).',
    'RN002': 'Cubagem e Peso: Dimensões (C x L x A) e peso bruto obrigatórios para cálculo logístico de cubagem.',
    'RN003': 'Impostos na Origem e Destino: Cálculo automatizado de ICMS-ST e DIFAL por UF de destino com regras tributárias.',
    'RN004': 'Kits e Combos de Produtos: Comercialização de pacotes vinculada ao saldo de todos os componentes.',
    'RN005': 'Controle Rigoroso de Estoque em Tempo Real: Bloqueio otimista para prevenir overselling entre PDV e E-commerce.',
    'RN006': 'Alerta de Ruptura (Stockout): Notificação proativa para reposição ao atingir o ponto de pedido mínimo.',
    'RN007': 'Múltiplas Opções de Frete por Cubagem: Roteamento inteligente entre Correios e transportadoras pesadas.',
    'RN008': 'Frete Grátis e BOPIS: Retirada imediata no balcão da loja e isenção regional por valor mínimo de pedido.',
    'RN009': 'Autenticação e Cadastro Dual (PF/PJ): Validação de CPF/CNPJ e checagem de Inscrição Estadual (IE).',
    'RN010': 'LGPD e Direito ao Esquecimento: Anonimização de dados cadastrais preservando histórico fiscal e contábil.',
    'RN011': 'Política de Devolução (CDC): Devolução por arrependimento em até 7 dias corridos e 90 dias para defeitos.',
    'RN012': 'Rastreamento e Notificação: Atualizações de status de entrega enviadas via e-mail e webhook.',
    'RN013': 'Avaliações Moderadas: Avaliações públicas de produtos liberadas após auditoria ou verificação de compra.',
    'RN014': 'Múltiplos Endereços (Shiptos): Suporte a endereços alternativos de entrega vinculados à mesma conta.',
    'RN015': 'Multi-meios de Pagamento: Processamento seguro de PIX com QR Code dinâmico, Boleto e Cartão de Crédito.',
    'RN016': 'Idempotência em Transações: Chaves de idempotência anti-duplo clique para prevenção de cobrança duplicada.',
    'RN017': 'Faturamento e Emissão Fiscal: Geração de chave de acesso NF-e / NFC-e sincronizada com a SEFAZ.',
    'RN018': 'Segmentação e Descontos: Preços e condições diferenciadas para clientes corporativos (B2B) e atacado.'
  };

  const RF_DESCRIPTIONS = {
    'RF001': 'Cadastro de produtos com fotos em alta resolução',
    'RF002': 'Exibição de especificações técnicas por categoria',
    'RF003': 'Categorização e taxonomia hierárquica de produtos',
    'RF004': 'Venda fracionada e múltiplas unidades de medida',
    'RF005': 'Gestão administrativa (CRUD) de produtos e SKUs',
    'RF006': 'Gestão automática e baixa de inventário em tempo real',
    'RF007': 'Criação e comercialização de kits/combos promocionais',
    'RF008': 'Sugestão de produtos correlatos (Cross-selling)',
    'RF009': 'Carrinho de compras interativo e persistente',
    'RF010': 'Cálculo de frete dinâmico por peso, cubagem e CEP',
    'RF011': 'Mecanismo de busca indexada e filtros avançados',
    'RF012': 'Página de detalhes do produto (PDP) dedicada',
    'RF013': 'Sistema de avaliações, classificação e comentários',
    'RF014': 'Autenticação segura e cadastro de clientes (PF/PJ)',
    'RF015': 'Recuperação de credenciais e redefinição de senha',
    'RF016': 'Histórico e rastreamento de compras pelo cliente',
    'RF017': 'Gestão de múltiplos endereços de entrega (Shiptos)',
    'RF018': 'Checkout multi-meios (PIX, Cartão, Boleto)',
    'RF019': 'Integração com gateway de pagamento seguro',
    'RF020': 'Faturamento e emissão de Nota Fiscal Eletrônica (NF-e)',
    'RF021': 'Modalidades de entrega e retirada na loja (BOPIS)',
    'RF022': 'Rastreamento last-mile e atualização de status',
    'RF023': 'Gestão de catálogo global e precificação segmentada',
    'RF024': 'Alerta proativo de ruptura de estoque (Stockout)',
    'RF025': 'Painel analítico de vendas e relatórios gerenciais'
  };

  document.addEventListener('DOMContentLoaded', () => {
    const matrixTable = document.getElementById('traceabilityMatrix');
    const searchInput = document.getElementById('matrixSearch');
    const detailBox = document.getElementById('matrixDetailBox');
    const detailRn = document.getElementById('detailRnText');
    const detailRf = document.getElementById('detailRfText');
    const detailRel = document.getElementById('detailRelText');

    if (!matrixTable) return;

    // Filtro por texto
    if (searchInput) {
      searchInput.addEventListener('input', (e) => {
        const query = e.target.value.toLowerCase().trim();
        const rows = matrixTable.querySelectorAll('tbody tr');

        rows.forEach((row) => {
          const rowText = row.textContent.toLowerCase();
          if (rowText.includes(query)) {
            row.style.display = '';
          } else {
            row.style.display = 'none';
          }
        });
      });
    }

    // Interatividade nas Células da Matriz
    const cells = matrixTable.querySelectorAll('tbody td');
    const headers = matrixTable.querySelectorAll('thead th');

    cells.forEach((cell) => {
      cell.addEventListener('mouseenter', () => {
        const row = cell.closest('tr');
        if (!row) return;

        const cellIndex = cell.cellIndex;
        const colHeader = headers[cellIndex];
        const rowHeader = row.querySelector('th, td:first-child');

        const rnCode = rowHeader?.dataset?.rn || rowHeader?.textContent.match(/RN\d+/)?.[0] || '';
        const rfCode = colHeader?.dataset?.rf || colHeader?.textContent.match(/RF\d+/)?.[0] || '';

        // Destaque visual
        cells.forEach(c => c.classList.remove('active-cell'));
        cell.classList.add('active-cell');

        if (detailBox && rnCode && rfCode) {
          const isMapped = cell.textContent.includes('X');
          if (detailRn) detailRn.innerHTML = `<strong>${rnCode}:</strong> ${RN_DESCRIPTIONS[rnCode] || 'Regra de Negócio'}`;
          if (detailRf) detailRf.innerHTML = `<strong>${rfCode}:</strong> ${RF_DESCRIPTIONS[rfCode] || 'Requisito Funcional'}`;
          if (detailRel) {
            if (isMapped) {
              detailRel.innerHTML = `<span class="badge badge-green">✓ Rastreabilidade Validada: ${rfCode} implementa ${rnCode}</span>`;
            } else {
              detailRel.innerHTML = `<span class="badge badge-amber">Sem relacionamento direto entre ${rnCode} e ${rfCode}</span>`;
            }
          }
        }
      });
    });
  });

})();
