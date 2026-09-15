# DP-54: Desativação do Modo de Depuração (Debug Mode) em Produção

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-07-29 00:44:10
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/54

## Descrição

# Plano de Implementação - Desativação do Modo de Depuração (Debug Mode) em Produção

Este plano descreve o ajuste na inicialização da **Alpha Engine** para alternar dinamicamente as configurações de exibição de erros, o depurador do Twig e o tratamento global de exceções HTTP 500 com base nas variáveis de ambiente (`APP_ENV` e `APP_DEBUG`).

## User Review Required

> [!IMPORTANT]
> Em ambiente de **Produção** (`APP_ENV=production` ou `APP_DEBUG=false`):
> - Detalhes internos de exceções PHP, nomes de arquivos e trechos de código **NÃO serão exibidos aos usuários** no navegador.
> - Erros e exceções serão gravados silenciosamente em arquivo de log (`storage/logs/error.log`) para auditoria interna.
> - O usuário final visualizará uma tela amigável de erro 500 (*Server Error*).

> [!NOTE]
> Em ambiente de **Desenvolvimento** (`APP_ENV=development` e `APP_DEBUG=true`):
> - O comportamento atual de depuração detalhada (stack traces e relatórios de erro) é mantido para facilitar o desenvolvimento.

## Proposed Changes

---

### Bootstraps da Aplicação (Catálogo & Admin)

#### [MODIFY] [index.php (Public Catalog)](/public_html/index.php)
- Ler `$appEnv` e `$appDebug` do `.env` para determinar se a aplicação está operando em desenvolvimento (`$isDev`).
- Parametrizar a criação do Twig:
  ```php
  'auto_reload' => $isDev,
  'debug'       => $isDev,
  ```
- Configurar o Middleware de Erros do Slim:
  ```php
  $errorMiddleware = $app->addErrorMiddleware($isDev, true, true);
  ```
- Registrar um **Handler Global de Erros 500** customizado para renderizar a view amigável [500.html.twig](/resources/views/pages/errors/500.html.twig) quando `$isDev` for `false`.

#### [MODIFY] [index.php (Admin)](/public_html/LPDHED2dC7Gjrg2b/index.php)
- Aplicar a mesma lógica condicional baseada em `$isDev` na inicialização do painel administrativo.
- Registrar Handler de Erros 500 customizado do backoffice.

---

### Camada de Apresentação (Views de Erro 500)

#### [NEW] [500.html.twig (Public)](/resources/views/pages/errors/500.html.twig)
- Criar a página de erro 500 amigável para o e-commerce público, estendendo o layout base com mensagem decorativa e botão de retorno à home.

#### [NEW] [500.html.twig (Admin)](/resources/views/admin/pages/errors/500.html.twig)
- Criar a página de erro 500 amigável para o painel administrativo.

---

## Verification Plan

### Automated Tests
- Criar o script de teste automatizado `tests/security_tests/teste_debug_mode.php` simulando exceções em ambiente de desenvolvimento vs. produção e validando:
  1. Ocultação de detalhes de arquivo/linha quando `APP_ENV=production`.
  2. Presença do código de status `HTTP 500 Internal Server Error`.
  3. Formatação amigável das telas de erro.

### Manual Verification
- Alterar `APP_ENV` no `.env` para `production` e disparar uma exceção simulada para confirmar a exibição da tela amigável sem vazar detalhes técnicos do servidor.

# Lista de Tarefas - Desativação do Modo de Depuração em Produção

- [x] Criar a view de erro 500 amigável do catálogo público (`resources/views/pages/errors/500.html.twig`)
- [x] Criar a view de erro 500 amigável do painel administrativo (`resources/views/admin/pages/errors/500.html.twig`)
- [x] Atualizar `public_html/index.php` para alternar Twig e ErrorMiddleware dinamicamente com base em `APP_ENV` / `APP_DEBUG` e registrar o Handler Global de Erros 500
- [x] Atualizar `public_html/LPDHED2dC7Gjrg2b/index.php` para alternar a depuração no painel administrativo
- [x] Criar e executar teste automatizado em `tests/security_tests/teste_debug_mode.php` para validar a ocultação de exceções em produção
- [x] Atualizar o relatório final de alteração (Walkthrough)

# Walkthrough - Implementação de Segurança (CSRF, Security Headers, Secure Cookies, Rate Limiting & Hardening de Produção)

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
- **[RateLimitMiddleware.php](/core/Auth/Middleware/RateLimitMiddleware.php)**: Cota estrita (10 req/min) para rotas de autenticação e cota ampla (60 req/min) para APIs `/api/*`, com fallback local em arquivo para desenvolvimento.

---

## 🛠️ 5. Desativação do Modo de Depuração em Produção & Handler 500

- **Parametrização Dinâmica do Ambiente (`$isDev`)**:
  - Tanto em **[public_html/index.php](/public_html/index.php)** quanto no painel administrativo ([public_html/LPDHED2dC7Gjrg2b/index.php](/public_html/LPDHED2dC7Gjrg2b/index.php)), o estado de depuração é lido dinamicamente das variáveis do `.env`:
    ```php
    $appEnv = $_ENV['APP_ENV'] ?? 'production';
    $appDebug = filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN);
    $isDev = ($appEnv === 'development') && $appDebug;
    ```
- **Configuração do Twig & ErrorMiddleware**:
  - Twig `'debug' => $isDev`, `'auto_reload' => $isDev`.
  - Slim `$app->addErrorMiddleware($isDev, true, true)`.
- **Novas Views de Erro 500 Amigáveis**:
  - **[500.html.twig (Público)](/resources/views/pages/errors/500.html.twig)** (`NEW`): Tela visual amigável para o e-commerce.
  - **[500.html.twig (Admin)](/resources/views/admin/pages/errors/500.html.twig)** (`NEW`): Tela visual amigável para o backoffice.
- **Default Error Handler 500**:
  - Quando a aplicação executa em modo de produção (`$isDev = false`), exceções não tratadas são gravadas silenciosamente no log do servidor e o usuário recebe a resposta HTTP 500 limpa (JSON para AJAX ou página 500 renderizada para o navegador), impedindo o vazamento de caminhos de código e detalhes de infraestrutura.

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
- **Modo Desenvolvimento (`$isDev = true`)**: Exibe stack trace e arquivo da exceção para o desenvolvedor -> `PASS`
- **Modo Produção (`$isDev = false`)**: Oculta stack trace e trechos de código, exibindo a página 500 amigável -> `PASS`
- **Erro 500 em AJAX Produção**: Retorna payload JSON 500 limpo `{"error":{"warning":"Ocorreu um erro interno..."}}` -> `PASS`

