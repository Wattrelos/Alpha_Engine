# DP-70: Novos Diagramas de Atividades (Activity Diagrams)

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-08-09 23:10:11
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/70

## Descrição

# Plano de Implementação: Diagramas de Atividades (Activity Diagrams)

Este plano detalha a criação de **6 novos diagramas de atividades em PlantUML (.puml)** no diretório [docs/workflows/activity_diagrams/](file:///var/www/html/agsonhos/docs/workflows/activity_diagrams/), utilizando a sintaxe moderna de diagramas de atividade do PlantUML (`start`, `if/then/else`, `fork/again`, `repeat/while`, `stop`).

## Objetivo

Enriquecer a documentação técnica do repositório modelando os **fluxos de decisão complexos**, **árvores de regras de negócio** e **esteiras de processamento** do e-commerce.

---

## Proposta de Diagramas de Atividades

| # | Arquivo PUML | Foco / Processo de Negócio | Elementos Chave Ilustrados |
|---|---|---|---|
| 1 | `checkout_decision_tree.puml` | Árvore de Decisão do Checkout | Validação do Carrinho, Seleção de Endereço, Cotação de Frete, Aplicação de Cupom, Modos de Pagamento (PIX/Cartão/Boleto) e Tratamento de Falhas |
| 2 | `gestao_estoque_concorrente.puml` | Reserva e Baixa de Estoque Concorrente | Verificação de disponibilidade, Bloqueio Otimista (*Optimistic Lock*), Reserva temporária, Baixa no pagamento e Reversão por expiração |
| 3 | `processamento_devolucao_rma.puml` | Ciclo de Vida da Devolução (RMA) | Solicitação pelo cliente, Análise do Admin, Geração de Etiqueta de Logística Reversa, Inspeção de Qualidade no Estoque e Reembolso/Estorno |
| 4 | `pipeline_seguranca_middleware.puml` | Esteira de Segurança & Middlewares HTTP | Pipeline do Slim 4: Validação Anti-CSRF, Rate Limiting, Checagem de Sessão (Redis/PHP) e Autorização RBAC de acesso às Actions |
| 5 | `fluxo_venda_pos_balcao.puml` | Atendimento Presencial (Ponto de Venda - POS) | Pré-venda/Balcão pelo Vendedor, Escolha de Retirada vs Entrega, Fechamento no Caixa, Emissão Fiscal (NFC-e) e Baixa em Estoque |
| 6 | `sanitizacao_lgpd_anonimizacao.puml` | Governança de Dados / Direito ao Esquecimento | Solicitação de exclusão do titular, Validação de retenção fiscal obrigatória (5 anos), Execução do `LgpdSanitizer` e Log de Conformidade |

---

## User Review Required

> [!IMPORTANT]
> Os diagramas serão estruturados utilizando a sintaxe estendida de atividade do PlantUML (`!theme plain`, `skinparam`, `swimlanes`/raias de responsabilidade por ator/módulo e codificação por cores).

---

## Modificações Propostas

### `docs/workflows/activity_diagrams`

#### [NEW] [checkout_decision_tree.puml](file:///var/www/html/agsonhos/docs/workflows/activity_diagrams/checkout_decision_tree.puml)
Modelagem completa da árvore de decisão do checkout do cliente, contemplando fluxos de sucesso e exceções de pagamento recusado ou falha de estoque.

#### [NEW] [gestao_estoque_concorrente.puml](file:///var/www/html/agsonhos/docs/workflows/activity_diagrams/gestao_estoque_concorrente.puml)
Fluxograma de decisão para validação, bloqueio otimista e decretação de estoque em ambiente de altíssima concorrência.

#### [NEW] [processamento_devolucao_rma.puml](file:///var/www/html/agsonhos/docs/workflows/activity_diagrams/processamento_devolucao_rma.puml)
Diagrama de atividade para o workflow de devoluções (RMA), com raias divididas entre Cliente, Painel Administrativo e Estoque/Logística.

#### [NEW] [pipeline_seguranca_middleware.puml](file:///var/www/html/agsonhos/docs/workflows/activity_diagrams/pipeline_seguranca_middleware.puml)
Diagrama de esteira de execução de Middlewares HTTP do Slim Framework com interrupções de segurança (400 CSRF, 401 Unauthorized, 403 Forbidden).

#### [NEW] [fluxo_venda_pos_balcao.puml](file:///var/www/html/agsonhos/docs/workflows/activity_diagrams/fluxo_venda_pos_balcao.puml)
Fluxograma de atendimento de venda física com divisões entre Vendedor, Operador de Caixa e Sistema de Gestão de Estoque.

#### [NEW] [sanitizacao_lgpd_anonimizacao.puml](file:///var/www/html/agsonhos/docs/workflows/activity_diagrams/sanitizacao_lgpd_anonimizacao.puml)
Fluxo decisório do módulo de governança LGPD, cobrindo o `LgpdSanitizer` e a política de preservação de registros fiscais obrigatórios.

---

## Plano de Verificação

### Validação de Sintaxe PlantUML
- Confirmar presença de marcadores `@startuml` e `@enduml` em todos os 6 arquivos.
- Garantir compilação limpa sem erros de sintaxe nos blocos condicionais (`if`, `elseif`, `else`, `endif`).

# Tarefas - Criação dos Diagramas de Atividades

- [x] `checkout_decision_tree.puml` - Criar diagrama de atividades para Árvore de Decisão do Checkout <!-- id: 0 -->
- [x] `gestao_estoque_concorrente.puml` - Criar diagrama de atividades para Reserva e Baixa de Estoque Concorrente <!-- id: 1 -->
- [x] `processamento_devolucao_rma.puml` - Criar diagrama de atividades para Ciclo de Vida da Devolução (RMA) <!-- id: 2 -->
- [x] `pipeline_seguranca_middleware.puml` - Criar diagrama de atividades para Esteira de Segurança & Middlewares HTTP <!-- id: 3 -->
- [x] `fluxo_venda_pos_balcao.puml` - Criar diagrama de atividades para Atendimento Presencial (POS / Venda Balcão) <!-- id: 4 -->
- [x] `sanitizacao_lgpd_anonimizacao.puml` - Criar diagrama de atividades para Governança de Dados e Sanitização LGPD <!-- id: 5 -->
- [x] Validação e Walkthrough - Revisar arquivos PUML criados e atualizar walkthrough.md <!-- id: 6 -->

# Walkthrough: Diagramas de Sequência e Atividades

Foram criadas duas novas coleções completas de diagramas no formato **PlantUML (.puml)** para ilustrar a arquitetura, segurança, concorrência, resiliência e regras de negócio da **Alpha Engine**.

---

## 1. Diagramas de Sequência em `docs/workflows/sequence_diagrams/`

Diretório: [docs/workflows/sequence_diagrams/](file:///var/www/html/agsonhos/docs/workflows/sequence_diagrams/)

* [autenticacao_redis_fallback.puml](file:///var/www/html/agsonhos/docs/workflows/sequence_diagrams/autenticacao_redis_fallback.puml): Autenticação Dual com Cache Redis e Fallback gracioso para `$_SESSION`.
* [lazy_loading_proxy.puml](file:///var/www/html/agsonhos/docs/workflows/sequence_diagrams/lazy_loading_proxy.puml): Virtual Proxy e hidratação diferida (*Lazy Loading*) no DAO.
* [webhook_pagamento_adapter.puml](file:///var/www/html/agsonhos/docs/workflows/sequence_diagrams/webhook_pagamento_adapter.puml): Processamento assíncrono de webhook com GoF Adapter e Observers.
* [fusao_carrinho_login.puml](file:///var/www/html/agsonhos/docs/workflows/sequence_diagrams/fusao_carrinho_login.puml): Mesclagem do carrinho anônimo para a conta do cliente no login.
* [resolucao_seo_url.puml](file:///var/www/html/agsonhos/docs/workflows/sequence_diagrams/resolucao_seo_url.puml): Resolução de URLs amigáveis (SEO URLs) com cache em arquivo.
* [calculo_frete_strategy.puml](file:///var/www/html/agsonhos/docs/workflows/sequence_diagrams/calculo_frete_strategy.puml): Cotação de frete multi-estratégia (GoF Strategy Pattern).

---

## 2. Diagramas de Atividades em `docs/workflows/activity_diagrams/`

Diretório: [docs/workflows/activity_diagrams/](file:///var/www/html/agsonhos/docs/workflows/activity_diagrams/)

* [checkout_decision_tree.puml](file:///var/www/html/agsonhos/docs/workflows/activity_diagrams/checkout_decision_tree.puml): Árvore de decisão completa do checkout (PIX, Cartão, Boleto, Frete, Cupons e exceções).
* [gestao_estoque_concorrente.puml](file:///var/www/html/agsonhos/docs/workflows/activity_diagrams/gestao_estoque_concorrente.puml): Reserva e baixa de estoque sob alta concorrência com bloqueio otimista (*Optimistic Lock*).
* [processamento_devolucao_rma.puml](file:///var/www/html/agsonhos/docs/workflows/activity_diagrams/processamento_devolucao_rma.puml): Fluxo de solicitação, triagem, inspeção física e estorno/crédito de devolução (RMA).
* [pipeline_seguranca_middleware.puml](file:///var/www/html/agsonhos/docs/workflows/activity_diagrams/pipeline_seguranca_middleware.puml): Esteira de execução de Middlewares HTTP do Slim 4 (CSRF, Rate Limiting, RBAC e Sessão).
* [fluxo_venda_pos_balcao.puml](file:///var/www/html/agsonhos/docs/workflows/activity_diagrams/fluxo_venda_pos_balcao.puml): Workflow de atendimento presencial no balcão da loja física (Vendedor, Caixa, Estoque e NFC-e).
* [sanitizacao_lgpd_anonimizacao.puml](file:///var/www/html/agsonhos/docs/workflows/activity_diagrams/sanitizacao_lgpd_anonimizacao.puml): Governança de dados, direito ao esquecimento e sanitização via `LgpdSanitizer` sob o Art. 18 da LGPD.

---

## Validação e Conformidade

Todos os 12 novos arquivos `.puml` foram criados com sintaxe PlantUML válida, separação de responsabilidades por raias (*swimlanes*) ou participantes, estilos limpos (`!theme plain`, `skinparam`) e alinhamento completo com os contratos e mappers do projeto.

