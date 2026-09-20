# 🌟 Casos de Uso Core do Sistema (Alpha Engine)

> **Documentação de Engenharia de Software - Laboratório de Engenharia de Software**  
> **Projeto:** Alpha Engine (Plataforma E-commerce & Ponto de Venda On-Premise para Materiais de Construção)  
> **Referência:** Módulo 04 da Apresentação Institucional FATEC Ferraz de Vasconcelos

---

## 🎯 1. Visão Geral dos Casos de Uso Core

Os **10 Casos de Uso Core** constituem a espinha dorsal funcional do ecossistema **Alpha Engine**. Eles sintetizam as interações mais críticas de negócio entre os 7 atores da plataforma (Visitante, Cliente, Vendedor Balcão, Operador de Caixa, Operador SAC, Administrador e Serviços Externos/SEFAZ).

Enquanto os módulos operacionais específicos (`customer/`, `pos/` e `dashboard/`) detalham granularmente as 53 interações do sistema, os Casos de Uso Core conectam de ponta a ponta os fluxos de valor de materiais de construção: desde a descoberta e cálculo técnico na vitrine até a baixa atômica fiscal e pós-venda.

---

## 📋 2. Matriz de Casos de Uso Core

| ID | Nome do Caso de Uso | Atores Primários | Requisitos (RF) | Regras de Negócio (RN) | Documento |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `UC_CORE_001` | Navegar no Catálogo & PDP | Visitante, Cliente | RF001, RF002, RF011, RF012 | RN001, RN002, RN003 | [Visualizar](UC_CORE_001_navegar_catalogo_pdp.md) |
| `UC_CORE_002` | Gestão CRUD de SKUs | Administrador | RF001, RF003, RF005 | RN001, RN002, RN003, RN006 | [Visualizar](UC_CORE_002_gestao_crud_skus.md) |
| `UC_CORE_003` | Autenticação & Cadastro | Visitante, Cliente | RF014, RF015 | RN017 | [Visualizar](UC_CORE_003_autenticacao_cadastro.md) |
| `UC_CORE_004` | Gerenciar Carrinho de Compras | Visitante, Cliente | RF004, RF009 | RN001, RN005 | [Visualizar](UC_CORE_004_gerenciar_carrinho_compras.md) |
| `UC_CORE_005` | Checkout E-Commerce | Cliente, Gateways | RF010, RF017, RF018, RF019, RF020 | RN002, RN007, RN008, RN016 | [Visualizar](UC_CORE_005_checkout_ecommerce.md) |
| `UC_CORE_006` | Pré-Venda no PDV Balcão | Vendedor POS, Cliente | RF004, RF006, RF021 | RN001, RN005, RN008, RN015 | [Visualizar](UC_CORE_006_pre_venda_pdv_balcao.md) |
| `UC_CORE_007` | Fechamento de Venda no Caixa | Operador Caixa, SEFAZ | RF006, RF018, RF020 | RN005, RN012, RN016 | [Visualizar](UC_CORE_007_fechamento_venda_caixa.md) |
| `UC_CORE_008` | Solicitar Devolução (RMA) | Cliente, SAC | RF016, RF022 | RN009, RN010, RN011, RN012 | [Visualizar](UC_CORE_008_solicitar_devolucao_rma.md) |
| `UC_CORE_009` | Gestão de Devoluções no Painel | Operador do Painel | RF020, RF022, RF025 | RN005, RN009, RN010, RN011, RN012 | [Visualizar](UC_CORE_009_gestao_devolucoes_painel.md) |
| `UC_CORE_010` | Monitoramento de Ruptura | Administrador | RF006, RF024, RF025 | RN005, RN006, RN015 | [Visualizar](UC_CORE_010_monitoramento_ruptura.md) |

---

## 🏛️ 3. Rastreabilidade com os Atores do Sistema

```
                    ┌─────────────────────────┐
                    │     Visitante / Guest   │───┐
                    └─────────────────────────┘   │
                                                  ▼
                    ┌─────────────────────────┐ ┌───────────────┐
                    │     Cliente (PF/PJ)     │─▶│  UC_CORE_001  │ (Catálogo & PDP)
                    └─────────────────────────┘ │  UC_CORE_003  │ (Autenticação/Cadastro)
                                                │  UC_CORE_004  │ (Carrinho & M²)
                                                │  UC_CORE_005  │ (Checkout E-Commerce)
                                                │  UC_CORE_008  │ (Solicitar RMA)
                                                └───────────────┘
                                                  ▲
                    ┌─────────────────────────┐   │
                    │   Vendedor de Balcão    │───┴─▶ UC_CORE_006 (Pré-Venda Balcão)
                    └─────────────────────────┘
                                                  ▲
                    ┌─────────────────────────┐   │
                    │    Operador de Caixa    │───┴─▶ UC_CORE_007 (Fechamento Caixa & NFC-e)
                    └─────────────────────────┘
                                                  ▲
                    ┌─────────────────────────┐   │
                    │   Operador SAC / CD     │───┴─▶ UC_CORE_009 (Gestão Devoluções / Triagem)
                    └─────────────────────────┘
                                                  ▲
                    ┌─────────────────────────┐   │
                    │   Administrador Geral   │───┴─▶ UC_CORE_002 (CRUD SKUs & Ficha Técnica)
                    └─────────────────────────┘       UC_CORE_010 (Monitoramento Ruptura & ABC)
```
