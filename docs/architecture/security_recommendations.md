# 🛡️ Guia Técnico de Recomendações e Diretrizes de Segurança - Alpha Engine

## 📋 Visão Geral

Este documento compila o conjunto de recomendações, especificações técnicas e boas práticas de segurança cibernética para o On-Premise **Alpha Engine** (baseado em Slim 4, Twig, PHP 8+, Redis e MySQL). Servindo como guia de referência técnica, todas as diretrizes listadas neste documento foram integralmente **implementadas, auditadas e validadas por suítes de testes automatizados**.

---

## 📊 Matriz de Priorização e Status de Implementação

| Prioridade | Domínio | Ameaça / Vulnerabilidade | Impacto | Esforço | Status |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **🔴 ALTA** | Aplicação Web | Falsificação de Requisição Entre Sites (CSRF) em formulários `POST` | Alto | Médio | ✅ **IMPLEMENTADO** |
| **🔴 ALTA** | Infraestrutura HTTP | Ausência de Cabeçalhos de Segurança HTTP (Security Headers) | Médio-Alto | Baixo | ✅ **IMPLEMENTADO** |
| **🟡 MÉDIA** | Autenticação | Cookie de sessão sem flag `Secure` sob HTTPS e fixação de sessão | Médio | Baixo | ✅ **IMPLEMENTADO** |
| **🟡 MÉDIA** | Disponibilidade / Brute-Force | Ausência de Rate Limiting por IP para login e APIs públicas | Alto | Médio | ✅ **IMPLEMENTADO** |
| **🟡 MÉDIA** | Divulgação de Dados | Exibição de rastros de erro e `debug = true` habilitado em produção | Médio | Baixo | ✅ **IMPLEMENTADO** |
| **🟢 BAIXA** | Gestão de Arquivos | Execução acidental de scripts PHP no diretório de uploads | Alto | Baixo | ✅ **IMPLEMENTADO** |
| **🟢 BAIXA** | On-Premise Multi-tenant | Risco de acesso cross-tenant por falta de escopo `store_id` | Crítico | Baixo | ✅ **IMPLEMENTADO** |
| **🟢 BAIXA** | Privacidade / LGPD | Vazamento de PII (dados sensíveis) em arquivos de log | Médio | Baixo | ✅ **IMPLEMENTADO** |

---

## 🚀 Especificações Técnicas e Arquitetura Aplicada

