# DP-56: Proteção dos Diretórios de Uploads (public_html/image e storage/)

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-07-29 00:57:29
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/56

## Descrição

# Plano de Implementação - Proteção dos Diretórios de Uploads (public_html/image e storage/)

Este plano descreve o endurecimento de segurança (*Hardening*) dos diretórios de arquivos da **Alpha Engine** ([public_html/image](/public_html/image) e [storage](/storage)), prevenindo a execução remota de código (RCE - *Remote Code Execution*) via upload de scripts maliciosos e o acesso direto a dados sensíveis de auditoria.

## User Review Required

> [!IMPORTANT]
> **Bloqueio Total de Execução de Scripts PHP em Uploads**:
> - O diretório `public_html/image/` aceitará exclusivamente arquivos de imagem estáticos (`.jpg`, `.jpeg`, `.png`, `.gif`, `.webp`, `.svg`, `.ico`, `.avif`).
> - Qualquer tentativa de executar scripts PHP, PHTML, PHAR ou arquivos executáveis dentro de `public_html/image/` será bloqueada no servidor web com o código **HTTP 403 Forbidden**.

> [!NOTE]
> **Isolamento Completo do Diretório `storage/`**:
> - O diretório `storage/` (que abriga logs, arquivos de sessão e caches do sistema) terá o acesso HTTP bloqueado por completo para visitantes externos via `.htaccess`.

## Proposed Changes

---

### Endurecimento por Servidor Web (.htaccess & Proteções)

#### [NEW] [storage/.htaccess](/storage/.htaccess)
- Criar a regra `.htaccess` bloqueando todo o tráfego HTTP direto para qualquer subdiretório do `storage/`:
  ```apache
  <IfModule mod_authz_core.c>
      Require all denied
  </IfModule>
  <IfModule !mod_authz_core.c>
      Order deny,allow
      Deny from all
  </IfModule>
  ```

#### [NEW] [public_html/image/.htaccess](/public_html/image/.htaccess)
- Criar a regra `.htaccess` com mitigação contra RCE no diretório público de mídias:
  1. Desativar a listagem de arquivos de diretório (`Options -Indexes`).
  2. Negação explícita de execução para extensões de script (`.php`, `.phtml`, `.php5`, `.phar`, `.inc`, `.sh`, `.cgi`, etc.).
  3. Desativação do motor PHP (`php_flag engine off`) em ambientes Apache mod_php.

---

### Sanitização e Validação no Código PHP

#### [NEW] [UploadSecurityHelper.php](/core/Support/UploadSecurityHelper.php)
- Criar a classe utilitária `UploadSecurityHelper` com métodos estáticos para:
  - Validar o MIME type real via `finfo_file()` (garantindo que o arquivo enviado é verdadeiramente uma imagem e não um script disfarçado).
  - Sanitizar nomes de arquivos enviados para prevenir o ataque *Path Traversal* (`../`).
  - Gerar nomes únicos e aleatórios seguros para novos uploads.

---

## Verification Plan

### Automated Tests
- Criar o script de teste automatizado `tests/security_tests/teste_upload_protection.php` simulando:
  1. Leitura do arquivo `.htaccess` em `storage/` e `public_html/image/` confirmando a presença das diretivas de bloqueio.
  2. Validação da classe `UploadSecurityHelper` na rejeição de arquivos `.php`, `.phtml` e MIME-types falsos.
  3. Sanitização de tentativas de Path Traversal (`../../etc/passwd`).

### Manual Verification
- Testar a gravação de um arquivo simulado `test.php` em `public_html/image/` e requisições HTTP para a URL para verificar o bloqueio de execução.

# Lista de Tarefas - Proteção dos Diretórios de Uploads (public_html/image e storage/)

- [x] Criar `storage/.htaccess` negando acesso HTTP público a todos os arquivos de logs, cache e sessões
- [x] Criar `public_html/image/.htaccess` desativando a execução de scripts PHP/PHTML/PHAR e desativando listagem de diretórios (`Options -Indexes`)
- [x] Criar a classe utilitária `UploadSecurityHelper.php` em `core/Support/UploadSecurityHelper.php` com validação de MIME-type real e sanitização contra Path Traversal
- [x] Executar `composer dump-autoload` para registrar a nova classe
- [x] Criar e executar o teste automatizado `tests/security_tests/teste_upload_protection.php` para validar as proteções de upload
- [x] Atualizar o relatório final de alteração (Walkthrough)

# Walkthrough - Implementação de Segurança (CSRF, Security Headers, Secure Cookies, Rate Limiting, Debug Mode, Isolamento de Tenants & Proteção de Uploads)

## 🔒 1. Proteção Anti-CSRF
- **[CsrfGuardMiddleware.php](/core/Auth/Middleware/CsrfGuardMiddleware.php)**: Proteção anti-CSRF com inicialização preguiçosa (*lazy initialization*) para evitar erros de sessão prematura e handler de erro JSON/HTML.

