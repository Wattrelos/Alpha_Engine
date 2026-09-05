# DP-52: Implementação: Flag `; Secure` Condicional em Cookies de Sessão

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-07-28 22:14:06
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/52

## Descrição

# Plano de Implementação - Flag `; Secure` Condicional em Cookies de Sessão

Este plano descreve a criação da classe utilitária `CookieHelper` e o ajuste dos fluxos de login e logout (Catálogo e Painel Administrativo) para anexar dinamicamente a diretiva `; Secure` nos cookies HTTP quando a conexão for realizada sob HTTPS (`$request->getUri()->getScheme() === 'https'`).

## User Review Required

> [!NOTE]
> A flag `; Secure` impede que cookies de sessão sejam transmitidos por conexões de texto simples (HTTP). Para garantir compatibilidade com ambientes de desenvolvimento local rodando via HTTP puro (ex: `http://localhost`), a flag será anexada **condicionalmente** apenas quando a conexão for identificada como segura.

## Proposed Changes

---

### Utilitários e Suporte (Core Support)

#### [NEW] [CookieHelper.php](file:///var/www/html/agsonhos/core/Support/CookieHelper.php)
- Criar a classe `CookieHelper` no namespace `Alpha\Support`.
- Implementar o método `isHttps(ServerRequestInterface $request): bool` para detectar conexões seguras inspecionando o esquema da URI PSR-7, a variável `$_SERVER['HTTPS']` e o cabeçalho `X-Forwarded-Proto`.
- Implementar o método `makeCookieHeader(...)` para formatar a string do cabeçalho `Set-Cookie` aplicando `Path=/`, `HttpOnly`, `SameSite=Lax` e acrescentando `; Secure` quando `isHttps()` for verdadeiro.

---

### Ações de Autenticação (Storefront & Admin)

#### [MODIFY] [LoginAction.php (Customer)](file:///var/www/html/agsonhos/core/Controller/Actions/Customer/Auth/LoginAction.php)
- Atualizar a definição do cookie `session_id` no login de cliente utilizando `CookieHelper::makeCookieHeader($request, 'session_id', $sessionId, 7200)`.

#### [MODIFY] [LogoutAction.php (Customer)](file:///var/www/html/agsonhos/core/Controller/Actions/Customer/Auth/LogoutAction.php)
- Atualizar a expiração do cookie `session_id` no logout de cliente utilizando `CookieHelper::makeCookieHeader($request, 'session_id', '', -1)`.

#### [MODIFY] [LoginAction.php (Admin)](file:///var/www/html/agsonhos/core/Admin/Controllers/Actions/Auth/LoginAction.php)
- Atualizar a definição do cookie `admin_session_id` no login administrativo utilizando `CookieHelper::makeCookieHeader($request, 'admin_session_id', $sessionId, 7200)`.

#### [MODIFY] [LogoutAction.php (Admin)](file:///var/www/html/agsonhos/core/Admin/Controllers/Actions/Auth/LogoutAction.php)
- Atualizar a expiração do cookie `admin_session_id` no logout administrativo utilizando `CookieHelper::makeCookieHeader($request, 'admin_session_id', '', -1)`.

---

## Verification Plan

### Automated Tests
- Criar teste automatizado `tests/security_tests/teste_secure_cookie.php` que simula requisições HTTP e HTTPS para verificar a presença ou ausência da flag `; Secure` na string de `Set-Cookie`.
- Executar `composer dump-autoload` e rodar os testes via PHP CLI.

### Manual Verification
- Validar via chamadas simuladas PSR-7 se conexões HTTP geram `Set-Cookie: session_id=...; HttpOnly; SameSite=Lax` e conexões HTTPS geram `Set-Cookie: session_id=...; HttpOnly; SameSite=Lax; Secure`.

# Lista de Tarefas - Flag `; Secure` Condicional em Cookies de Sessão

- [x] Criar a classe utilitária `CookieHelper.php` em `core/Support/CookieHelper.php`
- [x] Atualizar `Customer\Auth\LoginAction.php` e `LogoutAction.php` para utilizar `CookieHelper`
- [x] Atualizar `Admin\Auth\LoginAction.php` e `LogoutAction.php` para utilizar `CookieHelper`
- [x] Executar `composer dump-autoload` para atualizar o mapa autoritativo de classes
- [x] Criar e executar teste automatizado em `tests/security_tests/teste_secure_cookie.php` para validar as flags de cookie em conexões HTTP e HTTPS
- [x] Atualizar o relatório final de alteração (Walkthrough)

