# DP-51: Implementação de Cabeçalhos de Segurança HTTP (Security Headers)

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-07-28 22:06:40
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/51

## Descrição

# Plano de Implementação - Cabeçalhos de Segurança HTTP (Security Headers)

Este plano descreve a criação e integração do `SecurityHeadersMiddleware` para injetar automaticamente cabeçalhos defensivos HTTP em todas as respostas PSR-7 emitidas pela **Alpha Engine** (Catálogo e Painel Administrativo).

## User Review Required

> [!NOTE]
> Os cabeçalhos de segurança serão configurados de forma resiliente para não quebrar a inclusão de fontes externas já utilizadas na aplicação (como Google Fonts e FontAwesome) nem o carregamento de imagens de produtos.

> [!IMPORTANT]
> O cabeçalho `Strict-Transport-Security` (HSTS) será ativado dinamicamente apenas quando a requisição for realizada sob conexão HTTPS, evitando problemas em ambientes de desenvolvimento local rodando em HTTP limpo.

## Proposed Changes

---

### Middleware & Infraestrutura

#### [NEW] [SecurityHeadersMiddleware.php](file:///var/www/html/agsonhos/core/Auth/Middleware/SecurityHeadersMiddleware.php)
- Criar a classe `SecurityHeadersMiddleware` implementando `Psr\Http\Server\MiddlewareInterface`.
- Injetar na resposta PSR-7 os seguintes cabeçalhos de segurança:
  - `X-Frame-Options: SAMEORIGIN`: Impede o carregamento da loja dentro de `<iframe>` de domínios externos (mitigação contra Clickjacking).
  - `X-Content-Type-Options: nosniff`: Impede que navegadores ignorem o tipo MIME declarado e executem scripts maliciosos.
  - `X-XSS-Protection: 1; mode=block`: Ativa o filtro XSS nativo de navegadores legados.
  - `Referrer-Policy: strict-origin-when-cross-origin`: Oculta dados sensíveis da URL ao navegar para links externos.
  - `Permissions-Policy: camera=(), microphone=(), geolocation=()`: Desabilita permissões desnecessárias do navegador.
  - `Content-Security-Policy`: Define fontes permitidas para scripts, estilos (`Google Fonts`, `FontAwesome`), imagens e conexões.
  - `Strict-Transport-Security`: Adiciona `max-age=31536000; includeSubDomains` se a requisição utilizar HTTPS.

#### [MODIFY] [index.php (Public Catalog)](file:///var/www/html/agsonhos/public_html/index.php)
- Importar e registrar `SecurityHeadersMiddleware` na pilha global do Slim.

#### [MODIFY] [index.php (Admin)](file:///var/www/html/agsonhos/public_html/LPDHED2dC7Gjrg2b/index.php)
- Importar e registrar `SecurityHeadersMiddleware` na pilha global do Slim do painel administrativo.

---

## Verification Plan

### Automated Tests
- Criar script de teste automatizado `tests/security_tests/teste_security_headers.php` que faz chamadas simuladas PSR-7 para rotas do catálogo e admin e verifica se todos os cabeçalhos de segurança requeridos estão presentes com os valores corretos.
- Executar `composer dump-autoload` e testar a resolução das classes.

### Manual Verification
- Inspecionar a resposta no terminal via `curl -I` ou pelo inspetores de rede dos navegadores (DevTools -> Network -> Response Headers).

# Lista de Tarefas - Implementação dos Cabeçalhos de Segurança HTTP

- [x] Criar o middleware `SecurityHeadersMiddleware.php` em `core/Auth/Middleware/SecurityHeadersMiddleware.php`
- [x] Registrar o `SecurityHeadersMiddleware` em `public_html/index.php` (Catálogo) e `public_html/LPDHED2dC7Gjrg2b/index.php` (Admin)
- [x] Executar `composer dump-autoload` para atualizar o mapa autoritativo de classes
- [x] Criar e executar teste automatizado `tests/security_tests/teste_security_headers.php` para validar todos os cabeçalhos de resposta
- [x] Atualizar o relatório final de alteração (Walkthrough)