---

## 🛡️ 2. Cabeçalhos de Segurança HTTP (Security Headers)
- **[SecurityHeadersMiddleware.php](/core/Auth/Middleware/SecurityHeadersMiddleware.php)**: Cabeçalhos defensivos `X-Frame-Options`, `nosniff`, `XSS-Protection`, `Referrer-Policy`, `Permissions-Policy`, `CSP` e `HSTS` (HTTPS).

---

## 🍪 3. Flag `; Secure` Condicional em Cookies de Sessão
- **[CookieHelper.php](/core/Support/CookieHelper.php)**: Utilitário para formatar cookies HTTP anexando `; Secure` sob conexões HTTPS.

---

## ⚡ 4. Limitação de Taxa por IP (Rate Limiting com Redis & Fallback em Arquivo)
- **[RateLimitMiddleware.php](/core/Auth/Middleware/RateLimitMiddleware.php)**: Cota estrita (10 req/min) para autenticação e cota ampla (60 req/min) para APIs `/api/*`, com fallback local em arquivo para desenvolvimento.

---

## 🛠️ 5. Desativação do Modo de Depuração em Produção & Handler 500
- **[public_html/index.php](/public_html/index.php)** & **[public_html/LPDHED2dC7Gjrg2b/index.php](/public_html/LPDHED2dC7Gjrg2b/index.php)**: Configuração dinâmica de Twig debug e Slim ErrorMiddleware via `.env` (`APP_ENV` / `APP_DEBUG`), com páginas e handlers de erro 500 amigáveis sem vazamento de stack traces em produção.

---

## 🏢 6. Isolamento Rígido de Tenants (`store_id`)
- **[AbstractRepository.php](/core/Model/Domain/Repositories/AbstractRepository.php)**, **[CustomerRepository.php](/core/Model/Domain/Repositories/CustomerRepository.php)** & **[OrderRepository.php](/core/Model/Domain/Repositories/OrderRepository.php)**: Validação estrita de `store_id` para evitar vazamento de dados entre lojas (*cross-tenant data leakage*).

---

## 📁 7. Proteção do Diretório de Uploads (`public_html/image` e `storage/`)

- **[storage/.htaccess](/storage/.htaccess)** (`NEW`): Negação total de acesso HTTP público (`Require all denied` / `Deny from all`) ao diretório de logs, sessões e arquivos de cache.
- **[public_html/image/.htaccess](/public_html/image/.htaccess)** (`NEW`):
  - Bloqueio explícito de execução para extensões de script (`.php`, `.phtml`, `.phar`, `.inc`, `.sh`, `.cgi`, etc.).
  - Desativação do motor PHP (`php_flag engine off`) e da listagem de arquivos de diretórios (`Options -Indexes`).
- **[UploadSecurityHelper.php](/core/Support/UploadSecurityHelper.php)** (`NEW`):
  - Utilitário para inspecionar o MIME-type real via `finfo_file` (Magic Bytes).
  - Sanitização rigorosa contra *Path Traversal* (`../`) e *Null Byte Injection* (`\0`).
  - Bloqueio de ataques de dupla extensão (`exploit.php.jpg`).

---

## 🧪 Resultados dos Testes Automatizados

### Teste de CSRF (`tests/security_tests/teste_csrf.php`)
- **GET / POST / AJAX / Valid Token** -> `PASS`

### Teste de Security Headers (`tests/security_tests/teste_security_headers.php`)
- **Headers OWASP e HSTS** -> `PASS`

### Teste de Cookies Seguros (`tests/security_tests/teste_secure_cookie.php`)
- **HTTP / HTTPS / Logout** -> `PASS`

### Teste de Rate Limiting (`tests/security_tests/teste_rate_limit.php`)
- **Cota / HTTP 429 / Isolamento por IP** -> `PASS`

### Teste de Modo Debug & Erro 500 (`tests/security_tests/teste_debug_mode.php`)
- **Modo Dev / Modo Prod / AJAX 500** -> `PASS`

### Teste de Isolamento de Tenants (`tests/security_tests/teste_tenant_isolation.php`)
- **Acesso Cross-Tenant Bloqueado** -> `PASS`

### Teste de Proteção de Uploads (`tests/security_tests/teste_upload_protection.php`)
- **.htaccess em `storage/` e `image/`**: Regras ativas -> `PASS`
- **Sanitização de Path Traversal**: `../../etc/passwd` limpo -> `PASS`
- **Validação de Imagem PNG Real**: Aceito -> `PASS`
- **Script PHP Disfarçado de JPG**: Rejeitado -> `PASS`
- **Ataque de Dupla Extensão (`shell.php.jpg`)**: Rejeitado -> `PASS`

