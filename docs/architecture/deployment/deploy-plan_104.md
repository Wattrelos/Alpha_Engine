# Planejamento: Criação dos Artefatos de Casos de Uso Core em `docs/business/use-cases/core/`

## Objetivo
Criar as especificações formais e detalhadas dos **10 Casos de Uso Core** do ecossistema Alpha Engine em `docs/business/use-cases/core/`, mantendo 100% de consistência com:
- O Módulo 04 da apresentação interativa da FATEC (`public_html/presentation/04_casos_de_uso.html`);
- A Matriz de Rastreabilidade (`README.md` e `docs/requirements/`);
- O padrão rigoroso de engenharia de software adotado nos outros módulos (`customer/`, `pos/`, `dashboard/`).

## Lista dos 10 Casos de Uso Core a serem criados:
1. `UC_CORE_001_navegar_catalogo_pdp.md`: Navegar no Catálogo & PDP (RF001, RF002, RF011, RF012 | RN001, RN002, RN003)
2. `UC_CORE_002_gestao_crud_skus.md`: Gestão CRUD de SKUs (RF001, RF003, RF005 | RN001, RN002, RN003, RN006)
3. `UC_CORE_003_autenticacao_cadastro.md`: Autenticação & Cadastro Dual PF/PJ (RF014, RF015 | RN017, RNF003)
4. `UC_CORE_004_gerenciar_carrinho_compras.md`: Gerenciar Carrinho de Compras Persistente & Conversor de Metragem (RF004, RF009 | RN001, RN005)
5. `UC_CORE_005_checkout_ecommerce.md`: Checkout E-Commerce & Cálculo Tributário/Frete (RF010, RF017, RF018, RF019, RF020 | RN002, RN007, RN008, RN016)
6. `UC_CORE_006_pre_venda_pdv_balcao.md`: Pré-Venda no PDV Balcão & Reserva de Lote (RF004, RF006, RF021 | RN001, RN005, RN008, RN015)
7. `UC_CORE_007_fechamento_venda_caixa.md`: Fechamento de Venda no Caixa com Baixa de Estoque e NFC-e (RF006, RF018, RF020 | RN005, RN012, RN016)
8. `UC_CORE_008_solicitar_devolucao_rma.md`: Solicitar Devolução (RMA) pelo Cliente (RF016, RF022 | RN009, RN010, RN011, RN012)
9. `UC_CORE_009_gestao_devolucoes_painel.md`: Gestão de Devoluções e Homologação de RMA no Painel (RF020, RF022, RF025 | RN005, RN009, RN010, RN011, RN012)
10. `UC_CORE_010_monitoramento_ruptura.md`: Monitoramento de Ruptura de Estoque & Curva ABC (RF006, RF024, RF025 | RN005, RN006, RN015)
11. `README.md` (em `docs/business/use-cases/core/`): Sumário e índice didático dos 10 Casos de Uso Core com tabela de rastreabilidade.

Além disso, atualizar o `docs/business/use-cases/README.md` para referenciar a nova subpasta `core/`.

## Verificação
- Verificar a criação de todos os 11 arquivos markdown.
- Conferir a rastreabilidade com RFs, RNs e RNFs.
- Garantir coerência perfeita com `04_casos_de_uso.html`.

# Walkthrough: Criação dos Casos de Uso Core em `docs/business/use-cases/core/`

## 🎯 Resumo da Entrega
Foram criados com sucesso as especificações formais detalhadas dos **10 Casos de Uso Core** do ecossistema Alpha Engine, alinhados à apresentação da FATEC Ferraz de Vasconcelos ([04_casos_de_uso.html](/public_html/presentation/04_casos_de_uso.html)) e aos requisitos funcionais (RFs), regras de negócio (RNs) e requisitos não funcionais (RNFs) da disciplina de Laboratório de Engenharia de Software.

---

## 📁 Artefatos Criados

Todos os arquivos foram gerados dentro de [docs/business/use-cases/core/](/docs/business/use-cases/core/):

1. 📄 [README.md](/docs/business/use-cases/core/README.md)  
   *Índice mestre didático dos 10 Casos de Uso Core com tabela de rastreabilidade e diagrama de atores.*