### 1. Proteção contra Ataques CSRF (Cross-Site Request Forgery)
- **Status**: ✅ **Concluído e Testado**
- **Implementação**:
  - Dependência `slim/csrf` integrada ao pipeline Slim PSR-15 via [CsrfGuardMiddleware.php](file:///var/www/html/agsonhos/core/Auth/Middleware/CsrfGuardMiddleware.php).
  - Adotada **Inicialização Preguiçosa (*Lazy Initialization*)** para garantir que a sessão PHP esteja ativa antes de instanciar o `Guard`.
  - Tratamento de falhas customizado: Retorna respostas `HTTP 400` estruturadas em JSON para requisições AJAX e página HTML defensiva para formulários convencionais.
  - Injeção automática das meta-tags `<meta name="csrf-*">` nos layouts Twig e no manipulador [form-validator.js](file:///var/www/html/agsonhos/public_html/js/custom/form-validator.js).
  - **Regra Obrigatória para Novos Desenvolvimentos:**
    - **Formulários Twig (`POST`, `PUT`, `DELETE`):** Todo formulário HTML deve conter obrigatoriamente a injeção do bloco de inputs ocultos do CSRF:
      ```twig
      {% if csrf %}
          <input type="hidden" name="{{ csrf.keys.name }}" value="{{ csrf.name }}">
          <input type="hidden" name="{{ csrf.keys.value }}" value="{{ csrf.value }}">
      {% endif %}
      ```
    - **Requisições AJAX / JavaScript:** Desenvolvedores e agentes devem garantir a inclusão das chaves e valores CSRF extraídos das meta-tags `csrf-key-name`, `csrf-key-value`, `csrf-name` e `csrf-value` no payload (`FormData` ou JSON) da requisição.
- **Teste Automatizado**: `tests/security_tests/teste_csrf.php`

---

### 2. Cabeçalhos de Segurança HTTP (Security Headers)
- **Status**: ✅ **Concluído e Testado**
- **Implementação**:
  - Injeção centralizada via [SecurityHeadersMiddleware.php](file:///var/www/html/agsonhos/core/Auth/Middleware/SecurityHeadersMiddleware.php).
  - Adicionados os cabeçalhos: `X-Frame-Options: SAMEORIGIN`, `X-Content-Type-Options: nosniff`, `X-XSS-Protection: 1; mode=block`, `Referrer-Policy: strict-origin-when-cross-origin`, `Permissions-Policy` e `Content-Security-Policy`.
  - Injeção condicional de `Strict-Transport-Security` (HSTS) exclusivamente sob conexões HTTPS (`max-age=31536000; includeSubDomains; preload`).
- **Teste Automatizado**: `tests/security_tests/teste_security_headers.php`

---

### 3. Endurecimento de Sessões e Cookies (Cookie Flags & Rotation)
- **Status**: ✅ **Concluído e Testado**
- **Implementação**:
  - Utilitário centralizado [CookieHelper.php](file:///var/www/html/agsonhos/core/Support/CookieHelper.php) (`Alpha\Support\CookieHelper`) para emissão de cabeçalhos `Set-Cookie`.
  - Anexo condicional de `; Secure` ao identificar tráfego HTTPS (`$request->getUri()->getScheme() === 'https'` ou cabeçalhos `X-Forwarded-Proto`).
  - Atributos padrão `HttpOnly`, `Path=/` e `SameSite=Lax` aplicados em todas as ações de autenticação e logout do catálogo e admin.
- **Teste Automatizado**: `tests/security_tests/teste_secure_cookie.php`

---

### 4. Limitação de Taxa por IP (Rate Limiting com Redis & Fallback em Arquivo)
- **Status**: ✅ **Concluído e Testado**
- **Implementação**:
  - Criado o [RateLimitMiddleware.php](file:///var/www/html/agsonhos/core/Auth/Middleware/RateLimitMiddleware.php).
  - Limita requisições por endereço IP utilizando comandos atômicos do Redis (`INCR` e `EXPIRE`).
  - **Resiliência com Fallback em Arquivo**: Em ambientes sem servidor Redis ativo (ex: desenvolvimento local), o middleware alterna transparentemente para persistência em arquivo (`storage/cache/rate_limit/`), garantindo resiliência sem travar a aplicação.
  - Cota estrita (**10 req/min**) aplicada a formulários de login, cadastro e recuperação de senha.
  - Cota ampla (**60 req/min**) aplicada às rotas de API `/api/*`.
  - Retorna `HTTP 429 Too Many Requests` com cabeçalho `Retry-After` e resposta JSON/HTML.
- **Teste Automatizado**: `tests/security_tests/teste_rate_limit.php`

---

### 5. Hardening do Ambiente de Produção & Vazamento de Informações
- **Status**: ✅ **Concluído e Testado**
- **Implementação**:
  - Leitura dinâmica do ambiente via variáveis do `.env` (`APP_ENV` e `APP_DEBUG`).
  - Desativação do modo de depuração do Twig em produção (`'debug' => false`, `'auto_reload' => false`).
  - Slim `ErrorMiddleware` configurado para ocultar rastros de código e nomes de arquivos aos usuários finais.
  - **Handlers 500 Customizados**: Renderização de telas visuais amigáveis sem dados técnicos em [pages/errors/500.html.twig](file:///var/www/html/agsonhos/resources/views/pages/errors/500.html.twig) e [admin/pages/errors/500.html.twig](file:///var/www/html/agsonhos/resources/views/admin/pages/errors/500.html.twig).
- **Teste Automatizado**: `tests/security_tests/teste_debug_mode.php`

---

### 6. Isolamento Multi-Tenant (`store_id`)
- **Status**: ✅ **Concluído e Testado**
- **Implementação**:
  - Resolução e asserção rigorosa de `store_id` em [AbstractRepository.php](file:///var/www/html/agsonhos/core/Model/Domain/Repositories/AbstractRepository.php).
  - Filtragem estrita de `store_id = :store_id` em buscas e manipulações de clientes ([CustomerRepository.php](file:///var/www/html/agsonhos/core/Model/Domain/Repositories/CustomerRepository.php)) e pedidos ([OrderRepository.php](file:///var/www/html/agsonhos/core/Model/Domain/Repositories/OrderRepository.php)).
  - Bloqueio imediato (retorno `null` / HTTP 404) para tentativas de acesso a dados de outros inquilinos (*cross-tenant data leakage*).
- **Teste Automatizado**: `tests/security_tests/teste_tenant_isolation.php`

---

### 7. Proteção do Diretório de Uploads (`public_html/image` e `storage/`)
- **Status**: ✅ **Concluído e Testado**
- **Implementação**:
  - **[storage/.htaccess](file:///var/www/html/agsonhos/storage/.htaccess)**: Negação total de acesso HTTP direto (`Require all denied` / `Deny from all`) ao diretório de logs e caches.
  - **[public_html/image/.htaccess](file:///var/www/html/agsonhos/public_html/image/.htaccess)**: Bloqueio de execução de scripts PHP/CGI/PHAR (`FilesMatch`) e desativação de listagem de diretórios (`Options -Indexes`).
  - **[UploadSecurityHelper.php](file:///var/www/html/agsonhos/core/Support/UploadSecurityHelper.php)**: Validação do MIME-type real via `finfo_file` (Magic Bytes), sanitização contra *Path Traversal* (`../`) e bloqueio de ataques por dupla extensão (`.php.jpg`).
- **Teste Automatizado**: `tests/security_tests/teste_upload_protection.php`

---

### 8. Conformidade LGPD & Higienização de Logs (Data Privacy)
- **Status**: ✅ **Concluído e Testado**
- **Implementação**:
  - Utilitário [LgpdSanitizer.php](file:///var/www/html/agsonhos/core/Support/LgpdSanitizer.php) (`Alpha\Support\LgpdSanitizer`).
  - Redação automática de senhas, tokens e chaves privadas por `[REDACTED]`.
  - Mascaramento parcial de PII em logs de auditoria:
    - **CPF**: `123.***.***-00`
    - **CNPJ**: `12.***.***/****-99`
    - **E-mails**: `j***@dominio.com`
    - **Cartões de Crédito**: `****-****-****-1234`
  - Integrado ao serviço de auditoria de autenticação [AdminAuthService.php](file:///var/www/html/agsonhos/core/Auth/Services/AdminAuthService.php).
- **Teste Automatizado**: `tests/security_tests/teste_lgpd_sanitizer.php`

---

## 🧪 Suíte de Testes Automatizados de Segurança

Todos os controles de segurança implementados possuem testes unitários/integrados dedicados localizados em `tests/security_tests/`:

```bash
php tests/security_tests/teste_csrf.php
php tests/security_tests/teste_security_headers.php
php tests/security_tests/teste_secure_cookie.php
php tests/security_tests/teste_rate_limit.php
php tests/security_tests/teste_debug_mode.php
php tests/security_tests/teste_tenant_isolation.php
php tests/security_tests/teste_upload_protection.php
php tests/security_tests/teste_lgpd_sanitizer.php
```

---

## ✅ Checklist Definition of Done (DoD) de Segurança

Toda nova funcionalidade ou refatoração desenvolvida na **Alpha Engine** deve satisfazer os seguintes critérios antes do merge:

- [x] Todos os formulários HTML contêm o token anti-CSRF válido.
- [x] Entradas de texto do usuário passam por higienização adequada para evitar XSS e Injection.
- [x] As rotas administrativas mantêm verificação estrita pelo `AdminSessionMiddleware`.
- [x] As consultas ao banco filtram obrigatoriamente por `store_id` em operações multi-tenant.
- [x] Não há senhas, tokens ou chaves de API fixadas em código (*hardcoded*).
- [x] Testes de autenticação verificam o bloqueio após 5 tentativas mal-sucedidas.
- [x] O `session_regenerate_id(true)` é chamado imediatamente após o login bem-sucedido.
- [x] Uploads de arquivos validam o MIME-type real (ex: `image/jpeg`), e não apenas a extensão do arquivo.
- [x] Logs de erro de produção foram verificados para garantir que nenhuma query SQL ou PII (dados sensíveis) está vazando no arquivo de log.
- [x] O mapeamento de dados (Data Mapping) bloqueia propriedades extras para evitar Mass Assignment.
- [x] As respostas das APIs passam por serializadores (ex: Transformers/Resources) que removem dados sensíveis de usuários ou logs internos.
- [x] Rotas de Webhooks de pagamento validam rigorosamente a assinatura digital ou token secreto do gateway (Stripe, Adyen, etc.).
- [x] O comando `composer audit` foi executado com sucesso e não há dependências vulneráveis na aplicação.
- [x] A lógica de cupons e descontos foi testada contra valores negativos ou uso cumulativo indevido.
- [x] Os uploads de arquivos passam por um novo nome gerado aleatoriamente (ex: UUID) para evitar ataques de Path Traversal e colisão de arquivos.
- [x] O painel administrativo utiliza o prefixo de rota mascarado/ofuscado (/LPDHED2dC7Gjrg2b/).
- [x] O controle de acesso ao painel aplica autorização baseada em funções (RBAC) via `UserGroup` (`access` e `modify`).
- [x] A exclusão de contas proíbe a autoexclusão do superuser ativo e bloqueia a remoção de papéis com funcionários vinculados.
- [x] Novos controladores são adicionados ao mapa estático autoritativo do Composer via `composer dump-autoload`.

