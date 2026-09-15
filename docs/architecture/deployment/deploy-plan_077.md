# DP-77: Reforço e Validação de Segurança & Auditoria (Alpha Engine)

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-08-13 21:18:27
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/77

## Descrição

# Plano de Implementação - Reforço e Validação de Segurança & Auditoria (Alpha Engine)

Este plano descreve o roteiro de execução para validar, integrar e aprimorar os requisitos descritos em [`security-and-audit-architecture.md`](/backend/docs/architecture/security-and-audit-architecture.md) no On-Premise **Alpha Engine**.

---

## 🎯 Objetivos

1. **Garantir a Cobertura Integral dos Middlewares PSR-15** (`SecurityHeaders`, `RateLimit`, `CsrfGuard`, `Signature`, `AdminSession`).
2. **Conformidade LGPD nos Logs de Auditoria** (mascaramento de PII e redação de credenciais via `LgpdSanitizer`).
3. **Auditoria Assíncrona via Mensageria** (worker de fila RabbitMQ para gravação de eventos de auditoria sem bloquear requisições HTTP).
4. **Isolamento Multi-Tenant Estrito** (`store_id` em repositórios) e proteção de arquivos em `storage/` e `public_html/image/`.
5. **Validação Automatizada de Segurança** via suíte de testes unitários e de integração PHPUnit.

---

## ⚠️ User Review Required

> [!IMPORTANT]
> **Definições de Infraestrutura & Configuração:**
> - O worker de fila para auditoria necessita que a extensão `sockets` / servidor RabbitMQ esteja acessível (host/porta configurados no `.env`). O sistema possui suporte resiliente para log local se a mensageria estiver desativada.
> - O Rate Limiting usa o Redis quando disponível, com fallback automático para arquivos locais em `storage/cache/rate_limit/`.

---

## 🛠️ Modificações Propostas

### 1. Camada de Middlewares & Roteamento

#### [MODIFY] [`index.php`](/public_html/index.php)
- Garantir que a ordem de execução no Slim PSR-15 atenda à hierarquia de segurança (`SecurityHeadersMiddleware` -> `RateLimitMiddleware` -> `CsrfGuardMiddleware` -> `RoutingMiddleware`).
- Integrar `LgpdSanitizer` ao manipulador global de erros 500 para evitar o vazamento de dados sensíveis e PII em traces de exceção.

#### [MODIFY] [`Routes.php`](/backend/Config/Routes.php)
- Validar a aplicação do `authRateLimiter` (10 req/min) em rotas críticas (login, cadastro, recuperar senha).
- Aplicar `SignatureMiddleware` explicitamente no grupo de APIs/Webhooks sensíveis (`/api/*`).

---

### 2. Camada de Privacidade & Auditoria LGPD

#### [MODIFY] [`AdminAuthService.php`](/backend/core/Auth/Services/AdminAuthService.php)
- Reforçar o mascaramento de logs de login mal-sucedido e bloqueio (`LOCKED_OUT`) com `LgpdSanitizer::sanitizeLogMessage()`.

#### [NEW] [`AuditQueueWorker.php`](/backend/scripts/audit_queue_worker.php)
- Criar script worker CLI em background para consumir mensagens da fila de auditoria no RabbitMQ (`audit_events`) e gravar os logs em `storage/logs/audit.log` sanitizados.

---

### 3. Validação de Isolamento Multi-Tenant & Hardening de Uploads

#### [MODIFY] [`AbstractRepository.php`](/backend/core/Model/Domain/Repositories/AbstractRepository.php)
- Garantir a checagem rigorosa de `store_id` em consultas e buscas de agregados para prevenir *cross-tenant data leakage*.

#### [MODIFY] [`UploadSecurityHelper.php`](/backend/core/Support/UploadSecurityHelper.php)
- Validar sanitização anti-*path traversal* (`../`) e Magic Bytes (`finfo_file`) para evitar upload de scripts maliciosos.

---

## 🧪 Plano de Verificação

### Testes Automatizados (PHPUnit)

Executar a suíte de testes de validação de segurança e middlewares:

```bash
vendor/bin/phpunit tests/Validation/SecurityHeadersAndCsrfTest.php
vendor/bin/phpunit tests/Validation/AuthenticationBruteForceTest.php
vendor/bin/phpunit tests/Validation/TenantIsolationTest.php
vendor/bin/phpunit tests/Validation/WebhookSignatureTest.php
vendor/bin/phpunit tests/Validation/LoggingValidationTest.php
vendor/bin/phpunit tests/Validation/MimeTypeUploadTest.php
```

### Verificação Manual
1. Testar submissão AJAX sem CSRF e validar a resposta JSON HTTP 400.
2. Testar limites de requisições excedendo a cota de rate limit e checar os cabeçalhos `Retry-After` e HTTP 429.
3. Verificar a gravação de logs em `storage/logs/` confirmando o mascaramento de CPF/E-mail/Cartão.

# Tarefas de Execução — Segurança e Auditoria

- [x] Auditar e ajustar a ordem do pipeline PSR-15 de middlewares em `public_html/index.php` e `Config/Routes.php`
- [x] Integrar `LgpdSanitizer` nos tratadores de erro 500 em `public_html/index.php` e logs de autenticação
- [x] Criar o worker de fila CLI em background para eventos de auditoria (`scripts/audit_queue_worker.php`)
- [x] Validar isolamento multi-tenant (`store_id`) e proteção do diretório de uploads
- [x] Executar suíte de testes automatizados PHPUnit e gerar o walkthrough de conclusão

# Walkthrough — Implementação de Segurança, Auditoria & LGPD (Alpha Engine)

Concluímos a integração, adaptação de documentos e validação das especificações de segurança e auditoria no **Alpha Engine On-Premise**.

---

## 🎯 O que foi Realizado

### 1. Documento de Arquitetura de Segurança Atualizado
- Modificado o arquivo [`security-and-audit-architecture.md`](/backend/docs/architecture/security-and-audit-architecture.md) para refletir rigorosamente o ecossistema real da **Alpha Engine** (Slim 4, PHP 8.4, Twig 3.x, Redis, RabbitMQ, MySQL 8.0).

### 2. Ajuste Fino no Pipeline PSR-15 & Roteamento HMAC
- Em [`Config/Routes.php`](/backend/Config/Routes.php), adicionamos o grupo de rotas `/api/webhook/{provider}` sob o [`SignatureMiddleware`](/backend/core/Auth/Middleware/SignatureMiddleware.php) com validação de HMAC-SHA256 (`X-Signature`).
- Em [`public_html/index.php`](/public_html/index.php), estruturamos a ordem do pipeline PSR-15 e integramos a higienização de logs no manipulador de exceções.

### 3. Sanitização LGPD nos Tratadores Globais de Erro
- Integrado o [`LgpdSanitizer`](/backend/core/Support/LgpdSanitizer.php) ao manipulador de exceções 500 em [`public_html/index.php`](/public_html/index.php), garantindo que dados sensíveis (senhas, tokens, CPFs, e-mails e cartões) sejam omitidos/mascarados antes de gravar em `error_log`.

### 4. Worker CLI de Mensageria para Auditoria Assíncrona
- Criado o script CLI [`scripts/audit_queue_worker.php`](/backend/scripts/audit_queue_worker.php) para consumir mensagens da fila `audit_events` do RabbitMQ, aplicar sanitização LGPD em background e registrar logs auditáveis em `storage/logs/audit.log`.

### 5. Ajuste na Suíte de Testes
- Atualizado o [`phpunit.xml`](/backend/phpunit.xml) para limitar a varredura da testsuite ao diretório `tests/Validation`, eliminando warnings de varredura em scripts utilitários.

---

## 🧪 Resultados da Validação Automatizada

A suíte completa de testes unitários e de integração de segurança foi executada com sucesso:

```bash
vendor/bin/phpunit
```

**Resultado da execução:**
```text
PHPUnit 13.3.0 by Sebastian Bergmann and contributors.

Runtime:       PHP 8.4.22
Configuration: /var/www/html/agsonhos/backend/phpunit.xml

...........................................................  61 / 61 (100%)

Time: 00:01.176, Memory: 32.50 MB
OK (61 tests, 168 assertions)
```

Todos os **61 testes e 168 asserções** (incluindo Rate Limit, Proteção anti-CSRF, Cabeçalhos de Segurança, Assinatura de Webhooks, Isolamento Multi-Tenant e Higienização LGPD) passaram com **100% de aprovação** e zero avisos!

