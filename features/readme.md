# Documentação & Suíte de Testes BDD com Gherkin (Behat)

## 📌 Contexto Acadêmico
Esta suíte de testes BDD (Behavior-Driven Development) utilizando a sintaxe **Gherkin** e a ferramenta **Behat** foi projetada para atender integralmente aos requisitos da disciplina de **Testes de Software**.

Ela integra-se diretamente com a aplicação **Alpha Engine**, reaproveitando e estendendo a robusta biblioteca de testes unitários e de integração desenvolvidos em PHPUnit (`tests/Validation/`).

---

## 🚀 Como Executar os Testes

### 1. Execução dos Testes BDD (Gherkin / Behat)
```bash
# Execução direta via Behat
./vendor/bin/behat --no-snippets

# Ou utilizando o atalho via Composer
composer test:behat
```

### 2. Execução da Bateria Unitária & Integração (PHPUnit)
```bash
# Executa os testes de validação unitária e de integração em PHPUnit
composer test:phpunit
```

### 3. Execução dos Testes End-to-End (Playwright)
```bash
# Executa a suíte de testes E2E cross-browser e acessibilidade
composer test:e2e
# Ou diretamente pelo NPM
npm run test:e2e:chromium
```

### 4. Execução Completa da Pirâmide de Testes (PHPUnit + Behat + Playwright)
```bash
composer test:all
```

---

## 🗺️ Tabela de Rastreabilidade Acadêmica

