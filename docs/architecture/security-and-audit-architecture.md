# 🛡️ Arquitetura de Segurança, Auditoria e Detecção de Fraudes — Alpha Engine On-Premise

Este documento especifica a arquitetura técnica de segurança, auditoria imutável, privacidade de dados (LGPD) e detecção de fraudes implementada no **Alpha Engine On-Premise** (baseado em **Slim 4**, **PHP 8.4**, **Twig 3.x**, **Redis**, **RabbitMQ** e **MySQL 8.0**).

---

## 🏗️ Visão Geral da Infraestrutura de Segurança

A **Alpha Engine** opera como uma arquitetura modular desacoplada orientada a DDD, utilizando um pipeline PSR-15 de middlewares HTTP para interceptação preventiva, resiliência contra ataques maliciosos e auditoria assíncrona.

```
+-----------------------------------------------------------------------------------+
|                                  CLIENTE HTTP                                     |
+-----------------------------------------------------------------------------------+
                                          │
                                          ▼
+-----------------------------------------------------------------------------------+
|                   Servidor Web (Nginx / Apache) + .htaccess                       |
|           • Filtro de uploads em public_html/image e bloqueio em storage/         |
+-----------------------------------------------------------------------------------+
                                          │
                                          ▼
+-----------------------------------------------------------------------------------+
|                        Pipeline de Middlewares (Slim 4 PSR-15)                    |
|  1. SecurityHeadersMiddleware (HSTS, CSP, X-Frame-Options, X-Content-Type-Options) |
|  2. RateLimitMiddleware (Redis atomic INCR + Fallback File Cache em storage/)     |
|  3. SignatureMiddleware (Validação HMAC-SHA256 para APIs/Webhooks)                 |
|  4. CsrfGuardMiddleware (Slim Guard com Lazy Init e suporte AJAX/Twig)             |
|  5. AdminSessionMiddleware / SessionMiddleware (Validação de Sessão Redis/PHP)    |
+-----------------------------------------------------------------------------------+
                                          │
                                          ▼
+-----------------------------------------------------------------------------------+
|                       Camada de Aplicação e Negócio (DDD)                        |
|  • Single Action Controllers (Admin / Frontend)                                   |
|  • Isolamento Multi-Tenant estrito (store_id em Repositories)                     |
|  • Validação Transacional de Preços/Estoque via UnitOfWork                        |
+-----------------------------------------------------------------------------------+
                  │                                         │
                  ▼                                         ▼
+-----------------------------------+     +-----------------------------------+
|     Auditoria Assíncrona & Fila   |     |    Sanitização LGPD & Storage     |
|  • QueueService (RabbitMQ)        |     |  • LgpdSanitizer (Redação de PII) |
|  • Workers PHP em Background      |     |  • Audit Logs em storage/logs/    |
+-----------------------------------+     +-----------------------------------+
```

---

## 1. Camada de Identidade, Rede e Fingerprint (Coleta e Inspeção)

Para identificar e validar a "digital" da requisição antes da execução das regras de negócio:

* **Inspeção de IP Real e Proxy Forwarding:**
  * O endereço IP real do cliente é extraído de forma segura em `RateLimitMiddleware`, `SignatureMiddleware` e `AdminAuthService`, respeitando os cabeçalhos `HTTP_X_FORWARDED_FOR` e `HTTP_CLIENT_IP` quando atrás de proxies ou load balancers.
* **Cabeçalhos de Segurança HTTP (`SecurityHeadersMiddleware`):**
  * Injeção global dos cabeçalhos defensivos HTTP em todas as respostas da Alpha Engine:
    * `X-Frame-Options: SAMEORIGIN` (previne Clickjacking).
    * `X-Content-Type-Options: nosniff` (bloqueia MIME-sniffing).
    * `X-XSS-Protection: 1; mode=block` (filtro defensivo para navegadores legados).
    * `Referrer-Policy: strict-origin-when-cross-origin`.
    * `Permissions-Policy`.
    * `Strict-Transport-Security` (HSTS de 1 ano ativado condicionalmente sob conexões HTTPS).
* **Device Fingerprinting & User-Agent:**
  * Suporte ao cabeçalho `X-Device-Fingerprint` no front-end para correlacionar a requisição com o hash de componentes do dispositivo.
  * O `AdminAuthService` captura o `User-Agent` e realiza o registro de audibilidade contextual no log de autenticação.
