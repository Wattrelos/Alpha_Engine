# DP-57: Conformidade LGPD (Anonimização e Sanitização de Logs)

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-07-29 01:03:54
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/57

## Descrição

# Plano de Implementação - Conformidade LGPD (Anonimização e Sanitização de Logs)

Este plano descreve o projeto e a integração da camada de **Conformidade LGPD (Lei nº 13.709/2018)** para a **Alpha Engine**, através da criação de um mecanismo automatizado de anonimização e mascaramento de **Dados Pessoais Identificáveis (PII)** em arquivos de log, trilhas de auditoria e relatórios de erros.

## User Review Required

> [!IMPORTANT]
> **Mascaramento e Redação Automática de PII em Logs**:
> - **Senhas, Tokens, JWT, Chaves Secretas e CVVs**: Serão completamente substituídos por `[REDACTED]`.
> - **CPF / CNPJ**: Terão seus dígitos centrais mascarados (ex: `123.***.***-45` ou `12.***.*** /0001-**`).
> - **Endereços de E-mail**: Terão a parte local do usuário mascarada (ex: `j***@dominio.com`).
> - **Números de Cartão de Crédito**: Apenas os 4 últimos dígitos serão preservados (ex: `****-****-****-1234`).

> [!NOTE]
> Nenhum dado sensível de clientes ou administradores será gravado em texto claro nos logs de auditoria (`storage/logs/admin_login.log`, `storage/logs/error.log`, etc.).

## Proposed Changes

---

### Camada de Suporte (Sanitizador LGPD)

#### [NEW] [LgpdSanitizer.php](file:///var/www/html/agsonhos/core/Support/LgpdSanitizer.php)
- Criar a classe `LgpdSanitizer` em `core/Support/`:
  - `sanitizeLogMessage(string $message): string`: Aplica Expressões Regulares (PCRE) para detectar e mascarar CPFs, CNPJs, cartões de crédito, senhas e tokens em strings brutas de log.
  - `sanitizeArray(array $data): array`: Recursivo, higieniza arrays de dados (ex: `$_POST`, `$_SESSION`, payloads JSON), substituindo chaves de alta sensibilidade (`password`, `pass`, `token`, `secret`, `card_number`, `cvv`) por `[REDACTED]`.
  - `maskEmail(string $email): string`: Mascara a parte local do e-mail para auditoria segura sem rastreamento pessoal direto.

---

### Integração nos Serviços de Log

#### [MODIFY] [AdminAuthService.php](file:///var/www/html/agsonhos/core/Auth/Services/AdminAuthService.php)
- Atualizar o método `logAdminLogin` para submeter os dados do evento de auditoria ao `LgpdSanitizer` antes da gravação em `storage/logs/admin_login.log`.

---

## Verification Plan

### Automated Tests
- Criar o script de teste automatizado `tests/security_tests/teste_lgpd_sanitizer.php` submetendo strings e arrays com dados sensíveis simulados (CPF, CNPJ, senhas, e-mails e cartões) para verificar:
  1. O mascaramento correto dos dígitos do CPF e CNPJ.
  2. A substituição completa de senhas e tokens por `[REDACTED]`.
  3. O mascaramento parcial de e-mails (`j***@dominio.com`).
  4. A integridade do arquivo de log sem vazar PII em texto claro.

### Manual Verification
- Inspecionar os arquivos de log gerados em `storage/logs/` e confirmar a ausência de dados pessoais expostos em texto claro.

# Lista de Tarefas - Conformidade LGPD (Anonimização e Sanitização de Logs)

- [x] Criar a classe utilitária `LgpdSanitizer.php` em `core/Support/LgpdSanitizer.php`
- [x] Atualizar o `AdminAuthService.php` para higienizar logs de login com o `LgpdSanitizer`
- [x] Executar `composer dump-autoload` para registrar a nova classe
- [x] Criar e executar o teste automatizado `tests/security_tests/teste_lgpd_sanitizer.php` para validar o mascaramento de PII
- [x] Atualizar o relatório final de alteração (Walkthrough)

# Walkthrough - Implementação Completa de Segurança (Alpha Engine Security Hardening)

Todas as **8 Recomendações Prioritárias de Segurança** foram implementadas, testadas e integradas com sucesso na **Alpha Engine**.

---

