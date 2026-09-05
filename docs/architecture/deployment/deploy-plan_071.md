# DP-71: Diagramas de Componentes de Arquitetura

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-08-10 01:05:15
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/71

## Descrição

# Plano de Implementação: Diagramas de Componentes de Arquitetura

Este plano detalha a criação de **diagramas de componentes** no formato **PlantUML (.puml)** utilizando a notação UML 2.0 e o modelo **C4 (Component Level)** no diretório [docs/diagrams/](file:///var/www/html/agsonhos/docs/diagrams/) e [docs/architecture/components/](file:///var/www/html/agsonhos/docs/architecture/components/).

## Objetivo

Fornecer uma visão clara dos subsistemas desacoplados da **Alpha Engine**, mostrando as interfaces fornecidas e requeridas entre os componentes de software, microsserviços/infraestrutura (MySQL, Redis, RabbitMQ) e camadas de aplicação.

---

## Proposta de Diagramas de Componentes

| # | Arquivo PUML | Escopo & Notação | Principais Módulos/Componentes Ilustrados |
|---|---|---|---|
| 1 | `c4_component_architecture.puml` | Visão Geral C4 (Nível 3 - Componentes) | Web Routing (Slim 4), Subssistema de Autenticação (Redis), Módulo de Checkout/Pedidos (UoW), Módulo de Pagamentos (Factory/Adapter), Módulo de Frete (Strategy), Fila Assíncrona (RabbitMQ/Worker) |
| 2 | `persistence_component_diagram.puml` | Camada de Persistência & ORM | `UnitOfWork`, `IdentityMap`, `RepositoryFactory`, `MapperFactory`, `DataAccessObject`, `ProxyFactory`, `QueryBuilder`, Cache de Arquivos e Conexão PDO |
| 3 | `dual_architecture_components.puml` | Separação Admin vs Front-End | Contraste entre o **Painel Administrativo** (`Alpha\Admin\...` via `BaseController`) e o **Front-End E-Commerce** (`Alpha\Controller\...` via `ActionInterface` e DI pura) |

---

## User Review Required

> [!IMPORTANT]
> Os novos diagramas complementarão a especificação arquitetural existente em [docs/architecture/README.md](file:///var/www/html/agsonhos/docs/architecture/README.md) e [docs/diagrams/componentDiagram.puml](file:///var/www/html/agsonhos/docs/diagrams/componentDiagram.puml), trazendo maior detalhamento para os subsistemas de mensageria (RabbitMQ), cache (Redis) e desacoplamento DDD.

---

## Modificações Propostas

### `docs/diagrams` & `docs/architecture/components`

#### [NEW] [c4_component_architecture.puml](file:///var/www/html/agsonhos/docs/architecture/components/c4_component_architecture.puml)
Diagrama de componentes C4 detalhando a comunicação entre containers de serviços, APIs externas (Gateways), Barramento de Mensagens (RabbitMQ) e Bancos de Dados.

#### [NEW] [persistence_component_diagram.puml](file:///var/www/html/agsonhos/docs/diagrams/persistence_component_diagram.puml)
Diagrama UML do subssistema de persistência e mapeamento objeto-relacional (ORM customizado com Identity Map, Proxy Pattern e UoW).

#### [NEW] [dual_architecture_components.puml](file:///var/www/html/agsonhos/docs/diagrams/dual_architecture_components.puml)
Diagrama demonstrando o isolamento de namespaces, bibliotecas e injeção de dependências entre o escopo Admin e o escopo Front-end.

---

## Plano de Verificação

### Validação de Compilação
- Compilar todos os arquivos `.puml` gerados utilizando a CLI nativa do `plantuml` para garantir 0 erros de sintaxe.

# Tarefas - Criar Diagramas de Componentes

- [x] `c4_component_architecture.puml` - Criar diagrama de componentes C4 da arquitetura geral <!-- id: 0 -->
- [x] `persistence_component_diagram.puml` - Criar diagrama de componentes da camada de persistência & ORM <!-- id: 1 -->
- [x] `dual_architecture_components.puml` - Criar diagrama de componentes do isolamento Admin vs Frontend <!-- id: 2 -->
- [x] Compilação & Walkthrough - Validar arquivos com plantuml CLI e atualizar walkthrough.md <!-- id: 3 -->

# Walkthrough: Coleção Completa de Diagramas de Arquitetura

Foram criadas três coleções completas de diagramas no formato **PlantUML (.puml)** para ilustrar todos os níveis da arquitetura, segurança, concorrência, persistência, resiliência e regras de negócio da **Alpha Engine**.

---

## 1. Diagramas de Componentes (Component Architecture)

* [c4_component_architecture.puml](file:///var/www/html/agsonhos/docs/architecture/components/c4_component_architecture.puml): Visão C4 (Nível 3) de todos os subsistemas da Alpha Engine (Web Routing Slim 4, Auth Redis, Catálogo/SEO, Checkout/UoW, Pagamentos Adapter, Frete Strategy, RabbitMQ e Worker CLI).
* [persistence_component_diagram.puml](file:///var/www/html/agsonhos/docs/diagrams/persistence_component_diagram.puml): Visão detalhada da camada de persistência e ORM customizado (`UnitOfWork`, `IdentityMap`, `ProxyFactory`, `DataAccessObject`, `QueryBuilder` e Drivers).
* [dual_architecture_components.puml](file:///var/www/html/agsonhos/docs/diagrams/dual_architecture_components.puml): Mapeamento e contraste do isolamento entre o **Painel Administrativo** (`Alpha\Admin\...` via `BaseController`) e o **Front-End E-Commerce** (`Alpha\Controller\...` via `ActionInterface` e DI pura).

---

## 2. Diagramas de Sequência em `docs/workflows/sequence_diagrams/`

Diretório: [docs/workflows/sequence_diagrams/](file:///var/www/html/agsonhos/docs/workflows/sequence_diagrams/)

* [autenticacao_redis_fallback.puml](file:///var/www/html/agsonhos/docs/workflows/sequence_diagrams/autenticacao_redis_fallback.puml): Autenticação Dual com Cache Redis e Fallback gracioso para `$_SESSION`.
* [lazy_loading_proxy.puml](file:///var/www/html/agsonhos/docs/workflows/sequence_diagrams/lazy_loading_proxy.puml): Virtual Proxy e hidratação diferida (*Lazy Loading*) no DAO.
* [webhook_pagamento_adapter.puml](file:///var/www/html/agsonhos/docs/workflows/sequence_diagrams/webhook_pagamento_adapter.puml): Processamento assíncrono de webhook com GoF Adapter e Observers.
* [fusao_carrinho_login.puml](file:///var/www/html/agsonhos/docs/workflows/sequence_diagrams/fusao_carrinho_login.puml): Mesclagem do carrinho anônimo para a conta do cliente no login.
* [resolucao_seo_url.puml](file:///var/www/html/agsonhos/docs/workflows/sequence_diagrams/resolucao_seo_url.puml): Resolução de URLs amigáveis (SEO URLs) com cache em arquivo.
* [calculo_frete_strategy.puml](file:///var/www/html/agsonhos/docs/workflows/sequence_diagrams/calculo_frete_strategy.puml): Cotação de frete multi-estratégia (GoF Strategy Pattern).

---

## 3. Diagramas de Atividades em `docs/workflows/activity_diagrams/`

Diretório: [docs/workflows/activity_diagrams/](file:///var/www/html/agsonhos/docs/workflows/activity_diagrams/)

* [checkout_decision_tree.puml](file:///var/www/html/agsonhos/docs/workflows/activity_diagrams/checkout_decision_tree.puml): Árvore de decisão completa do checkout (PIX, Cartão, Boleto, Frete, Cupons e exceções).
* [gestao_estoque_concorrente.puml](file:///var/www/html/agsonhos/docs/workflows/activity_diagrams/gestao_estoque_concorrente.puml): Reserva e baixa de estoque sob alta concorrência com bloqueio otimista (*Optimistic Lock*).
* [processamento_devolucao_rma.puml](file:///var/www/html/agsonhos/docs/workflows/activity_diagrams/processamento_devolucao_rma.puml): Fluxo de solicitação, triagem, inspeção física e estorno/crédito de devolução (RMA).
* [pipeline_seguranca_middleware.puml](file:///var/www/html/agsonhos/docs/workflows/activity_diagrams/pipeline_seguranca_middleware.puml): Esteira de execução de Middlewares HTTP do Slim 4 (CSRF, Rate Limiting, RBAC e Sessão).
* [fluxo_venda_pos_balcao.puml](file:///var/www/html/agsonhos/docs/workflows/activity_diagrams/fluxo_venda_pos_balcao.puml): Workflow de atendimento presencial no balcão da loja física (Vendedor, Caixa, Estoque e NFC-e).
* [sanitizacao_lgpd_anonimizacao.puml](file:///var/www/html/agsonhos/docs/workflows/activity_diagrams/sanitizacao_lgpd_anonimizacao.puml): Governança de dados, direito ao esquecimento e sanitização via `LgpdSanitizer` sob o Art. 18 da LGPD.

---

## Validação e Conformidade

Todos os 15 novos arquivos `.puml` foram validados e compilados via CLI do `plantuml` com 0 erros.