# Walkthrough - Implementação de Segurança (CSRF & Security Headers)

## 🔒 1. Proteção Anti-CSRF

A proteção contra **Cross-Site Request Forgery (CSRF)** foi implementada no ecossistema **Alpha Engine** (Slim 4 + Twig + Redis).

### Alterações Realizadas
- **[composer.json](file:///var/www/html/agsonhos/composer.json)**: Instalada a biblioteca [`slim/csrf`](file:///var/www/html/agsonhos/vendor/slim/csrf).
- **[CsrfGuardMiddleware.php](file:///var/www/html/agsonhos/core/Auth/Middleware/CsrfGuardMiddleware.php)**: Middleware PSR-15 com modo persistente e handler de falhas (JSON 400 para AJAX, HTML 400 para navegadores).
- **[public_html/index.php](file:///var/www/html/agsonhos/public_html/index.php)** e **[public_html/LPDHED2dC7Gjrg2b/index.php](file:///var/www/html/agsonhos/public_html/LPDHED2dC7Gjrg2b/index.php)**: Registrado o middleware nos bootstraps público e administrativo.
- **[base.html.twig](file:///var/www/html/agsonhos/resources/views/base.html.twig)** e **[admin/layouts/base.html.twig](file:///var/www/html/agsonhos/resources/views/admin/layouts/base.html.twig)**: Adicionadas meta-tags globais de CSRF.
- **[form-validator.js](file:///var/www/html/agsonhos/public_html/js/custom/form-validator.js)**: Injeção automática dos tokens CSRF das meta-tags em submissões AJAX.

---

## 🛡️ 2. Cabeçalhos de Segurança HTTP (Security Headers)

Implementado o middleware de injeção automática de cabeçalhos defensivos segundo os padrões OWASP.

### Alterações Realizadas
- **[SecurityHeadersMiddleware.php](file:///var/www/html/agsonhos/core/Auth/Middleware/SecurityHeadersMiddleware.php)** (`NEW`):
  - Injeta os seguintes cabeçalhos em todas as respostas PSR-7 emitidas pela Alpha Engine:
    - `X-Frame-Options: SAMEORIGIN` (mitigação contra Clickjacking).
    - `X-Content-Type-Options: nosniff` (mitigação contra MIME Sniffing).
    - `X-XSS-Protection: 1; mode=block` (filtro XSS de navegadores legados).
    - `Referrer-Policy: strict-origin-when-cross-origin` (proteção de origem em navegação externa).
    - `Permissions-Policy: camera=(), microphone=(), geolocation=()` (restrição de APIs do navegador).
    - `Content-Security-Policy`: Regras seguras permitindo assets próprios, Google Fonts e FontAwesome.
    - `Strict-Transport-Security: max-age=31536000; includeSubDomains; preload` (HSTS ativado dinamicamente sob conexões HTTPS).
- **Bootstraps**: Registrado o `SecurityHeadersMiddleware` nos pontos de entrada do catálogo e do painel administrativo.

---

## 🧪 Resultados dos Testes Automatizados

### Teste de CSRF (`tests/security_tests/teste_csrf.php`)
- **GET Request**: Status `200 OK` (Tokens gerados no Twig) -> `PASS`
- **POST sem Token (Browser)**: Status `400 Bad Request` (HTML renderizado) -> `PASS`
- **POST sem Token (AJAX)**: Status `400 Bad Request` (JSON warning) -> `PASS`
- **POST com Token Válido**: Status `200 OK` -> `PASS`

### Teste de Security Headers (`tests/security_tests/teste_security_headers.php`)
- **X-Frame-Options**: `SAMEORIGIN` -> `PASS`
- **X-Content-Type-Options**: `nosniff` -> `PASS`
- **X-XSS-Protection**: `1; mode=block` -> `PASS`
- **Referrer-Policy**: `strict-origin-when-cross-origin` -> `PASS`
- **Permissions-Policy**: `camera=(), microphone=(), geolocation=()` -> `PASS`
- **Content-Security-Policy**: Validado com sucesso -> `PASS`
- **HSTS**: Ativado sob HTTPS e desativado em HTTP simples -> `PASS`

