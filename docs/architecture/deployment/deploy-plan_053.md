# DP-53: Limitação de Taxa por IP (Rate Limiting com Redis)

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-07-29 00:19:38
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/53

## Descrição

# Plano de Implementação - Limitação de Taxa por IP (Rate Limiting com Redis)

Este plano descreve o projeto e a integração da camada de **Rate Limiting por IP** para a **Alpha Engine**, utilizando a infraestrutura do **Redis** (`predis/predis`). Seu objetivo é proteger endpoints sensíveis contra ataques automatizados de força bruta, estouro de cota e negação de serviço (DoS/DDoS).

## User Review Required

> [!IMPORTANT]
> O middleware de Rate Limiting aplicará cotas diferenciadas:
> - **Rotas Sensíveis / Autenticação** (`/login`, `/cadastro`, `/recuperar-senha`, `/setup`): Limite de **10 requisições por minuto** por IP.
> - **APIs Públicas e Internas** (`/api/*`, `/carrinho/*`): Limite de **60 requisições por minuto** por IP.
> - Requisições que excederem a cota receberão resposta com código **HTTP 429 Too Many Requests** e o cabeçalho `Retry-After`.

> [!NOTE]
> **Resiliência contra Falhas no Redis**: Caso a instância do Redis fique temporariamente inacessível, o middleware capturará a exceção graciosamente e permitirá o fluxo normal da requisição, registrando a falha em log sem derrubar o site.

## Proposed Changes

---

### Middleware & Serviços (Core Auth)

#### [NEW] [RateLimitMiddleware.php](/core/Auth/Middleware/RateLimitMiddleware.php)
- Criar a classe `RateLimitMiddleware` implementando `Psr\Http\Server\MiddlewareInterface`.
- Parâmetros de construtor:
  - `int $maxRequests`: Cota máxima de requisições por janela (ex: 10 ou 60).
  - `int $decaySeconds`: Duração da janela em segundos (padrão: 60s).
  - `string $routeGroup`: Identificador do grupo de rotas para compor a chave no Redis.
- Lógica de Execução:
  1. Extrair o IP real do cliente tratando cabeçalhos `HTTP_X_FORWARDED_FOR`, `HTTP_CLIENT_IP` e `REMOTE_ADDR`.
  2. Gerar a chave de controle no Redis: `rate_limit:{routeGroup}:{md5(IP)}`.
  3. Incrementar o contador via atomic `INCR` no Redis.
  4. Se for o primeiro acesso da janela (`counter === 1`), definir a expiração `EXPIRE key decaySeconds`.
  5. Adicionar os cabeçalhos estatísticos de resposta:
     - `X-RateLimit-Limit: maxRequests`
     - `X-RateLimit-Remaining: max(0, maxRequests - counter)`
  6. Caso a cota seja ultrapassada (`counter > maxRequests`):
     - Calcular o tempo restante com `TTL key`.
     - Para requisições AJAX / JSON: Retornar HTTP 429 com JSON contendo aviso e o tempo de espera.
     - Para navegação via navegador: Retornar HTTP 429 com página visual de aviso e contagem regressiva.

---

### Configuração e Registro no Roteamento

#### [MODIFY] [Routes.php](/Config/Routes.php)
- Instanciar a conexão Redis (reaproveitando a configuração existente no `.env`).
- Aplicar a proteção estrita `RateLimitMiddleware(10, 60, 'auth')` especificamente nos grupos de rotas de Login, Cadastro, Recuperação de Senha e Setup Administrativo.
- Aplicar o `RateLimitMiddleware(60, 60, 'api')` no grupo de rotas de API `/api/*`.

---

## Verification Plan

### Automated Tests
- Criar script de teste automatizado `tests/security_tests/teste_rate_limit.php` enviando requisições sequenciais simuladas para validar:
  1. Contagem incremental nos cabeçalhos `X-RateLimit-Remaining`.
  2. Bloqueio com código HTTP `429 Too Many Requests` no momento exato em que a cota (ex: 10) é ultrapassada.
  3. Retorno do cabeçalho `Retry-After`.
  4. Restabelecimento da cota após expiração da janela.

### Manual Verification
- Testar submissões em sequência via formulário ou script curl e verificar a exibição da mensagem de limitação de taxa.

# Lista de Tarefas - Limitação de Taxa por IP (Rate Limiting com Redis)

- [x] Criar a classe `RateLimitMiddleware.php` em `core/Auth/Middleware/RateLimitMiddleware.php`
- [x] Registrar o `RateLimitMiddleware` em `Config/Routes.php` para rotas de Autenticação (`/login`, `/cadastro`, `/recuperar-senha`) e APIs (`/api/*`)
- [x] Executar `composer dump-autoload` para atualizar o mapa autoritativo de classes
- [x] Criar e executar teste automatizado em `tests/security_tests/teste_rate_limit.php` para validar o bloqueio HTTP 429 e os cabeçalhos de cota
- [x] Atualizar o relatório final de alteração (Walkthrough)

