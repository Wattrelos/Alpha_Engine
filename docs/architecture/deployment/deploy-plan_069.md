# DP-69: Novos Diagramas de Sequência

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-08-09 22:50:46
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/69

## Descrição

# Plano de Implementação: Novos Diagramas de Sequência

Este plano detalha a criação de 6 novos diagramas de sequência no formato **PlantUML (.puml)** no diretório [docs/workflows/sequence_diagrams/](/docs/workflows/sequence_diagrams/), alinhados com a arquitetura real da **Alpha Engine** e cobrindo aspectos de segurança, resiliência, persistência e padrões de projeto GoF.

## Visão Geral dos Diagramas Propostos

| # | Arquivo PUML | Categoria / Tema | Principais Componentes Ilustrados |
|---|---|---|---|
| 1 | `autenticacao_redis_fallback.puml` | Segurança & Sessão | `AbstractAuthService`, `CustomerAuthService`, `Predis\Client`, `PHP $_SESSION` |
| 2 | `lazy_loading_proxy.puml` | Persistência & GoF Proxy | `DataAccessObject`, `ProxyFactory`, `_alphaTriggerLoad()`, MySQL |
| 3 | `webhook_pagamento_adapter.puml` | Pagamentos & GoF Adapter/Factory | Gateway Webhook, `PaymentGatewayFactory`, `MercadoPagoAdapter`, `OrderObserverInterface` |
| 4 | `fusao_carrinho_login.puml` | Domínio & UX Carrinho | `CustomerAuthService`, `CartRepository`, `CartMapper`, `mergeCartOnLogin()` |
| 5 | `resolucao_seo_url.puml` | Roteamento & Cache | `LegacyRouteRedirectMiddleware`, `SeoUrlRepository`, `FilesystemCacheStrategy`, Slim Action |
| 6 | `calculo_frete_strategy.puml` | GoF Strategy (Frete) | `QuoteShippingAction`, `FlatRateShippingService`, `WeightBasedShippingService`, `FreeShippingService` |

---

## User Review Required

> [!IMPORTANT]
> Os novos diagramas utilizarão a mesma estrutura visual e paleta de cores dos diagramas já existentes em [docs/workflows/sequence_diagrams/](/docs/workflows/sequence_diagrams/) (`autonumber`, `box`, cabeçalhos informativos, notas de resiliência e tratamento de exceções).

---

## Modificações Propostas

### `docs/workflows/sequence_diagrams`

#### [NEW] [autenticacao_redis_fallback.puml](/docs/workflows/sequence_diagrams/autenticacao_redis_fallback.puml)
Ilustra o fluxo de login e validação de sessão usando `AbstractAuthService`. Detalha o caminho feliz com gravação/leitura no **Redis (Predis)** (`sessao:{type}:{sessionId}`) e o fluxo alternativo (*fallback*) com degradação graciosa para `$_SESSION` do PHP nativo caso haja erro de conexão no Redis.

#### [NEW] [lazy_loading_proxy.puml](/docs/workflows/sequence_diagrams/lazy_loading_proxy.puml)
Ilustra a hidratação diferida (*lazy loading*) de entidades pelo `DataAccessObject` via `ProxyFactory`. Exibe a instanciação do objeto proxy "fantasma" sem consulta SQL imediata e a interceptação transparente do `_alphaTriggerLoad()` disparando a consulta SQL no banco apenas quando um método da entidade é invocado.

#### [NEW] [webhook_pagamento_adapter.puml](/docs/workflows/sequence_diagrams/webhook_pagamento_adapter.puml)
Ilustra o processamento assíncrono de notificações de pagamento (Webhook). Mostra a chamada à fábrica `PaymentGatewayFactory`, a conversão da carga pelo `MercadoPagoAdapter` / `PixGateway`, a transição de estado da ordem no repositório e o disparo dos `OrderObserver`s (como `OrderEmailObserver` e baixa de estoque).

#### [NEW] [fusao_carrinho_login.puml](/docs/workflows/sequence_diagrams/fusao_carrinho_login.puml)
Ilustra a migração dos produtos de um carrinho anônimo (armazenado em sessão) para a conta do cliente recém-autenticado. Exibe a execução do `CartRepository` invocando o `CartMapper::mergeCartOnLogin()`, a consolidação de itens duplicados e a atualização no MySQL.

#### [NEW] [resolucao_seo_url.puml](/docs/workflows/sequence_diagrams/resolucao_seo_url.puml)
Ilustra a resolução de URLs amigáveis (ex: `/smartphones/iphone-15-pro`). Mostra o despacho da requisição via `LegacyRouteRedirectMiddleware`, a busca da rota real no `SeoUrlRepository` utilizando a camada de cache em disco (`FilesystemCacheStrategy`), e o direcionamento final para a `ShowProductAction` ou `ShowCategoryAction`.

#### [NEW] [calculo_frete_strategy.puml](/docs/workflows/sequence_diagrams/calculo_frete_strategy.puml)
Ilustra o cálculo de cotação de frete no carrinho. Demonstra o uso do padrão **Strategy** iterando sobre os serviços independentes (`FlatRateShippingService`, `WeightBasedShippingService` e `FreeShippingService`), agregando os resultados e retornando as opções formatadas para a interface.

---

## Plano de Verificação