* **Endurecimento de Sessões e Cookies (`CookieHelper`):**
  * Emissão centralizada de cookies via `Alpha\Support\CookieHelper` com os atributos obrigatórios: `HttpOnly`, `Path=/` e `SameSite=Lax`.
  * Adição dinâmica do atributo `; Secure` ao detectar tráfego HTTPS.
  * Rotação obrigatória do ID de sessão (`session_regenerate_id(true)`) imediatamente após o login bem-sucedido.

---

## 2. Camada de Aplicação PHP (Middlewares & Detecção de Fraudes)

Mecanismos internos interceptadores do pipeline Slim 4 para validação de intenções:

* **Rate Limiting Dinâmico (`RateLimitMiddleware`):**
  * Algoritmo atômico baseado em Redis (`INCR` + `EXPIRE`).
  * **Fallback em Arquivo:** Em ambientes de desenvolvimento ou sem servidor Redis ativo, o middleware alterna automaticamente para o cache de arquivos em `storage/cache/rate_limit/`, garantindo resiliência total.
  * **Cotas Diferenciadas:**
    * Grupo `auth`: **10 requisições/minuto** (para login, cadastro e recuperação de senha).
    * Grupo `api`: **60 requisições/minuto** (para endpoints da API).
  * Retorna HTTP `429 Too Many Requests` com o cabeçalho `Retry-After` e resposta JSON para AJAX ou HTML customizado para navegadores.
* **Proteção Anti-CSRF (`CsrfGuardMiddleware`):**
  * Integração com a biblioteca `slim/csrf` (`Guard`) usando **Inicialização Preguiçosa (*Lazy Initialization*)** para garantir que a sessão PHP esteja iniciada com segurança.
  * Injeção automática das variáveis de token CSRF no motor de templates Twig (`{{ csrf.keys.name }}` e `{{ csrf.name }}`).
  * Suporte nativo para AJAX via cabeçalhos HTTP `X-CSRF-Name` e `X-CSRF-Value` ou campos no payload.
  * Respostas defensivas estruturadas: JSON em HTTP 400 para requisições AJAX e página amigável para formulários HTML.
* **Validação Criptográfica de Requisições (`SignatureMiddleware`):**
  * Proteção para APIs de integração e webhooks críticos via assinatura HMAC-SHA256 no cabeçalho `X-Signature`.
  * Validação em tempo constante via `hash_equals()` para impedir ataques de tempo (*timing attacks*).
  * Contagem de erros por IP persistida no Redis com **bloqueio automático de 15 minutos (900s)** após 5 tentativas de assinatura inválida.
* **Isolamento Multi-Tenant (`store_id`):**
  * Filtragem obrigatória por `store_id` em `AbstractRepository`, `CustomerRepository` e `OrderRepository`, prevenindo vazamento de dados entre diferentes lojas (*cross-tenant data leakage*). A loja padrão é mantida como `store_id = 1` no banco de dados.
* **Validação de Regras de Negócio e Anti-Manipulação de Preço:**
  * Uso de DTOs e POPOs imutáveis na camada de domínio.
  * Processamento transacional de pedidos, cupons e estoque encapsulado na `UnitOfWork` (com PDO `beginTransaction()`, `commit()`, e `rollBack()`), garantindo consistência atômica.

---

## 3. Camada de Auditoria, Privacidade LGPD e Proteção de Storage

Garantia de integridade, conformidade legal e auditoria auditável:

* **Conformidade LGPD & Higienização de Logs (`LgpdSanitizer`):**
  * O utilitário `Alpha\Support\LgpdSanitizer` higieniza mensagens e payloads de requisição antes da gravação em disco.
  * **Omissão Estrita:** Redação automática de campos sensíveis (`password`, `senha`, `token`, `jwt`, `secret`, `cvv`, `card_number`, `api_key`) substituindo seu valor por `[REDACTED]`.
  * **Mascaramento Parcial de PII (Dados Pessoais Identificáveis):**
    * CPF: `123.***.***-00`
    * CNPJ: `12.***.***/****-99`
    * E-mail: `j***@dominio.com`
    * Cartão de Crédito: `****-****-****-3456`
* **Hardening do Diretório de Storage e Uploads:**
  * `storage/.htaccess`: Negação total de acesso direto via web (`Require all denied`).
  * `public_html/image/.htaccess`: Desativação da listagem de diretórios (`Options -Indexes`) e bloqueio de execução de scripts PHP/CGI/PHAR via diretiva `<FilesMatch>`.
  * `UploadSecurityHelper`: Validação do MIME-type real através de Magic Bytes com `finfo_file()`, higienização contra *Path Traversal* (`../`), bloqueio de dupla extensão (ex: `.php.jpg`) e geração de nomes aleatórios UUID para arquivos gravados.