# Walkthrough - Implementação de Segurança (CSRF, Security Headers, Secure Cookies & Rate Limiting)

## 🔒 1. Proteção Anti-CSRF

- **[composer.json](/composer.json)**: Instalada a biblioteca [`slim/csrf`](/vendor/slim/csrf).
- **[CsrfGuardMiddleware.php](/core/Auth/Middleware/CsrfGuardMiddleware.php)**: Middleware PSR-15 com modo persistente, inicialização preguiçosa (*lazy initialization*) e handler de falhas (JSON 400 para AJAX, HTML 400 para navegadores).
- **[public_html/index.php](/public_html/index.php)** e **[public_html/LPDHED2dC7Gjrg2b/index.php](/public_html/LPDHED2dC7Gjrg2b/index.php)**: Registrado o middleware nos bootstraps público e administrativo.
- **[form-validator.js](/public_html/js/custom/form-validator.js)**: Injeção automática dos tokens CSRF em submissões AJAX.

---

## 🛡️ 2. Cabeçalhos de Segurança HTTP (Security Headers)

- **[SecurityHeadersMiddleware.php](/core/Auth/Middleware/SecurityHeadersMiddleware.php)**: Injeta os cabeçalhos defensivos `X-Frame-Options: SAMEORIGIN`, `X-Content-Type-Options: nosniff`, `X-XSS-Protection`, `Referrer-Policy`, `Permissions-Policy`, `Content-Security-Policy` e `HSTS` (sob HTTPS).

---

## 🍪 3. Flag `; Secure` Condicional em Cookies de Sessão

- **[CookieHelper.php](/core/Support/CookieHelper.php)**: Utilitário centralizado para formatar o cabeçalho `Set-Cookie` com `Path=/`, `HttpOnly`, `SameSite=Lax` e anexo condicional de `; Secure` sob conexões HTTPS.
- **Ações de Login & Logout Ajustadas**: `Customer\Auth\LoginAction`, `LogoutAction`, `Admin\Auth\LoginAction` e `LogoutAction`.

---

## ⚡ 4. Limitação de Taxa por IP (Rate Limiting com Redis & Fallback em Arquivo)

- **[RateLimitMiddleware.php](/core/Auth/Middleware/RateLimitMiddleware.php)** (`NEW`):
  - Injeta limitação de taxa por endereço IP (`REMOTE_ADDR` e `X-Forwarded-For`) utilizando a velocidade do Redis (operações atômicas `INCR` e `EXPIRE`).
  - **Fallback Resiliente de Arquivos**: Se o servidor Redis estiver inacessível (ex: ambiente local), alterna automaticamente para persistência em arquivo local (`storage/cache/rate_limit/`), garantindo resiliência total sem interromper a execução do site.
  - Injeta os cabeçalhos estatísticos PSR-7:
    - `X-RateLimit-Limit`: Cota máxima permitida no grupo de rotas.
    - `X-RateLimit-Remaining`: Requisições restantes na janela atual.
    - `Retry-After`: Tempo de espera necessário (em segundos) quando a cota for ultrapassada.
  - Retorna `HTTP 429 Too Many Requests` estruturado em JSON para chamadas AJAX e página HTML amigável para navegadores.
- **[Config/Routes.php](/Config/Routes.php)**:
  - Aplicada cota estrita (10 req/min) para rotas sensíveis: `POST /login` (Admin/Cliente), `POST /cadastro`, `POST /recuperar-senha`.
  - Aplicada cota ampla (60 req/min) para o grupo de rotas de API `/api/*`.

---

## 🧪 Resultados dos Testes Automatizados

### Teste de CSRF (`tests/security_tests/teste_csrf.php`)
- **GET Request**: Status `200 OK` -> `PASS`
- **POST sem Token (Browser)**: Status `400 Bad Request` -> `PASS`
- **POST sem Token (AJAX)**: Status `400 Bad Request` -> `PASS`
- **POST com Token Válido**: Status `200 OK` -> `PASS`

### Teste de Security Headers (`tests/security_tests/teste_security_headers.php`)
- **Headers OWASP** e **HSTS (HTTPS)** -> `PASS`

### Teste de Cookies Seguros (`tests/security_tests/teste_secure_cookie.php`)
- **HTTP**: `session_id=...; HttpOnly; SameSite=Lax` (Sem flag Secure) -> `PASS`
- **HTTPS**: `session_id=...; HttpOnly; SameSite=Lax; Secure` -> `PASS`

### Teste de Rate Limiting (`tests/security_tests/teste_rate_limit.php`)
- **3 Requisições Válidas**: Status `200 OK`, decremento correto de `X-RateLimit-Remaining` -> `PASS`
- **4ª Requisição (Cota Excedida)**: Status `429 Too Many Requests`, cabeçalho `Retry-After: 60s` e JSON de aviso -> `PASS`
- **Isolamento por IP**: IPs diferentes mantêm cotas separadas independentes -> `PASS`

