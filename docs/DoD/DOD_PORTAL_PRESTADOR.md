---
type: "Quality_Governance"
scope: "Service_Provider_Portal"
trace_adr: ["ADR-009"]
trace_use_cases: ["UC_PRV_001", "UC_PRV_002", "UC_PRV_003", "RF033", "RF036", "RF037"]
version: "1.1"
---

# 📋 Definition of Done (DoD) - Portal do Prestador & Módulo de Cotações

## 🛠️ Lógica de Validação por Camada

### C1: HTTP / Controller
- [x] **Rotas multilíngues:** `/prestador/oportunidades`, `/prestador/projetos/{rfq_id}/proposta`, `/takeoff` e `/api/projetos/adicionar-item` registradas em `Routes.php`.
- [x] **CSRF Integrado:** formulários de perfil, publicação de RFQ (`/projetos/novo`) e manipulações de BoQ protegidos por token via `CsrfGuardMiddleware` e helpers `appendCsrf` / `getCsrfHeaders` no frontend.
- [x] **FE01 (Acesso):** prestador não homologado acessando projeto de terceiro -> `HTTP 403`.
- [x] **API Resiliente:** endpoint de inclusão de insumos no orçamento da obra respondendo com JSON e código HTTP 200 para usuários autenticados, ou flag `require_login` para anônimos.

### C2: Domínio & Aplicação
- [x] **Haversine:** distância em km entre prestador e obra via `GeoMatchingService`.
- [x] **RN-BID-01:** bloqueio ao atingir 10 propostas na mesma RFQ.
- [x] **Integridade:** proposta só em RFQ `open`; edição da própria proposta permitida.
- [x] **Takeoff & BoQ:** recálculo automático do total e transição para `boq_ready` ao finalizar.
- [x] **Catálogo → Orçamento:** produto selecionado na vitrine/PDP vinculado ao BoQ do projeto com nome, preço unitário e quantidade especificada.

### C3: Persistência & Integração
- [x] **DI/PSR-11:** `GeoMatchingService`, `BoqSpreadsheetImportService` e `BoqToCartConverterService` registrados em `AppBootstrap`/`AppContainer`.
- [x] **Entidades e Repositórios:** `ProductDescription` mapeado e `ProductRepository` com namespaces completos (`QueryBuilder` e `DataAccessObject`).
- [x] **Integridade MySQL:** datas (`date_added`, `date_modified`) com preenchimento padrão em `ProjectRfq`, `ProjectBoq`, `ProjectBoqItem`, `ProjectBid` e `ServiceProviderProfile`.
- [x] **Seed:** `seed_rfq_provider_data.sql` disponível para reidratar o ambiente.

---

## 🧪 Testes & Quality Thresholds

| Ferramenta / Suíte | Escopo | Resultado |
| :--- | :--- | :--- |
| **PHPUnit 11** | Haversine, perfil, limite de 10 propostas, cálculo BoQ (`tests/Validation/QuotationAndTakeoffValidationTest.php`) | **120 / 120 OK (100%)** |
| **Behat (BDD)** | Cotações e Prestadores (`features/use_cases/uc_prv_portal_prestador.feature` e `features/services/cotacao_e_prestadores_rfq.feature`) | **11 / 11 cenários OK (74 passos)** |
| **Behat (BDD Geral)** | Suíte completa da plataforma Alpha Engine (`composer test:behat`) | **117 / 117 cenários OK (772 passos)** |
| **Playwright E2E** | `e2e/specs/frontend/service-provider.spec.ts` (Feed, Proposta, Takeoff, RFQ sem CSRF, Add to Quote PDP) | **6 / 6 OK (100%)** |

---

## 🚀 PR Review Criteria

1. **Evidências:** logs dos três conjuntos de testes anexados ao PR.
2. **Rastreabilidade:** alterações vinculadas aos UCs em `docs/business/use-cases/service_provider_area/` e `docs/architecture/deployment/deploy-plan_106.md`.
3. **Versionamento:** commit na branch `main`.
