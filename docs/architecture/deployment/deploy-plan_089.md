# DP-89: Implementação do Módulo de Cotação de Projetos e Prestadores de Serviço (RFQ, BoQ e Material Takeoff)

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-08-24 18:48:51
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/89

## Descrição

# Plano de Implementação: Módulo de Cotação de Projetos e Prestadores de Serviço (RFQ, BoQ e Material Takeoff)

Implementação do módulo de **Cotação de Serviços e Insumos** da Alpha Engine, cobrindo os requisitos funcionais **RF033 a RF037** conforme especificado em [products_quotation.yaml](file:///var/www/html/agsonhos/docs/requirements/functional/products_quotation.yaml). O sistema integrará a publicação de projetos pelo cliente, matching geoespacial de prestadores em raio $X$ km, comparação de propostas (*Bid Comparison*), levantamento técnico de materiais (*Material Takeoff / Bill of Quantities - BoQ*) e conversão direta para compras no e-commerce (*Add to Quote*).

---

## User Review Required

> [!IMPORTANT]
> **Estratégia de Geolocalização e Matching de Prestadores (RF034)**:
> O cálculo de distância entre o endereço da obra do cliente e os prestadores pode ser realizado de duas formas:
> 1. **Cálculo Geoespacial por Coordenadas (Fórmula de Haversine / Lat-Long)**: Permite raio circular preciso (ex: 25 km). Requer obtenção de latitude/longitude no cadastro do CEP/endereço (via API ViaCEP + Nominatim/Google Maps ou tabela local de CEPs).
> 2. **Matching por Faixa de CEP / Cidade / Zona (GeoZones)**: Utiliza a estrutura já existente de `agsc_geo_cities` e `agsc_geo_zones`.
> *Nossa proposta inicial adota o cálculo de Haversine com fallback por cidade/estado.*

> [!NOTE]
> **Perfil de Acesso do Prestador**:
> Propomos criar um grupo específico de clientes (`CustomerGroup: Prestador de Serviços / Pro`) ou perfil dedicado para permitir que o mesmo usuário acesse a área profissional sem duplicar o mecanismo de autenticação existente.

---

## Open Questions

> [!NOTE]
> 1. **Importação de Planilha no BoQ (RF036)**: Deseja suporte imediato a upload de arquivos `.xlsx` / `.csv` para preenchimento em lote da lista de materiais (BoQ), além da busca e seleção manual de SKUs do catálogo?
> 2. **Validade e Negociação de Preço no RFQ (RF037)**: Quando os itens do BoQ forem convertidos em cotação no e-commerce, o cliente deve receber automaticamente os descontos por volume (RN015) do catálogo ou o lojista/administrador poderá aplicar um desconto customizado negociado antes do fechamento?

---

## Proposed Changes

### 1. Banco de Dados & Esquema de Dados (Schema)

Criação das tabelas relacionais necessárias no banco de dados da Alpha Engine.

#### [NEW] [rfq_service_provider_tables.sql](file:///var/www/html/agsonhos/backend/resources/schema/rfq_service_provider_tables.sql)
- `agsc_service_provider_profile`: Perfil do prestador (especialidades, raio de atendimento em km, latitude, longitude, avaliação média).
- `agsc_project_rfq`: Solicitação de orçamento emitida pelo cliente (título, descrição, endereço da obra, CEP, coordenadas, data limite, status).
- `agsc_project_bid`: Propostas de serviço dos prestadores (mão de obra, prazo estimado em dias, detalhes técnicos, status).
- `agsc_project_boq`: Lista técnica quantitativa de materiais (*Bill of Quantities*) gerada pelo profissional para a obra.
- `agsc_project_boq_item`: Itens vinculados ao catálogo de produtos ou insumos genéricos (quantidade, unidade de medida, preço unitário de referência).

---

### 2. Camada de Domínio & Entidades (Model / Domain)

Criação das entidades, DTOs e Repositórios seguindo o padrão de arquitetura da Alpha Engine.

#### [NEW] [ProjectRfq.php](file:///var/www/html/agsonhos/backend/core/Model/Domain/Entities/Quotation/ProjectRfq.php)
#### [NEW] [ProjectBid.php](file:///var/www/html/agsonhos/backend/core/Model/Domain/Entities/Quotation/ProjectBid.php)
#### [NEW] [ProjectBoq.php](file:///var/www/html/agsonhos/backend/core/Model/Domain/Entities/Quotation/ProjectBoq.php)
#### [NEW] [ProjectBoqItem.php](file:///var/www/html/agsonhos/backend/core/Model/Domain/Entities/Quotation/ProjectBoqItem.php)
#### [NEW] [ServiceProviderProfile.php](file:///var/www/html/agsonhos/backend/core/Model/Domain/Entities/Quotation/ServiceProviderProfile.php)

#### [NEW] [ProjectRfqRepository.php](file:///var/www/html/agsonhos/backend/core/Model/Domain/Repositories/ProjectRfqRepository.php)
#### [NEW] [ProjectBidRepository.php](file:///var/www/html/agsonhos/backend/core/Model/Domain/Repositories/ProjectBidRepository.php)
#### [NEW] [ProjectBoqRepository.php](file:///var/www/html/agsonhos/backend/core/Model/Domain/Repositories/ProjectBoqRepository.php)
#### [NEW] [ServiceProviderProfileRepository.php](file:///var/www/html/agsonhos/backend/core/Model/Domain/Repositories/ServiceProviderProfileRepository.php)

---

### 3. Camada de Serviços de Negócio (Services)

#### [NEW] [GeoMatchingService.php](file:///var/www/html/agsonhos/backend/core/Services/Quotation/GeoMatchingService.php)
- Implementação da fórmula de Haversine para filtrar prestadores com raio de cobertura compatível com o CEP/local da obra.
- Despacho de notificações/eventos de novas oportunidades para os prestadores da região.

#### [NEW] [BoqToCartConverterService.php](file:///var/www/html/agsonhos/backend/core/Services/Quotation/BoqToCartConverterService.php)
- Conversão automatizada de itens do BoQ em itens de carrinho (`agsc_cart`) ou pedido de cotação formal (`Add to Quote`).
- Aplicação das regras de negócio de precificação progressiva por volume (RN015) e tipo de cliente (RN017).

---

### 4. Controladores e Ações Web/API (Controllers & Actions)

#### [NEW] [CustomerRfqActions](file:///var/www/html/agsonhos/backend/core/Controller/Actions/Quotation/Customer/)
- `CreateProjectRfqAction.php`: Formulário e processamento de criação do projeto (RFQ).
- `ListCustomerProjectsAction.php`: Painel do cliente para acompanhar projetos em andamento.
- `ShowBidComparisonAction.php`: Painel comparativo de propostas (*Bid Comparison*).
- `AcceptBidAction.php`: Seleção e contratação do profissional.
- `ApproveBoqAndAddToCartAction.php`: Validação do BoQ e envio ao carrinho de compras.

#### [NEW] [ProviderRfqActions](file:///var/www/html/agsonhos/backend/core/Controller/Actions/Quotation/Provider/)
- `ListOpportunitiesAction.php`: Feed de projetos disponíveis dentro do raio de atendimento do prestador.
- `SubmitBidAction.php`: Envio de orçamento de mão de obra.
- `MaterialTakeoffAction.php`: Ferramenta do prestador (*Takeoff Tool*) para montar a lista de materiais (BoQ).
- `SearchCatalogItemsAction.php`: Endpoint JSON para busca e seleção rápida de SKUs na montagem do BoQ.

#### [MODIFY] [Routes.php](file:///var/www/html/agsonhos/backend/Config/Routes.php)
- Registro das rotas internacionalizadas sob `/{lang}/projetos` e `/{lang}/prestador`.

---

### 5. Frontend & Componentes de Interface (Twig, CSS, JS)

#### [NEW] [project-rfq-form.twig](file:///var/www/html/agsonhos/backend/resources/views/pages/quotation/project-rfq-form.twig)
- Formulário intuitivo para postagem de obra com especificação técnica, upload de fotos/plantas e endereço.

#### [NEW] [bid-comparison.twig](file:///var/www/html/agsonhos/backend/resources/views/pages/quotation/bid-comparison.twig)
- Interface de comparação lado a lado de propostas de profissionais (preço, prazo, avaliação).

#### [NEW] [takeoff-tool.twig](file:///var/www/html/agsonhos/backend/resources/views/pages/quotation/takeoff-tool.twig)
- Interface rica para o prestador montar o *Bill of Quantities*, pesquisando produtos do catálogo, definindo quantidades e visualizando o valor total estimado.

#### [NEW] [quotation.css](file:///var/www/html/agsonhos/public_html/css/custom/quotation.css)
#### [NEW] [quotation.js](file:///var/www/html/agsonhos/public_html/js/custom/quotation.js)

---

## Verification Plan

### Testes Automatizados (BDD com Behat)

Criação dos cenários Gherkin para validação end-to-end de cada requisito:

1. **`features/services/rfq_criacao_projeto.feature`**:
   - Validar envio de RFQ por cliente autenticado (RF033).
   - Validar bloqueio de publicação sem localização/CEP válido.
2. **`features/services/matching_prestadores_raio.feature`**:
   - Validar matching geoespacial por raio de atendimento (RF034).
   - Prestadores fora do raio não visualizam a oportunidade.
3. **`features/services/comparacao_propostas_bid.feature`**:
   - Validar submissão de orçamentos e painel comparativo (RF035).
4. **`features/services/material_takeoff_boq.feature`**:
   - Validar montagem do BoQ pelo profissional contratado (RF036).
5. **`features/services/conversao_boq_carrinho.feature`**:
   - Validar conversão do BoQ em itens de carrinho com descontos aplicados (RF037).

Comandos para execução:
```bash
./vendor/bin/behat features/services/ --no-snippets
cd backend && ./vendor/bin/phpunit --filter Quotation
```

### Testes Manuais de Navegação
1. Criar um projeto como Cliente no painel `/{lang}/projetos/novo`.
2. Como Prestador A (dentro do raio de 25 km), visualizar oportunidade no feed e submeter proposta.
3. Como Cliente, acessar o painel de comparação (*Bid Comparison*), selecionar o Prestador A.
4. Como Prestador A, acessar a ferramenta de *Takeoff*, adicionar 3 produtos do catálogo ao BoQ e submeter.
5. Como Cliente, revisar a lista de insumos e clicar em "Adicionar ao Carrinho / Cotação", verificando a persistência no checkout.

# Walkthrough: Módulo de Cotação de Projetos, Prestadores de Serviço e Levantamento de Materiais (RFQ, BoQ e Takeoff Tool)

Implementação completa dos requisitos funcionais **RF033 a RF037** na plataforma **Alpha Engine**, com suporte adicional a **Importação de Planilhas em Lote (CSV/TSV)** e **Desconto Progressivo por Volume (RN015)**.

---

## 1. O que foi Implementado

### 1.1. Importação em Lote de Planilhas (CSV / TSV / Excel) — RF036
- [BoqSpreadsheetImportService.php](file:///var/www/html/agsonhos/backend/core/Services/Quotation/BoqSpreadsheetImportService.php):
  - Detecção automática e tolerante de delimitadores (vírgula, ponto e vírgula, tabulação).
  - Reconhecimento inteligente de cabeçalhos em português e inglês (`Nome do Item`, `Unidade`, `Quantidade`, `Preço Unitário`, `SKU`, `Observações`).
  - Normalização de encodings (UTF-8, ISO-8859-1, Windows-1252) e tratamento numérico resiliente.
  - Inserção em lote na tabela `agsc_project_boq_item` e recálculo automático do valor consolidado do BoQ.

### 1.2. Desconto Progressivo por Volume — RN015 / RF037
- [BoqToCartConverterService.php](file:///var/www/html/agsonhos/backend/core/Services/Quotation/BoqToCartConverterService.php):
  - Tiers progressivos de desconto por quantidade no BoQ:
    - **10+ unidades**: 5% de desconto
    - **50+ unidades**: 10% de desconto
    - **100+ unidades**: 15% de desconto
    - **250+ unidades**: 20% de desconto
  - Integração com descontos cadastrados no banco via [ProductDiscountRepository](file:///var/www/html/agsonhos/backend/core/Model/Domain/Repositories/ProductDiscountRepository.php).
  - Cálculo de economia total (*savings*), percentual global de desconto e preços unitários efetivos.

### 1.3. Esquema de Banco de Dados & Migração
- [rfq_service_provider_tables.sql](file:///var/www/html/agsonhos/backend/resources/schema/rfq_service_provider_tables.sql):
  - `agsc_service_provider_profile`: Perfil profissional, especialidades, raio de cobertura em km e geolocalização.
  - `agsc_project_rfq`: Solicitações de orçamento de projetos emitidas por clientes (RF033).
  - `agsc_project_bid`: Propostas comerciais de mão de obra enviadas pelos prestadores (RF035).
  - `agsc_project_boq`: Listas quantitativas de materiais (*Bill of Quantities*) geradas pelos profissionais (RF036).
  - `agsc_project_boq_item`: Itens e insumos individuais vinculados ao catálogo ou customizados.

### 1.4. Camada de Domínio & Repositórios
- **Entidades**: [ProjectRfq](file:///var/www/html/agsonhos/backend/core/Model/Domain/Entities/Quotation/ProjectRfq.php), [ProjectBid](file:///var/www/html/agsonhos/backend/core/Model/Domain/Entities/Quotation/ProjectBid.php), [ProjectBoq](file:///var/www/html/agsonhos/backend/core/Model/Domain/Entities/Quotation/ProjectBoq.php), [ProjectBoqItem](file:///var/www/html/agsonhos/backend/core/Model/Domain/Entities/Quotation/ProjectBoqItem.php) e [ServiceProviderProfile](file:///var/www/html/agsonhos/backend/core/Model/Domain/Entities/Quotation/ServiceProviderProfile.php).
- **Repositórios**: [ProjectRfqRepository](file:///var/www/html/agsonhos/backend/core/Model/Domain/Repositories/ProjectRfqRepository.php), [ProjectBidRepository](file:///var/www/html/agsonhos/backend/core/Model/Domain/Repositories/ProjectBidRepository.php), [ProjectBoqRepository](file:///var/www/html/agsonhos/backend/core/Model/Domain/Repositories/ProjectBoqRepository.php) e [ServiceProviderProfileRepository](file:///var/www/html/agsonhos/backend/core/Model/Domain/Repositories/ServiceProviderProfileRepository.php).

### 1.5. Interface e Componentes Visuais (Twig, CSS, JS)
- [takeoff-tool.twig](file:///var/www/html/agsonhos/backend/resources/views/pages/quotation/takeoff-tool.twig): Área de upload de arquivos CSV/TSV com feedback em tempo real e autocomplete de SKUs.
- [customer-boq-view.twig](file:///var/www/html/agsonhos/backend/resources/views/pages/quotation/customer-boq-view.twig): Banner de economia com cálculo detalhado dos descontos progressivos por volume (RN015) e conversão para o carrinho (*Add to Quote*).
- [project-rfq-form.twig](file:///var/www/html/agsonhos/backend/resources/views/pages/quotation/project-rfq-form.twig): Cadastro de projeto com CEP e endereço para geolocalização.
- [bid-comparison.twig](file:///var/www/html/agsonhos/backend/resources/views/pages/quotation/bid-comparison.twig): Comparação analítica de orçamentos de mão de obra.
- [provider-opportunities.twig](file:///var/www/html/agsonhos/backend/resources/views/pages/quotation/provider-opportunities.twig): Feed de oportunidades filtrado por raio de atendimento em km (RF034).
- [quotation.css](file:///var/www/html/agsonhos/public_html/css/custom/quotation.css) e [quotation.js](file:///var/www/html/agsonhos/public_html/js/custom/quotation.js): Estilização e scripts de reatividade AJAX.

---

## 2. Validação e Testes Automatizados

### 2.1. Testes Unitários Automatizados (PHPUnit)
Arquivo: [QuotationAndTakeoffValidationTest.php](file:///var/www/html/agsonhos/tests/Validation/QuotationAndTakeoffValidationTest.php)

Cenários cobertos:
1. `testHaversineDistanceCalculation`: Validação de cálculo geodésico Haversine.
2. `testProviderEligibleInsideRadius`: Matching de prestadores no raio.
3. `testProviderRejectedOutsideRadius`: Rejeição de prestadores fora do raio.
4. `testProviderMatchingFallbackByCity`: Matching resiliente por cidade/estado.
5. `testProjectRfqEntityAttributes`: Validação de integridade do modelo RFQ.
6. `testProjectBidEntityAttributes`: Validação de propostas comerciais de mão de obra.
7. `testVolumeDiscountCalculation`: Cálculo de descontos por volume e faixas progressivas (RN015).
8. `testSpreadsheetImportService`: Importação de arquivo CSV com delimitadores e colunas dinâmicas.
9. `testConvertBoqToCartWithVolumeDiscounts`: Conversão integral para carrinho com descontos aplicados.

**Resultado da execução da suíte completa**:
```bash
./vendor/bin/phpunit
OK (79 tests, 269 assertions)
```


# Walkthrough: Módulo de Cotação de Projetos, Prestadores de Serviço, Minha Conta e Carrinho

Implementação completa dos requisitos funcionais **RF033 a RF037** na plataforma **Alpha Engine**, com o botão **"Adicionar ao Orçamento" (Add to Quote)** na PDP e Vitrine, correção de persistência na área **Minha Conta** e suporte a identificadores de sessão criptográficos de alta entropia (64+ caracteres) no carrinho.

---

## 1. O que foi Corrigido e Ajustado

### 1.1. Resolução do Erro de Truncamento de `session_id` ao Adicionar ao Carrinho
- **Causa Identificada**:
  - A coluna `session_id` na tabela `agsc_cart` estava definida como `VARCHAR(32)`.
  - Ao gerar sessões criptograficamente seguras com `bin2hex(random_bytes(32))` (64 caracteres) ou hashes modernos de sessão, o MySQL em strict mode (`STRICT_TRANS_TABLES`) bloqueava a inserção disparando o erro: `SQLSTATE[22001]: String data, right truncated: 1406 Data too long for column 'session_id'`.
- **Correções Aplicadas**:
  - **Banco de Dados**: Alterado o tipo da coluna `session_id` na tabela `agsc_cart` para `VARCHAR(255) NULL DEFAULT NULL`.
  - [install.sql](file:///var/www/html/agsonhos/backend/resources/schema/install.sql): Atualizado o DDL da tabela `agsc_cart` com `session_id varchar(255)` e índice composto otimizado `KEY cart_id (customer_id, session_id(191), product_id, subscription_plan_id)`.
  - [CartMapper.php](file:///var/www/html/agsonhos/backend/core/Mappers/EntityMappers/CartMapper.php): Inserida proteção defensiva `substr($sessionId, 0, 255)`.

---

## 2. Validação e Testes Automatizados

- **Teste de Carrinho com Session ID de 64 Caracteres**:
  - `QuotationAndTakeoffValidationTest::testCartMapperAcceptsLongSessionId` ➔ **Aprovado com sucesso**.
- **Suíte Completa PHPUnit**:
  ```bash
  ./vendor/bin/phpunit
  OK (81 tests, 275 assertions)
  ```