## 🔒 1. Proteção Anti-CSRF (Cross-Site Request Forgery)
- **[CsrfGuardMiddleware.php](file:///var/www/html/agsonhos/core/Auth/Middleware/CsrfGuardMiddleware.php)**: Middleware anti-CSRF com inicialização preguiçosa (*lazy initialization*) e respostas apropriadas (JSON 400 para AJAX, HTML 400 para navegadores).

---

## 🛡️ 2. Cabeçalhos de Segurança HTTP (Security Headers)
- **[SecurityHeadersMiddleware.php](file:///var/www/html/agsonhos/core/Auth/Middleware/SecurityHeadersMiddleware.php)**: Cabeçalhos defensivos `X-Frame-Options: SAMEORIGIN`, `nosniff`, `XSS-Protection`, `Referrer-Policy`, `Permissions-Policy`, `CSP` e `HSTS` (HTTPS).

---

## 🍪 3. Flag `; Secure` Condicional em Cookies de Sessão
- **[CookieHelper.php](file:///var/www/html/agsonhos/core/Support/CookieHelper.php)**: Utilitário para emissão de cookies de sessão anexando `; Secure` sob conexões HTTPS.

---

## ⚡ 4. Limitação de Taxa por IP (Rate Limiting com Redis & Fallback em Arquivo)
- **[RateLimitMiddleware.php](file:///var/www/html/agsonhos/core/Auth/Middleware/RateLimitMiddleware.php)**: Cota estrita (10 req/min) para rotas de login/cadastro e cota de API (60 req/min), com fallback em arquivo local.

---

## 🛠️ 5. Desativação do Modo de Depuração em Produção & Handler 500
- **[public_html/index.php](file:///var/www/html/agsonhos/public_html/index.php)** & **[public_html/LPDHED2dC7Gjrg2b/index.php](file:///var/www/html/agsonhos/public_html/LPDHED2dC7Gjrg2b/index.php)**: Alternância dinâmica de debug baseada no `.env` (`APP_ENV` / `APP_DEBUG`) com views de erro 500 amigáveis.

---

## 🏢 6. Isolamento Rígido de Tenants (`store_id`)
- **[AbstractRepository.php](file:///var/www/html/agsonhos/core/Model/Domain/Repositories/AbstractRepository.php)**, **[CustomerRepository.php](file:///var/www/html/agsonhos/core/Model/Domain/Repositories/CustomerRepository.php)** & **[OrderRepository.php](file:///var/www/html/agsonhos/core/Model/Domain/Repositories/OrderRepository.php)**: Filtragem e asserção de `store_id` prevenindo vazamento de dados *cross-tenant*.

---

## 📁 7. Proteção do Diretório de Uploads (`public_html/image` e `storage/`)
- **[storage/.htaccess](file:///var/www/html/agsonhos/storage/.htaccess)** & **[public_html/image/.htaccess](file:///var/www/html/agsonhos/public_html/image/.htaccess)**: Negação de acesso público ao `storage/` e desativação de execução de scripts PHP/CGI no `image/`.
- **[UploadSecurityHelper.php](file:///var/www/html/agsonhos/core/Support/UploadSecurityHelper.php)**: Inspeção do MIME-type real (Magic Bytes) e sanitização contra *Path Traversal* e dupla extensão.

---

## ⚖️ 8. Conformidade LGPD (Anonimização e Sanitização de Logs)

- **[LgpdSanitizer.php](file:///var/www/html/agsonhos/core/Support/LgpdSanitizer.php)** (`NEW`):
  - Utilitário automatizado para mascarar Dados Pessoais Identificáveis (PII) em logs.
  - Mascara CPFs (`123.***.***-00`), CNPJs (`12.***.***/****-99`), Números de Cartão (`****-****-****-1234`) e E-mails (`j***@dominio.com`).
  - Redige senhas, tokens de autenticação e segredos por `[REDACTED]`.
- **[AdminAuthService.php](file:///var/www/html/agsonhos/core/Auth/Services/AdminAuthService.php)**:
  - Integrada a sanitização do `LgpdSanitizer` na gravação do histórico de auditoria de login (`storage/logs/admin_login.log`).

---

## 🧪 Suíte de Testes Automatizados Executada (100% PASS)

| Teste | Descrição | Resultado |
|---|---|---|
| `teste_csrf.php` | Validação anti-CSRF em requisições GET, POST e AJAX | ✅ PASS |
| `teste_security_headers.php` | Verificação dos cabeçalhos defensivos HTTP e HSTS | ✅ PASS |
| `teste_secure_cookie.php` | Validação da flag `; Secure` condicional a HTTPS | ✅ PASS |
| `teste_rate_limit.php` | Validação do bloqueio HTTP 429 e cabeçalhos `X-RateLimit` | ✅ PASS |
| `teste_debug_mode.php` | Validação da ocultação de stack traces e erro 500 em produção | ✅ PASS |
| `teste_tenant_isolation.php` | Bloqueio de acesso a clientes e pedidos entre lojas | ✅ PASS |
| `teste_upload_protection.php` | Proteção .htaccess e rejeição de scripts maliciosos/fake JPGs | ✅ PASS |
| `teste_lgpd_sanitizer.php` | Mascaramento de PII (CPF, CNPJ, e-mail, senha, cartão) em logs | ✅ PASS |

