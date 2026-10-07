---
adr: 9
title: Matching Geográfico por Haversine e Fluxo Takeoff/BoQ → Carrinho no Portal do Prestador
status: Accepted
date: 2026-10-07
authors:
  - Antigravity AI
  - Josias
impacted_components:
  - namespace: Alpha\Services\Quotation\GeoMatchingService
    path: /backend/core/Services/Quotation/GeoMatchingService.php
  - namespace: Alpha\Services\Quotation\BoqToCartConverterService
    path: /backend/core/Services/Quotation/BoqToCartConverterService.php
  - namespace: Containers\AppBootstrap
    path: /backend/Containers/AppBootstrap.php
rules:
  business:
    max_bids_per_rfq: 10
    rfq_status_flow: ["open", "boq_ready"]
validation:
  must_use_di: true
---

# ADR 009: Matching Geográfico por Haversine e Fluxo Takeoff/BoQ → Carrinho

## Status
Aceito

## Contexto
O Portal do Prestador (UC_PRV_001 a UC_PRV_003) precisa listar obras (RFQs) dentro do raio de atendimento do profissional, limitar a concorrência de propostas (`RN-BID-01`) e converter o levantamento de materiais (BoQ) em carrinho de compras para o cliente. Consultar APIs externas de distância a cada listagem adicionaria custo, latência e dependência de terceiros, o que é inadequado para o escopo acadêmico do projeto.

## Decisão
1. **Distância:** usar a fórmula de **Haversine** calculada localmente em `GeoMatchingService`, a partir das coordenadas base do prestador e da obra, filtrando pelo raio (`?radius=`) e por especialidade (`?categoria=`).
2. **Concorrência:** limitar a 10 propostas por RFQ, validando também o status `open` da obra (`RN-BID-01`).
3. **Takeoff → Carrinho:** ao finalizar o levantamento, a RFQ passa a `boq_ready` e o `BoqToCartConverterService` converte o BoQ em itens do carrinho após aprovação do cliente.
4. **Injeção de dependências:** os serviços são registrados em `AppBootstrap` e resolvidos via PSR-11 (`AppContainer`).

## Consequências

### Positivas (Prós)
* Sem dependência externa nem custo por consulta; cálculo determinístico e testável.
* Regras de negócio isoladas em serviços injetáveis, facilitando testes unitários.

### Negativas (Contras)
* Haversine mede distância em linha reta, não a rota rodoviária real.
* Exige coordenadas válidas por CEP no perfil do prestador e na obra.