### Testes Manuais & Sintaxe PlantUML
- Verificar a integridade sintática dos 6 novos arquivos `.puml` garantindo a presença dos blocos `@startuml` e `@enduml`.
- Garantir alinhamento com a arquitetura definida no arquivo [SKILL.md do architecture-validator](/.agents/skills/architecture-validator/SKILL.md) e com as convenções dos diagramas existentes.

# Tarefas - Criação dos Diagramas de Sequência

- [x] `autenticacao_redis_fallback.puml` - Criar diagrama de sequência de Autenticação com Fallback Redis/$_SESSION <!-- id: 0 -->
- [x] `lazy_loading_proxy.puml` - Criar diagrama de sequência de Lazy Loading via ProxyFactory no DataAccessObject <!-- id: 1 -->
- [x] `webhook_pagamento_adapter.puml` - Criar diagrama de sequência de Notificação de Webhook de Pagamento com GoF Adapter <!-- id: 2 -->
- [x] `fusao_carrinho_login.puml` - Criar diagrama de sequência de Fusão de Carrinho no Login do Cliente <!-- id: 3 -->
- [x] `resolucao_seo_url.puml` - Criar diagrama de sequência de Resolução Dinâmica de SEO URLs e Cache <!-- id: 4 -->
- [x] `calculo_frete_strategy.puml` - Criar diagrama de sequência de Cálculo de Frete com GoF Strategy <!-- id: 5 -->
- [x] Validação e Walkthrough - Revisar arquivos PUML criados e gerar resumo das entregas <!-- id: 6 -->

# Walkthrough: Novos Diagramas de Sequência

Foram criados **6 novos diagramas de sequência em PlantUML (.puml)** dentro do diretório [docs/workflows/sequence_diagrams/](/docs/workflows/sequence_diagrams/).

Estes diagramas ilustram os principais aspectos de arquitetura, padrões GoF, segurança, resiliência e regras de negócio da **Alpha Engine**.

---

## Diagramas Criados

### 1. [autenticacao_redis_fallback.puml](/docs/workflows/sequence_diagrams/autenticacao_redis_fallback.puml)
* **Tema:** Autenticação Dual com Cache Redis e Fallback Gracioso.
* **Conteúdo:** Detalha a tentativa primária de persistência/validação de sessão no **Redis via Predis** (`sessao:cliente:{sessionId}`) e a alternância transparente para `$_SESSION` do PHP nativo quando a conexão com o Redis falha ou atinge timeout.

### 2. [lazy_loading_proxy.puml](/docs/workflows/sequence_diagrams/lazy_loading_proxy.puml)
* **Tema:** Virtual Proxy & Hidratação Diferida no DAO.
* **Conteúdo:** Ilustra a criação de instâncias "fantasma" pelo `ProxyFactory` para relacionamentos `@ManyToOne` sem consulta SQL imediata, demonstrando a intercepção transparente do `_alphaTriggerLoad()` apenas quando um método da entidade é invocado.

### 3. [webhook_pagamento_adapter.puml](/docs/workflows/sequence_diagrams/webhook_pagamento_adapter.puml)
* **Tema:** Processamento Assíncrono de Webhook de Pagamento com GoF Adapter & Observers.
* **Conteúdo:** Exibe o recebimento de notificações assíncronas do Gateway de Pagamento, a resolução do `MercadoPagoAdapter` / `PixGateway` via `PaymentGatewayFactory`, a transição de estado da ordem e a notificação da cadeia de `OrderObserverInterface` (`OrderEmailObserver`, estoque, etc.).

### 4. [fusao_carrinho_login.puml](/docs/workflows/sequence_diagrams/fusao_carrinho_login.puml)
* **Tema:** Mesclagem do Carrinho de Compras no Login do Cliente.
* **Conteúdo:** Demonstra a migração dos produtos de um carrinho anônimo (gravados sob `session_id`) para a conta do cliente autenticado no MySQL (`CartMapper::mergeCartOnLogin`), com consolidação de quantidades e resolução de conflitos.

### 5. [resolucao_seo_url.puml](/docs/workflows/sequence_diagrams/resolucao_seo_url.puml)
* **Tema:** Resolução Dinâmica de SEO URLs e Cache de Roteamento.
* **Conteúdo:** Exibe a captura de URLs amigáveis pelo `LegacyRouteRedirectMiddleware`, a busca no `SeoUrlRepository` utilizando a camada de cache em disco `FilesystemCacheStrategy`, e o despacho interno para a Action correspondente.

### 6. [calculo_frete_strategy.puml](/docs/workflows/sequence_diagrams/calculo_frete_strategy.puml)
* **Tema:** Cálculo de Frete Multi-Estratégia (GoF Strategy Pattern).
* **Conteúdo:** Mostra a iteração polimórfica sobre os serviços independentes de frete (`FlatRateShippingService`, `WeightBasedShippingService` e `FreeShippingService`), agregando as opções disponíveis e devolvendo para a interface do checkout.

---

## Validação de Arquivos

Todos os 6 arquivos foram verificados no repositório:
- Estrutura PlantUML `@startuml ... @enduml` válida.
- Numeração automática (`autonumber`), blocos organizados (`box`) e notas explicativas inclusas.
- Alinhados rigorosamente com a arquitetura definida no [architecture-validator SKILL.md](/.agents/skills/architecture-validator/SKILL.md) e no [gof-redis-patterns SKILL.md](/.agents/skills/gof-redis-patterns/SKILL.md).