# Walkthrough - Implementação de Segurança (CSRF, Security Headers & Secure Cookies)

## 🔒 1. Proteção Anti-CSRF

- **[composer.json](file:///var/www/html/agsonhos/composer.json)**: Instalada a biblioteca [`slim/csrf`](file:///var/www/html/agsonhos/vendor/slim/csrf).
- **[CsrfGuardMiddleware.php](file:///var/www/html/agsonhos/core/Auth/Middleware/CsrfGuardMiddleware.php)**: Middleware PSR-15 com modo persistente e handler de falhas (JSON 400 para AJAX, HTML 400 para navegadores).
- **[public_html/index.php](file:///var/www/html/agsonhos/public_html/index.php)** e **[public_html/LPDHED2dC7Gjrg2b/index.php](file:///var/www/html/agsonhos/public_html/LPDHED2dC7Gjrg2b/index.php)**: Registrado o middleware nos bootstraps público e administrativo.
- **[form-validator.js](file:///var/www/html/agsonhos/public_html/js/custom/form-validator.js)**: Injeção automática dos tokens CSRF em submissões AJAX.

---

## 🛡️ 2. Cabeçalhos de Segurança HTTP (Security Headers)

- **[SecurityHeadersMiddleware.php](file:///var/www/html/agsonhos/core/Auth/Middleware/SecurityHeadersMiddleware.php)**: Injeta os cabeçalhos defensivos `X-Frame-Options: SAMEORIGIN`, `X-Content-Type-Options: nosniff`, `X-XSS-Protection`, `Referrer-Policy`, `Permissions-Policy`, `Content-Security-Policy` e `HSTS` (sob HTTPS).

---

## 🍪 3. Flag `; Secure` Condicional em Cookies de Sessão

- **[CookieHelper.php](file:///var/www/html/agsonhos/core/Support/CookieHelper.php)** (`NEW`):
  - Utilitário centralizado para formatar o cabeçalho `Set-Cookie` com as diretivas de segurança `Path=/`, `HttpOnly`, `SameSite=Lax` e anexo condicional de `; Secure` quando a requisição for realizada sob conexão HTTPS.
- **Ações de Login & Logout Ajustadas**:
  - **[Customer LoginAction.php](file:///var/www/html/agsonhos/core/Controller/Actions/Customer/Auth/LoginAction.php)** e **[LogoutAction.php](file:///var/www/html/agsonhos/core/Controller/Actions/Customer/Auth/LogoutAction.php)**.
  - **[Admin LoginAction.php](file:///var/www/html/agsonhos/core/Admin/Controllers/Actions/Auth/LoginAction.php)** e **[LogoutAction.php](file:///var/www/html/agsonhos/core/Admin/Controllers/Actions/Auth/LogoutAction.php)**.

---

## 🧪 Resultados dos Testes Automatizados

### Teste de CSRF (`tests/security_tests/teste_csrf.php`)
- **GET Request**: Status `200 OK` (Tokens gerados) -> `PASS`
- **POST sem Token (Browser)**: Status `400 Bad Request` -> `PASS`
- **POST sem Token (AJAX)**: Status `400 Bad Request` -> `PASS`
- **POST com Token Válido**: Status `200 OK` -> `PASS`

### Teste de Security Headers (`tests/security_tests/teste_security_headers.php`)
- **X-Frame-Options**, **X-Content-Type-Options**, **X-XSS-Protection**, **Referrer-Policy**, **Permissions-Policy**, **CSP** e **HSTS** -> `PASS`

### Teste de Cookies Seguros (`tests/security_tests/teste_secure_cookie.php`)
- **HTTP Normal**: `session_id=...; Path=/; Max-Age=7200; HttpOnly; SameSite=Lax` (Sem flag Secure) -> `PASS`
- **HTTPS Segura**: `session_id=...; Path=/; Max-Age=7200; HttpOnly; SameSite=Lax; Secure` (Com flag Secure) -> `PASS`
- **Logout HTTPS**: `session_id=; Path=/; Expires=Thu, 01 Jan 1970 00:00:00 GMT; Max-Age=0; HttpOnly; SameSite=Lax; Secure` -> `PASS`