* **Estratégia de Auditoria Multinível & Resiliência (`AuditLoggerService`):**
  * O serviço `Alpha\Services\Audit\AuditLoggerService` gerencia a gravação de auditoria com suporte a hospedagem compartilhada (ex: **Hostinger Premium Web Hosting**):
    * **Nível 1 (Mensageria Asíncrona):** Publicação no RabbitMQ se ativado via `RABBITMQ_ENABLED=true`.
    * **Nível 2 (Fallback Hostinger / MySQL):** Na ausência de mensageria, faz a inserção direta na tabela `tbkk_audit_logs` do MySQL com criação automática da tabela (*Auto-Healing*).
    * **Nível 3 (Fallback Emergencial):** Registra no arquivo local `storage/logs/audit.log` se a conexão com o banco de dados falhar.
  * Todos os eventos gravados passam obrigatoriamente pela sanitização do `LgpdSanitizer` antes da persistência no banco de dados ou arquivo.
* **Mensageria e Filas Assíncronas (`QueueService` / RabbitMQ):**
  * Integração com RabbitMQ via `php-amqplib` para publicação de eventos do sistema (ex: `OrderCreatedEvent`).
  * Filas declaradas como **duráveis** e mensagens com modo de entrega **persistente** (`DELIVERY_MODE_PERSISTENT`). Caso o servidor RabbitMQ esteja inacessível, repassa graciosamente a execução para a camada de fallback MySQL do `AuditLoggerService`.

---


## 4. Camada de Inteligência Comportamental, Métricas e Failsafes

Mecanismos de observabilidade e bloqueio de comportamentos anômalos:

* **Proteção contra Força Bruta em Autenticação:**
  * O `AdminAuthService` utiliza `UserRepository::isLockedOut($username, 5)` para bloquear temporariamente o usuário caso atinja 5 falhas de login dentro da mesma hora.
  * Gravação auditada dos eventos `SUCCESS`, `FAILURE` e `LOCKED_OUT` no log de autenticação `admin_login.log` sanitizado.
* **Proteção contra Crashes de Sessão (Session Failsafe):**
  * O `SessionMapper` inspeciona o tamanho da sessão via `SELECT LENGTH(data)`. Caso o payload exceda 5MB, a linha é truncada para evitar estouro de memória no servidor PHP.
* **Hardening de Ambientes de Produção:**
  * Configuração dinâmica via `.env` (`APP_ENV=production` e `APP_DEBUG=false`).
  * Ocultação de rastros de código e stack traces em produção pelo Slim `ErrorMiddleware`. Renderização de páginas de erro customizadas 500 sem vazamento de detalhes técnicos (`resources/views/pages/errors/500.html.twig`).

---

## 🔄 Fluxo de Execução da Requisição HTTP na Alpha Engine

```
[Cliente / Navegador]
       │
       ▼ (Requisição HTTP / AJAX com X-Signature ou CSRF Token)
[Servidor Web Nginx / Apache] ──► (Aplica Regras .htaccess em /storage e /image)
       │
       ▼
[Slim 4 Pipeline (PSR-15)]
       │
       ├─► 1. SecurityHeadersMiddleware ──► (Injeta HSTS, CSP, X-Frame-Options)
       ├─► 2. RateLimitMiddleware ────────► (Consulta Redis / Fallback em arquivo ──► 429 se exceder cota)
       ├─► 3. SignatureMiddleware ────────► (Valida HMAC-SHA256 ──► Bloqueia 15 min no Redis se violado)
       └─► 4. CsrfGuardMiddleware ─────────► (Valida Token CSRF ──► Injeta variaveis no Twig)
       │
       ▼ [Se aprovado nos Middlewares]
[AdminSessionMiddleware / SessionMiddleware] ──► (Valida Sessão Redis sessao:admin:{id})
       │
       ▼
[Action / Controller (DDD)]
       │
       ├─► Repositories (Filtra obrigatoriamente por store_id)
       ├─► UnitOfWork (Garante atomicidade da transação MySQL)
       │
       ▼ [Pós-Processamento / Auditoria]
[LgpdSanitizer] ──► (Sanitiza PII/Credenciais) ──► Grava em storage/logs/
       │
       └─► QueueService (RabbitMQ) ──► Publica evento persistente para Workers em Background
```
