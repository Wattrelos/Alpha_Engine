# DP-55: Isolamento Rígido de Tenants (store_id)

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-07-29 00:51:45
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/55

## Descrição

# Plano de Implementação - Isolamento Rígido de Tenants (store_id)

Este plano descreve o reforço da camada de **Isolamento Multi-tenant (`store_id`)** na **Alpha Engine**, garantindo que todas as consultas a produtos, pedidos, clientes, categorias e configurações sejam filtradas de forma estrita pela loja ativa (`store_id`), prevenindo totalmente o vazamento de dados entre lojas em um ambiente On-Premise.

## User Review Required

> [!IMPORTANT]
> Em arquiteturas On-Premise Multi-tenant, o vazamento de dados entre inquilinos (*cross-tenant data leakage*) ocorre se qualquer consulta SQL resgatar entidades apenas pelo ID primário (ex: `WHERE id = :id`) sem verificar a posse da loja (`AND store_id = :store_id`).

> [!NOTE]
> **Validação em Cascata de `store_id`**:
> 1. Toda busca individual ou listagem nos Mappers deve conter a restrição do `store_id`.
> 2. O `AbstractRepository` aplicará asserção de `store_id` válido.
> 3. Os métodos de gravação/edição no painel administrativo também validarão se o recurso pertence ao `store_id` do administrador autenticado antes de permitir atualizações.

## Proposed Changes

---

### Camada de Repositórios & Repositório Base (Core Domain)

#### [MODIFY] [AbstractRepository.php](file:///var/www/html/agsonhos/core/Model/Domain/Repositories/AbstractRepository.php)
- Reforçar o getter magico `__get('store_id')` para garantir resolução consistente do `config_store_id` a partir do container ou das configurações globais, com sanitização para tipo `int`.

#### [MODIFY] [CustomerRepository.php](file:///var/www/html/agsonhos/core/Model/Domain/Repositories/CustomerRepository.php)
- Garantir que buscas de clientes por ID, e-mail ou autenticação filtrem explicitamente por `store_id`.

#### [MODIFY] [OrderRepository.php](file:///var/www/html/agsonhos/core/Model/Domain/Repositories/OrderRepository.php)
- Reforçar que a recuperação e alteração de histórico de pedidos exijam `store_id`.

---

### Camada de Mappers (Data Mappers SQL)

#### [MODIFY] [OrderMapper.php](file:///var/www/html/agsonhos/core/Mappers/EntityMappers/OrderMapper.php)
- Garantir a junção e o filtro `o.store_id = :store_id` em consultas de pedidos e relatórios.

#### [MODIFY] [CustomerMapper.php](file:///var/www/html/agsonhos/core/Mappers/EntityMappers/CustomerMapper.php)
- Adicionar/reforçar a cláusula `store_id = :store_id` no resgate e atualização de clientes.

#### [MODIFY] [CategoryMapper.php](file:///var/www/html/agsonhos/core/Mappers/EntityMappers/CategoryMapper.php)
- Garantir a cláusula `c2s.store_id = :store_id` no resgate e busca de categorias por ID.

---

## Verification Plan

### Automated Tests
- Criar script de teste automatizado `tests/security_tests/teste_tenant_isolation.php` simulando consultas em contextos de `store_id = 1` vs `store_id = 2`:
  1. Tentar acessar um pedido/cliente da Loja 2 estando no contexto da Loja 1 -> Deve retornar `null` ou `404`.
  2. Verificar se a busca de categorias/produtos isola estritamente a loja ativa.
  3. Validar se a alteração de registro bloqueia IDs de outros tenants.

### Manual Verification
- Testar requisições no painel e no catálogo alterando o ID do recurso via URL e verificar se o sistema bloqueia acessos entre instâncias de lojas.

# Lista de Tarefas - Isolamento Rígido de Tenants (store_id)

- [x] Reforçar a resolução estrita de `store_id` em `core/Model/Domain/Repositories/AbstractRepository.php`
- [x] Reforçar a cláusula de `store_id` nos Data Mappers (`CustomerMapper.php`, `OrderMapper.php`, `CategoryMapper.php`)
- [x] Reforçar a passagem obrigatória de `store_id` nos repositórios `CustomerRepository.php` e `OrderRepository.php`
- [x] Executar `composer dump-autoload`
- [x] Criar e executar o teste automatizado `tests/security_tests/teste_tenant_isolation.php` para validar a proteção contra acesso cross-tenant
- [x] Atualizar o relatório final de alteração (Walkthrough)

# Walkthrough - Implementação de Segurança (CSRF, Security Headers, Secure Cookies, Rate Limiting, Debug Mode & Isolamento de Tenants)