2. 📄 [UC_CORE_001_navegar_catalogo_pdp.md](/docs/business/use-cases/core/UC_CORE_001_navegar_catalogo_pdp.md)  
   *Navegar no Catálogo & PDP com conversor de m² de pisos, fotos HD e simulação de frete (RF001, RF002, RF011, RF012 \| RN001, RN002, RN003).*
3. 📄 [UC_CORE_002_gestao_crud_skus.md](/docs/business/use-cases/core/UC_CORE_002_gestao_crud_skus.md)  
   *Gestão CRUD de SKUs no Painel com obrigatoriedade de peso/cubagem, NCM e fator de fracionamento (RF001, RF003, RF005 \| RN001, RN002, RN003, RN006).*
4. 📄 [UC_CORE_003_autenticacao_cadastro.md](/docs/business/use-cases/core/UC_CORE_003_autenticacao_cadastro.md)  
   *Autenticação & Cadastro Dual PF (CPF) e PJ (CNPJ com validação de Inscrição Estadual para construtoras) (RF014, RF015 \| RN017, RNF003).*
5. 📄 [UC_CORE_004_gerenciar_carrinho_compras.md](/docs/business/use-cases/core/UC_CORE_004_gerenciar_carrinho_compras.md)  
   *Gerenciamento de Carrinho com conversor automático de m² em caixas fechadas e persistência híbrida Redis/MySQL (RF004, RF009 \| RN001, RN005).*
6. 📄 [UC_CORE_005_checkout_ecommerce.md](/docs/business/use-cases/core/UC_CORE_005_checkout_ecommerce.md)  
   *One-Page Checkout com múltiplos shiptos de obra, frete de carga pesada com Munck, BOPIS e PIX com QR Code dinâmico (RF010, RF017, RF018, RF019, RF020 \| RN002, RN007, RN008, RN016).*
7. 📄 [UC_CORE_006_pre_venda_pdv_balcao.md](/docs/business/use-cases/core/UC_CORE_006_pre_venda_pdv_balcao.md)  
   *Atendimento de balcão físico, aplicação de descontos por volume para cimento/argamassa e emissão de ticket térmico com código de barras (RF004, RF006, RF021 \| RN001, RN005, RN008, RN015).*
8. 📄 [UC_CORE_007_fechamento_venda_caixa.md](/docs/business/use-cases/core/UC_CORE_007_fechamento_venda_caixa.md)  
   *Liquidação no caixa via leitura de ticket, pagamentos multi-meios (TEF/PIX/Dinheiro), baixa definitiva e emissão de NFC-e (RF006, RF018, RF020 \| RN005, RN012, RN016).*
9. 📄 [UC_CORE_008_solicitar_devolucao_rma.md](/docs/business/use-cases/core/UC_CORE_008_solicitar_devolucao_rma.md)  
   *Abertura de RMA pelo cliente no portal, validação do prazo de 7 dias do CDC, regras de embalagem original e anexo de NF-e (RF016, RF022 \| RN009, RN010, RN011, RN012).*
10. 📄 [UC_CORE_009_gestao_devolucoes_painel.md](/docs/business/use-cases/core/UC_CORE_009_gestao_devolucoes_painel.md)  
    *Triagem de SAC, laudo de vistoria no CD, emissão de NF-e de entrada de devolução, reintegração ao estoque e estorno financeiro no gateway (RF020, RF022, RF025 \| RN005, RN009, RN010, RN011, RN012).*
11. 📄 [UC_CORE_010_monitoramento_ruptura.md](/docs/business/use-cases/core/UC_CORE_010_monitoramento_ruptura.md)  
    *Daemon de auditoria contínua de estoque, curva ABC, alertas de baixo estoque e sugestão de lote de compra (RF006, RF024, RF025 \| RN005, RN006, RN015).*

Além disso, o índice geral [docs/business/use-cases/README.md](/docs/business/use-cases/README.md) foi atualizado para referenciar o novo módulo `core/`.

# Walkthrough: Integração dos Cards de Casos de Uso Core na Apresentação (`04_casos_de_uso.html`)