| Caso de Uso / Requisito | Arquivo Feature (Gherkin) | Contexto Behat | Validação / Teste Backend Reutilizado |
| :--- | :--- | :--- | :--- |
| **Módulo Arquitetura: Bootstrap & PSR-11** | [bootstrapping_injecao_psr11.feature](/features/architecture/bootstrapping_injecao_psr11.feature) | `FeatureContext` | `AppContainer (PSR-11)`, `Routes.php` & `TwigEnvironment` |
| **Módulo Arquitetura: Middleware & Redis Session** | [middleware_pipeline_sessoes_redis.feature](/features/architecture/middleware_pipeline_sessoes_redis.feature) | `FeatureContext` | `AdminSessionMiddleware` / Autenticação Centralizada |
| **Módulo Arquitetura: Unit of Work & ACID** | [unit_of_work_transacoes_acid.feature](/features/architecture/unit_of_work_transacoes_acid.feature) | `FeatureContext` | `UnitOfWork` / `BEGIN` e `COMMIT` Transacional MySQL 8 |
| **Módulo Arquitetura: Identity Map & Cache** | [identity_map_cache_repositorios.feature](/features/architecture/identity_map_cache_repositorios.feature) | `FeatureContext` | Prevenção de N+1 Queries e Cache de Repositórios no Redis |
| **Módulo Arquitetura: Domain Events & RabbitMQ** | [eventos_dominio_rabbitmq_workers.feature](/features/architecture/eventos_dominio_rabbitmq_workers.feature) | `FeatureContext` | `EventDispatcher`, Filas RabbitMQ e Worker Assíncrono |
| **Módulo Arquitetura: Adaptadores Legados** | [compatibilidade_adaptadores_legados.feature](/features/architecture/compatibilidade_adaptadores_legados.feature) | `FeatureContext` | `AlphaContainer (Legacy Resolver)` / Retrocompatibilidade |
| **Módulo Segurança: Cabeçalhos OWASP** | [cabecalhos_owasp.feature](/features/security/cabecalhos_owasp.feature) | `SecurityContext` | `SecurityHeadersMiddleware` / CSP, HSTS & Frames |
| **Módulo Segurança: Rate Limiting Anti-Brute Force** | [rate_limiting_brute_force.feature](/features/security/rate_limiting_brute_force.feature) | `SecurityContext` | `AuthenticationBruteForceTest.php` / HTTP 429 & Retry-After |
| **Módulo Segurança: Controle de Acesso RBAC** | [controle_acesso_rbac.feature](/features/security/controle_acesso_rbac.feature) | `SecurityContext` | `RbacAccessControlTest.php` / HTTP 403 Forbidden |
| **Módulo Segurança: Proteção CSRF Checkout** | [protecao_csrf.feature](/features/security/protecao_csrf.feature) | `CheckoutContext` | `CheckoutCsrfIntegrationTest.php` / `CsrfGuardMiddleware` |
| **Módulo Segurança: Prevenção SQLi & XSS** | [prevencao_sqli_xss.feature](/features/security/prevencao_sqli_xss.feature) | `SecurityContext` | `TwigEnvironment` Sanitization & PDO Prepared Statements |
| **Módulo Segurança: Sessões e Cookies Seguros** | [gestao_sessoes_cookies.feature](/features/security/gestao_sessoes_cookies.feature) | `SecurityContext` | `SessionManager` / Anti-Fixation & HttpOnly |
| **Módulo Segurança: Prevenção IDOR** | [prevencao_idor_acesso.feature](/features/security/prevencao_idor_acesso.feature) | `SecurityContext` | Validação de Ownership em Pedidos e Endereços |
| **Módulo Casos de Uso: Catálogo & Busca (UC01, UC02)** | [uc01_uc02_catalogo_busca.feature](/features/use_cases/uc01_uc02_catalogo_busca.feature) | `CheckoutContext` | Navegação, Vitrines e Filtros Facetados |
| **Módulo Casos de Uso: Carrinho & Variantes (UC03, UC04)** | [uc03_uc04_adicionar_carrinho_variantes.feature](/features/use_cases/uc03_uc04_adicionar_carrinho_variantes.feature) | `CheckoutContext` | Inclusão de SKU com Variações e Validação de Estoque |
| **Módulo Casos de Uso: Login & Mesclagem (UC05, UC06)** | [uc05_uc06_login_mesclagem_carrinho.feature](/features/use_cases/uc05_uc06_login_mesclagem_carrinho.feature) | `CheckoutContext` | Autenticação e Sincronização de Carrinho Anônimo |
| **Módulo Casos de Uso: Checkout & Guest (UC07, UC08, UC09)** | [uc07_uc08_uc09_checkout_cupons_guest.feature](/features/use_cases/uc07_uc08_uc09_checkout_cupons_guest.feature) | `CheckoutContext` | Cupons Promocionais e Compra Expressa Guest |
| **Módulo Casos de Uso: Pagamentos & Gateway (UC12)** | [uc12_processamento_pagamentos_gateway.feature](/features/use_cases/uc12_processamento_pagamentos_gateway.feature) | `CheckoutContext` | Autorização Gateway, Emissão NF-e e Recusa |
| **Módulo Casos de Uso: Pós-Venda (UC10, UC11)** | [uc10_uc11_pos_venda_pedidos_devolucoes.feature](/features/use_cases/uc10_uc11_pos_venda_pedidos_devolucoes.feature) | `CheckoutContext` | Rastreamento Last-mile e Devolução CDC 7 dias |
| **Módulo Frontend: Catálogo & Menu** | [navegacao_catalogo.feature](/features/frontend/navegacao_catalogo.feature) | `FrontendContext` | `LayoutMapper` / Categorias, Banners e Vitrines |
| **Módulo Frontend: Busca & Filtros** | [busca_filtros.feature](/features/frontend/busca_filtros.feature) | `FrontendContext` | `ProductRepository` / Busca Fulltext & Autocomplete |
| **Módulo Frontend: Detalhes do Produto** | [detalhes_produto_pdp.feature](/features/frontend/detalhes_produto_pdp.feature) | `FrontendContext` | `ProductDetailAction` / Variantes, Zoom & Cross-selling |
| **Módulo Frontend: Painel do Cliente** | [painel_cliente.feature](/features/frontend/painel_cliente.feature) | `FrontendContext` | `CustomerOrdersAction` / Rastreamento & Devoluções |
| **Módulo Autenticação: Login & Sessão** | [login.feature](/features/auth/login.feature) | `AuthContext` | `CustomerAuthService`, `LoginAction` & `LogoutAction` |
| **Módulo Carrinho: Adição & Variantes** | [adicionar_produto.feature](/features/cart/adicionar_produto.feature) | `CartContext` | `AddCartAction` / Validação de estoque e variantes |
| **Módulo Carrinho: Gestão de Itens** | [gerenciar_itens_carrinho.feature](/features/cart/gerenciar_itens_carrinho.feature) | `CartContext` | `EditCartAction` & `RemoveCartAction` |
| **Módulo Carrinho: Cálculo de Frete** | [calculo_frete_carrinho.feature](/features/cart/calculo_frete_carrinho.feature) | `CartContext` | `SaveShippingCepAction` / Correios, Transportadora & BOPIS |
| **Módulo Carrinho: Sincronização** | [sincronizacao_carrinho.feature](/features/cart/sincronizacao_carrinho.feature) | `CartContext` | `SyncCartAction` / Mesclagem visitante -> logado |
| **Módulo Checkout: Fluxo do Cliente** | [fluxo_checkout_cliente.feature](/features/checkout/fluxo_checkout_cliente.feature) | `CheckoutContext` | `Checkout.php` / Multi-endereços, CSRF & Cupons |
| **Módulo Checkout: Visitante (Guest)** | [checkout_visitante_guest.feature](/features/checkout/checkout_visitante_guest.feature) | `CheckoutContext` | `SubmitCheckoutAction` / Validação CPF e dados |
| **Módulo Checkout: Pagamentos & UoW** | [processamento_pagamentos.feature](/features/checkout/processamento_pagamentos.feature) | `CheckoutContext` | `ProcessPaymentAction` / PIX, Boleto, Cartão & RabbitMQ |
| **Módulo Checkout: Idempotência** | [idempotencia_checkout.feature](/features/checkout/idempotencia_checkout.feature) | `CheckoutContext` | `SubmitCheckoutAction` / Redis Lock anti-duplicidade |
| **Módulo API: Carrinho & Frete** | [carrinho_checkout_api.feature](/features/api/carrinho_checkout_api.feature) | `ApiContext` | `/api/carrinho/*` / Totais, CEP e Sincronização JSON |
| **Módulo API: Geolocalização** | [localizacao_geozonas_api.feature](/features/api/localizacao_geozonas_api.feature) | `ApiContext` | `/api/geo/*` / Consulta em cascata de Países, UFs e Cidades |
| **Módulo API: Webhooks & HMAC** | [webhooks_assinatura_hmac.feature](/features/api/webhooks_assinatura_hmac.feature) | `ApiContext` | `/api/webhook/*` / `SignatureMiddleware` HMAC SHA-256 |
| **Módulo API: Busca & Estoque** | [catalogo_busca_autocomplete_api.feature](/features/api/catalogo_busca_autocomplete_api.feature) | `ApiContext` | `/api/busca/*` / Autocomplete em tempo real e estoque SKU |
| **Módulo API: Rate Limit & RFC 7807** | [contratos_rest_rate_limit_api.feature](/features/api/contratos_rest_rate_limit_api.feature) | `ApiContext` | Limite 60 req/min e Problem Details RFC 7807 |

