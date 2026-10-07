# Planejamento: Implementação do Portal do Prestador de Serviços (UC_PRV_001 a UC_PRV_003)

## Objetivo
Implementar e validar o **Portal do Prestador** do ecossistema Alpha Engine, cobrindo matching geográfico de obras, submissão de propostas de mão de obra e levantamento técnico de materiais (Takeoff/BoQ), com consistência com:
- Os casos de uso em [service_provider_area](/docs/business/use-cases/service_provider_area/README.md);
- A decisão arquitetural [ADR 0009](/docs/architecture/adr/0009-geomatching-haversine-prestador.md);
- O critério de aceite em [DOD_PORTAL_PRESTADOR.md](/docs/DoD/DOD_PORTAL_PRESTADOR.md).

## Lista de Entregas
1. `UC_PRV_001`: Consultar Oportunidades no Raio de Atendimento (`/pt-br/prestador/oportunidades`).
2. `UC_PRV_002`: Submeter Proposta Comercial de Mão de Obra (`/pt-br/prestador/projetos/{rfq_id}/proposta`).
3. `UC_PRV_003`: Levantamento Técnico de Materiais / Takeoff & BoQ Tool (`/pt-br/prestador/projetos/{rfq_id}/takeoff`).
4. Bindings de DI, rotas multilíngues e links de navegação.
5. Suítes PHPUnit, Behat e Playwright.

## Verificação
- Executar PHPUnit, Behat e Playwright (resultados no DoD).
- Conferir rastreabilidade com os UCs e a ADR 0009.
- Confirmar commit na branch `main`.

# Walkthrough: Portal do Prestador de Serviços

## 🎯 Resumo da Entrega
Os três casos de uso do prestador foram implementados e validados. Detalhes de regra de negócio estão nas especificações dos UCs; a justificativa técnica, na ADR 0009; e as evidências de teste, no DoD.

---

## 🛠️ Casos de Uso Implementados

| ID | Caso de Uso | Rota | Destaques |
| :--- | :--- | :--- | :--- |
| `UC_PRV_001` | Consultar Oportunidades no Raio | `/prestador/oportunidades` | Haversine ([GeoMatchingService.php](/backend/core/Services/Quotation/GeoMatchingService.php)), filtros `?categoria=` e `?radius=`, perfil com CSRF |
| `UC_PRV_002` | Submeter Proposta de Mão de Obra | `/prestador/projetos/{rfq_id}/proposta` | `RN-BID-01` (teto de 10 propostas), RFQ `open`, edição da própria proposta |
| `UC_PRV_003` | Takeoff & BoQ Tool | `/prestador/projetos/{rfq_id}/takeoff` | `FE01` (HTTP 403), [SearchCatalogItemsAction.php](/backend/core/Controller/Actions/Quotation/Provider/SearchCatalogItemsAction.php), status `boq_ready`, [BoQ to Cart](/backend/core/Services/Quotation/BoqToCartConverterService.php) |

---

## 🧱 Arquitetura e Integrações

1. **DI & PSR-11:** bindings em [AppBootstrap.php](/backend/Containers/AppBootstrap.php) e auto-instanciação em [AppContainer.php](/backend/Containers/AppContainer.php) para `GeoMatchingService`, `BoqSpreadsheetImportService` e `BoqToCartConverterService`.
2. **Rotas:** registradas em [Routes.php](/backend/Config/Routes.php) (oportunidades, propostas e `/takeoff`).
3. **UI:** links para *Portal do Prestador* e *Meus Projetos (RFQ)* em [top-nav.twig](/backend/resources/views/components/molecules/top-nav.twig), [footer.twig](/backend/resources/views/components/organisms/footer.twig) e [account.twig](/backend/resources/views/pages/users/accounts/account.twig).

---

## 🧪 Resultados dos Testes

| Suíte | Resultado |
| :--- | :--- |
| PHPUnit 11 | 120 / 120 OK |
| Behat (BDD) | 6 / 6 cenários OK (42 passos) |
| Playwright E2E | 20 / 20 OK (Chromium, Firefox, WebKit) |

Escopo detalhado em [DOD_PORTAL_PRESTADOR.md](/docs/DoD/DOD_PORTAL_PRESTADOR.md).

---

## 📁 Artefatos Relacionados
- 📄 [ADR 0009 — Haversine e Takeoff → Carrinho](/docs/architecture/adr/0009-geomatching-haversine-prestador.md)
- 📄 [DoD — Portal do Prestador](/docs/DoD/DOD_PORTAL_PRESTADOR.md)
- 📄 [Casos de Uso do Prestador](/docs/business/use-cases/service_provider_area/README.md)
- 📄 [seed_rfq_provider_data.sql](/backend/resources/schema/seed_rfq_provider_data.sql) — carga para reidratação do ambiente.

## 🔍 Validação e Versionamento
- Alterações commitadas na `main`: `df6f759e` — *feat(provider): finalize service provider portal with matching, bids, takeoff tool and automated tests*.