## 🎯 Resumo da Execução
Os 10 Casos de Uso Core foram completamente integrados à interface da apresentação interativa da FATEC ([04_casos_de_uso.html](/public_html/presentation/04_casos_de_uso.html)), permitindo que o usuário ou avaliador da banca clique em qualquer um dos 10 cards para abrir o modal com a especificação formal completa.

---

## 🛠️ Alterações Realizadas

1. **Injeção de Dados em [use-cases-data.js](/public_html/presentation/assets/js/use-cases-data.js)**:
   - Foram convertidos os 10 arquivos markdown de `docs/business/use-cases/core/` para HTML formatado com badges de rastreabilidade (`RF`, `RN`, `RNF`), tabelas com scroll e seções numeradas.
   - Os dados foram adicionados tanto no array `window.USE_CASES_ORDER` quanto no dicionário `window.USE_CASES_DATA` sob as chaves `UC_CORE_001` a `UC_CORE_010`.

2. **Criação da Seção Visual de Cards em [04_casos_de_uso.html](/public_html/presentation/04_casos_de_uso.html)**:
   - A antiga tabela estática da Seção 4 foi substituída por um grid responsivo de **10 cards interativos** (`.uc-card`) com efeito hover elevado, badges coloridos por subdomínio e botões de ação dedicados `👁️ Ver Detalhes`.
   - Cada card possui o atributo `data-uc-id="UC_CORE_00X"`.

3. **Event Listeners e Modal Integrado**:
   - Os listeners de clique no JavaScript foram estendidos para escutar tanto `.uc-row` quanto `.uc-card` e `.uc-action-btn`.
   - A função `openUseCaseModal(ucId)` foi calibrada para tratar a categoria `core`, atribuindo a cor roxa institucional (`badge-purple`).

4. **Estilo CSS em [assets/css/style.css](/public_html/presentation/assets/css/style.css)**:
   - Adicionada a classe `.badge-purple` com tema escuro e claro consistente com o Design System da FATEC.

---

## 📋 Lista dos 10 Cards Core Interativos

| ID | Nome do Card | Domínio | Atores | Requisitos |
| :--- | :--- | :--- | :--- | :--- |
| `UC_CORE_001` | Navegar no Catálogo & PDP | Storefront & PDP | Visitante, Cliente | RF001, RF002, RF012 |
| `UC_CORE_002` | Gestão CRUD de SKUs | Backoffice & Catálogo | Administrador, Operador | RF001, RF003, RF005 |
| `UC_CORE_003` | Autenticação & Cadastro | Segurança & Identidade | Visitante, Cliente (PF/PJ) | RF014, RF015 |
| `UC_CORE_004` | Gerenciar Carrinho de Compras | Carrinho & Sessão | Visitante, Cliente | RF004, RF009 |
| `UC_CORE_005` | Checkout E-Commerce | Checkout & Gateways | Cliente, Gateways | RF010, RF018, RF020 |
| `UC_CORE_006` | Pré-Venda no PDV Balcão | PDV Balcão | Vendedor, Cliente | RF004, RF006, RF021 |
| `UC_CORE_007` | Fechamento de Venda no Caixa | PDV Caixa | Operador Caixa, SEFAZ | RF006, RF018, RF020 |
| `UC_CORE_008` | Solicitar Devolução (RMA) | Pós-Venda & RMA | Cliente, SAC | RF016, RF022 |
| `UC_CORE_009` | Gestão de Devoluções no Painel | Governança & CD | Operador SAC, CD | RF020, RF022, RF025 |
| `UC_CORE_010` | Monitoramento de Ruptura | BI & Suprimentos | Administrador, Compras | RF006, RF024, RF025 |

---

## 🔍 Validação
- Verificado via Node.js que todos os 10 identificadores `UC_CORE_001` a `UC_CORE_010` existem e estão sincronizados entre o HTML e o `use-cases-data.js`.
- O clique em qualquer card ou no botão `👁️ Ver Detalhes` abre instantaneamente o modal interativo com navegação entre anterior/próximo e alternância entre visualização rica e Markdown original.