## 🔒 1. Proteção Anti-CSRF
- **[CsrfGuardMiddleware.php](file:///var/www/html/agsonhos/core/Auth/Middleware/CsrfGuardMiddleware.php)**: Proteção anti-CSRF com inicialização preguiçosa (*lazy initialization*) para evitar erros de sessão prematura e handler de erro JSON/HTML.

---

## 🛡️ 2. Cabeçalhos de Segurança HTTP (Security Headers)
- **[SecurityHeadersMiddleware.php](file:///var/www/html/agsonhos/core/Auth/Middleware/SecurityHeadersMiddleware.php)**: Cabeçalhos defensivos `X-Frame-Options`, `nosniff`, `XSS-Protection`, `Referrer-Policy`, `Permissions-Policy`, `CSP` e `HSTS` (HTTPS).

---

## 🍪 3. Flag `; Secure` Condicional em Cookies de Sessão
- **[CookieHelper.php](file:///var/www/html/agsonhos/core/Support/CookieHelper.php)**: Utilitário para formatar cookies HTTP anexando `; Secure` sob conexões HTTPS.

---

## ⚡ 4. Limitação de Taxa por IP (Rate Limiting com Redis & Fallback em Arquivo)
- **[RateLimitMiddleware.php](file:///var/www/html/agsonhos/core/Auth/Middleware/RateLimitMiddleware.php)**: Cota estrita (10 req/min) para autenticação e cota ampla (60 req/min) para APIs `/api/*`, com fallback local em arquivo para desenvolvimento.

---

## 🛠️ 5. Desativação do Modo de Depuração em Produção & Handler 500
- **[public_html/index.php](file:///var/www/html/agsonhos/public_html/index.php)** & **[public_html/LPDHED2dC7Gjrg2b/index.php](file:///var/www/html/agsonhos/public_html/LPDHED2dC7Gjrg2b/index.php)**: Configuração dinâmica de Twig debug e Slim ErrorMiddleware via `.env` (`APP_ENV` / `APP_DEBUG`), com páginas e handlers de erro 500 amigáveis sem vazamento de stack traces em produção.

---

## 🏢 6. Isolamento Rígido de Tenants (`store_id`)

- **[AbstractRepository.php](file:///var/www/html/agsonhos/core/Model/Domain/Repositories/AbstractRepository.php)**: Reforçada a resolução magica e adicionado o método `getStoreId()`, garantindo resgate estrito do `store_id` a partir do container ou cabeçalho HTTP `X-Store-ID`.
- **[CustomerRepository.php](file:///var/www/html/agsonhos/core/Model/Domain/Repositories/CustomerRepository.php)** & **[CustomerMapper.php](file:///var/www/html/agsonhos/core/Mappers/EntityMappers/CustomerMapper.php)**:
  - `find($id)` e `findByEmail($email)` passam a validar obrigatoriamente se a conta do cliente pertence ao `store_id` da loja ativa.
  - Tentativas de acesso cross-tenant (acessar cliente de outra loja) retornam `null` para prevenir vazamento de PII.
- **[OrderRepository.php](file:///var/www/html/agsonhos/core/Model/Domain/Repositories/OrderRepository.php)** & **[OrderMapper.php](file:///var/www/html/agsonhos/core/Mappers/EntityMappers/OrderMapper.php)**:
  - `getOrder($orderId, $customerId)` passa a incluir a verificação obrigatória de `store_id = :store_id` na consulta SQL.

---

## 🧪 Resultados dos Testes Automatizados

### Teste de CSRF (`tests/security_tests/teste_csrf.php`)
- **GET Request**: Status `200 OK` -> `PASS`
- **POST sem Token / AJAX**: Status `400 Bad Request` -> `PASS`
- **POST com Token Válido**: Status `200 OK` -> `PASS`

### Teste de Security Headers (`tests/security_tests/teste_security_headers.php`)
- **Headers OWASP** e **HSTS (HTTPS)** -> `PASS`

### Teste de Cookies Seguros (`tests/security_tests/teste_secure_cookie.php`)
- **HTTP / HTTPS / Logout**: Flags formatadas corretamente -> `PASS`

### Teste de Rate Limiting (`tests/security_tests/teste_rate_limit.php`)
- **Contagem de Cota / HTTP 429 / Isolamento por IP** -> `PASS`

### Teste de Modo Debug & Erro 500 (`tests/security_tests/teste_debug_mode.php`)
- **Modo Dev / Modo Prod / AJAX 500**: Ocultação de stack trace em produção -> `PASS`

### Teste de Isolamento de Tenants (`tests/security_tests/teste_tenant_isolation.php`)
- **Resolução de `store_id` via Repositórios**: Status OK -> `PASS`
- **Bloqueio de Acesso Cross-Tenant a Clientes**: Registro de outra loja bloqueado (`null`) -> `PASS`
- **Restrição de Pedidos por Loja**: Escopo `store_id` aplicado -> `PASS`