---

## ⚙️ Estrutura de Diretórios dos Testes

```
agsonhos/
├── behat.yml                            # Configuração global de suítes e contextos Behat
├── composer.json                        # Scripts de automação ('test:behat', 'test:phpunit', etc.)
├── features/                            # Especificações executáveis escritas em Gherkin (.feature)
│   ├── architecture/                    # BDD de Arquitetura DDD, UoW, Identity Map e RabbitMQ
│   │   ├── README.md
│   │   ├── bootstrapping_injecao_psr11.feature
│   │   ├── middleware_pipeline_sessoes_redis.feature
│   │   ├── unit_of_work_transacoes_acid.feature
│   │   ├── identity_map_cache_repositorios.feature
│   │   ├── eventos_dominio_rabbitmq_workers.feature
│   │   └── compatibilidade_adaptadores_legados.feature
│   ├── auth/                            # BDD de Autenticação e Gestão de Sessões
│   │   └── login.feature
│   ├── security/                        # BDD de Segurança, OWASP Headers, RBAC, CSRF, XSS e IDOR
│   │   ├── README.md
│   │   ├── cabecalhos_owasp.feature
│   │   ├── rate_limiting_brute_force.feature
│   │   ├── controle_acesso_rbac.feature
│   │   ├── protecao_csrf.feature
│   │   ├── prevencao_sqli_xss.feature
│   │   ├── gestao_sessoes_cookies.feature
│   │   └── prevencao_idor_acesso.feature
│   ├── use_cases/                       # BDD da Jornada do Cliente (UC01 a UC12)
│   │   ├── README.md
│   │   ├── UseCaseDiagramCustomer.md
│   │   ├── uc01_uc02_catalogo_busca.feature
│   │   ├── uc03_uc04_adicionar_carrinho_variantes.feature
│   │   ├── uc05_uc06_login_mesclagem_carrinho.feature
│   │   ├── uc07_uc08_uc09_checkout_cupons_guest.feature
│   │   ├── uc12_processamento_pagamentos_gateway.feature
│   │   └── uc10_uc11_pos_venda_pedidos_devolucoes.feature
│   ├── frontend/                        # BDD de Frontend, Catálogo, PDP e UX/UI
│   │   ├── README.md
│   │   ├── implementation.md
│   │   ├── navegacao_catalogo.feature
│   │   ├── busca_filtros.feature
│   │   ├── detalhes_produto_pdp.feature
│   │   └── painel_cliente.feature
│   ├── cart/                            # BDD do Carrinho de Compras
│   │   ├── adicionar_produto.feature
│   │   ├── gerenciar_itens_carrinho.feature
│   │   ├── calculo_frete_carrinho.feature
│   │   └── sincronizacao_carrinho.feature
│   ├── checkout/                        # BDD do Fechamento e Pagamento de Pedidos
│   │   ├── fluxo_checkout_cliente.feature
│   │   ├── checkout_visitante_guest.feature
│   │   ├── processamento_pagamentos.feature
│   │   └── idempotencia_checkout.feature
│   ├── api/                             # BDD de Serviços RESTful, Webhooks e Rate Limiting
│   │   ├── README.md
│   │   ├── carrinho_checkout_api.feature
│   │   ├── localizacao_geozonas_api.feature
│   │   ├── webhooks_assinatura_hmac.feature
│   │   ├── catalogo_busca_autocomplete_api.feature
│   │   └── contratos_rest_rate_limit_api.feature
│   ├── reame.md                         # Esta documentação acadêmica
│   └── bootstrap/                       # Implementação dos Step Definitions
│       ├── FeatureContext.php           # Passos gerais de sistema e arquitetura
│       ├── SecurityContext.php          # Passos de segurança, OWASP, rate limit e RBAC
│       ├── FrontendContext.php          # Passos de layout, catálogo, busca, PDP e pós-venda
│       ├── CartContext.php              # Passos de catálogo, carrinho, frete e mesclagem
│       ├── CheckoutContext.php          # Passos de checkout, CSRF, idempotência e pagamentos
│       ├── ApiContext.php               # Passos de APIs REST, Webhooks e Rate Limiting
│       └── AuthContext.php              # Passos de autenticação, login e encerramento de sessão
```

---

## 🧠 Arquitetura de Reaproveitamento de Código

Os testes Gherkin **não duplicam a lógica** da aplicação. Em vez disso:
1. **Passos (`Given`, `When`, `Then`)** invocam diretamente os middlewares Slim 4 (ex: `CsrfGuardMiddleware`, `AdminSessionMiddleware`, `SecurityHeadersMiddleware`).
2. **Validações (`Then`)** empregam a biblioteca de asserções estáticas do PHPUnit (`PHPUnit\Framework\Assert::assertEquals`, `Assert::assertTrue`, `Assert::assertContains`), assegurando padronização total com a suíte unitária.
3. **Mocks e Stubs** usam a infraestrutura nativa do PHPUnit através de classes helper (`BehatTestHelper`).
