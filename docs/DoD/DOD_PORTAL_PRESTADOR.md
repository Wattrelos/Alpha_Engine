---
type: "Quality_Governance"
scope: "Service_Provider_Portal"
trace_adr: ["ADR-009"]
trace_use_cases: ["UC_PRV_001", "UC_PRV_002", "UC_PRV_003"]
version: "1.0"
---

# 📋 Definition of Done (DoD) - Portal do Prestador

## 🛠️ Lógica de Validação por Camada

### C1: HTTP / Controller
- [x] **Rotas multilíngues:** `/prestador/oportunidades`, `/prestador/projetos/{rfq_id}/proposta` e `/takeoff` registradas em `Routes.php`.
- [x] **CSRF:** formulário de perfil do prestador protegido por token.
- [x] **FE01 (Acesso):** prestador não homologado acessando projeto de terceiro -> `HTTP 403`.

### C2: Domínio & Aplicação
- [x] **Haversine:** distância em km entre prestador e obra via `GeoMatchingService`.
- [x] **RN-BID-01:** bloqueio ao atingir 10 propostas na mesma RFQ.
- [x] **Integridade:** proposta só em RFQ `open`; edição da própria proposta permitida.
- [x] **Takeoff:** recálculo automático do total e transição para `boq_ready` ao finalizar.

### C3: Persistência & Integração
- [x] **DI/PSR-11:** `GeoMatchingService`, `BoqSpreadsheetImportService` e `BoqToCartConverterService` registrados em `AppBootstrap`/`AppContainer`.
- [x] **Seed:** `seed_rfq_provider_data.sql` disponível para reidratar o ambiente.

---

## 🧪 Testes & Quality Thresholds

| Ferramenta / Suíte | Escopo | Resultado |
| :--- | :--- | :--- |
| **PHPUnit 11** | Haversine, perfil, limite de 10 propostas, cálculo BoQ (`tests/Validation/QuotationAndTakeoffValidationTest.php`) | **120 / 120 OK** |
| **Behat (BDD)** | `features/use_cases/uc_prv_portal_prestador.feature` via `ProviderContext.php` | **6 / 6 cenários OK (42 passos)** |
| **Playwright E2E** | `e2e/specs/frontend/service-provider.spec.ts` (Feed, Proposta, Takeoff, Proteção de Rotas, Navegação) | **20 / 20 OK (Chromium, Firefox, WebKit)** |

---

## 🚀 PR Review Criteria

1. **Evidências:** logs dos três conjuntos de testes anexados ao PR.
2. **Rastreabilidade:** alterações vinculadas aos UCs em `docs/business/use-cases/service_provider_area/`.
3. **Versionamento:** commit `df6f759e` na branch `main`.
