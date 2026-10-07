# 👷 Módulo 4: Portal do Prestador de Serviços (Alpha Engine)

> **Documentação de Engenharia de Software**  
> **Projeto:** Alpha Engine (Plataforma E-commerce & Hub de Serviços para Construção Civil)  
> **Domínio:** Cotação de Obras, Matching Geoespacial & Levantamento de Materiais (RFQ / BoQ)  

---

## 🎯 1. Visão Geral do Módulo

O **Portal do Prestador de Serviços** conecta profissionais técnicos da construção civil (pedreiros, eletricistas, encanadores, gesseiros, pintores, engenheiros e empreiteiros) às oportunidades de obras publicadas pelos clientes na plataforma.

O fluxo de valor se divide em 3 etapas integradas:
1. **Matching Geográfico por Proximidade:** Notificação e exibição de projetos que estão dentro do raio de cobertura em km do prestador (Fórmula de Haversine);
2. **Concorrência e Proposta Comercial:** Submissão do orçamento de mão de obra com prazo e memorial descritivo para comparação pelo cliente;
3. **Levantamento Técnico de Insumos (Material Takeoff):** Elaboração da lista quantitativa de materiais de construção (*Bill of Quantities - BoQ*), gerando compras diretas com descontos no e-commerce da loja.

---

## 📋 2. Catálogo de Casos de Uso do Prestador (`service_provider_area/`)

| ID | Nome do Caso de Uso | Atores | Rastreabilidade | Rota / Action | Especificação |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `UC_PRV_001` | Consultar Oportunidades no Raio de Atendimento | Prestador, Sistema Geo | RF033, RF034, RNF002 | `GET /prestador/oportunidades`<br>`ListOpportunitiesAction` | [Visualizar](UC_PRV_001_consultar_oportunidades_raio.md) |
| `UC_PRV_002` | Submeter Proposta Comercial de Mão de Obra | Prestador, Cliente | RF035, RNF003 | `POST /prestador/projetos/{id}/proposta`<br>`SubmitBidAction` | [Visualizar](UC_PRV_002_submeter_proposta_mao_de_obra.md) |
| `UC_PRV_003` | Realizar Levantamento Técnico de Materiais (Takeoff & BoQ) | Prestador, Catálogo | RF036, RF037, RN001, RN015 | `POST /prestador/projetos/{id}/takeoff`<br>`MaterialTakeoffAction` | [Visualizar](UC_PRV_003_levantamento_materiais_takeoff_boq.md) |

---

## 🔄 3. Rastreabilidade Cruzada com Casos de Uso do Cliente (`customer/`)

```
[Cliente: UC_CLI_025] Cria RFQ do Projeto 
        │
        ▼ (Matching Geoespacial Haversine - RF034)
[Prestador: UC_PRV_001] Visualiza Oportunidade no Raio X km
        │
        ▼ (Elaboração do Orçamento de Mão de Obra)
[Prestador: UC_PRV_002] Submete Proposta Comercial (Bid)
        │
        ▼ (Análise Comparativa - RF035)
[Cliente: UC_CLI_027] Compara Propostas lado a lado
        │
        ▼ (Homologação & Contratação)
[Cliente: UC_CLI_028] Aceita Proposta do Prestador
        │
        ▼ (Liberação de Ferramenta Técnica)
[Prestador: UC_PRV_003] Executa Material Takeoff e monta BoQ
        │
        ▼ (Revisão da Lista de Insumos - RF036 / RF037)
[Cliente: UC_CLI_029] Aprova BoQ e Envia ao Carrinho de Compras
        │
        ▼ (Fechamento Comercial)
[Cliente: UC_CLI_009] Conclui Checkout no E-commerce
```

---

## 🗄️ 4. Modelagem de Dados Envolvida
- `agsc_service_provider_profile`: Perfil profissional, latitude/longitude, raio em km (`service_radius_km`).
- `agsc_project_rfq`: Solicitações de projetos e obras do cliente.
- `agsc_project_bid`: Propostas comerciais de prestação de serviço.
- `agsc_project_boq`: Cabeçalho da lista discriminada de quantitativos de materiais.
- `agsc_project_boq_item`: Itens quantitativos vinculados ao catálogo de produtos.
