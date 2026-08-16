# Especificações BDD: Arquitetura de Software & Padrões DDD

Este diretório contém os arquivos de especificação executável em **Gherkin (.feature)** cobrindo a arquitetura de camadas (DDD), pipeline de execução, gerenciamento transacional e mensageria assíncrona da **Alpha Engine**.

---

## 🏛️ Matriz de Especificações Arquiteturais

| Arquivo Feature | Padrões de Software & Camadas | Tecnologias / Frameworks | Contexto Behat |
| :--- | :--- | :--- | :--- |
| [bootstrapping_injecao_psr11.feature](file:///var/www/html/agsonhos/features/architecture/bootstrapping_injecao_psr11.feature) | Single Action Controllers, Injeção de Dependências PSR-11, Roteamento e View Renderer | Slim 4, Twig, AppContainer | `FeatureContext` |
| [middleware_pipeline_sessoes_redis.feature](file:///var/www/html/agsonhos/features/architecture/middleware_pipeline_sessoes_redis.feature) | Interceptação de Middlewares (PSR-15) e Validação Centralizada de Sessão | Slim PSR-15, Redis Session | `FeatureContext` |
| [unit_of_work_transacoes_acid.feature](file:///var/www/html/agsonhos/features/architecture/unit_of_work_transacoes_acid.feature) | Consistência Transacional ACID, Atomic Commits e Rollbacks Automáticos | UnitOfWork, DataAccessObject, MySQL 8 | `FeatureContext` |
| [identity_map_cache_repositorios.feature](file:///var/www/html/agsonhos/features/architecture/identity_map_cache_repositorios.feature) | Identity Map em Memória RAM, Prevenção de Queries Duplicadas N+1 e Cache de Domínio | IdentityMap, Redis Cache, PDO | `FeatureContext` |
| [eventos_dominio_rabbitmq_workers.feature](file:///var/www/html/agsonhos/features/architecture/eventos_dominio_rabbitmq_workers.feature) | Arquitetura Orientada a Eventos (EDA), Domain Events e Processamento em Segundo Plano | EventDispatcher, RabbitMQ, CLI Workers | `FeatureContext` |
| [compatibilidade_adaptadores_legados.feature](file:///var/www/html/agsonhos/features/architecture/compatibilidade_adaptadores_legados.feature) | Padrão Adapter para retrocompatibilidade com Mappers e Repositórios antigos | AlphaContainer Legacy Resolver | `FeatureContext` |

---

## 🚀 Como Executar

```bash
# Execução direta via Composer
composer test:behat:architecture

# Execução direta via Behat CLI
./vendor/bin/behat features/architecture/ --no-snippets
```
