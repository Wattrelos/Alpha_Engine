# DP-49: Prioridade Alta (Vulnerabilidades Críticas de Aplicação Web)

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-07-28 21:46:07
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/49

## Descrição

🚨 Prioridade Alta (Vulnerabilidades Críticas de Aplicação Web)
1. Implementação de Proteção CSRF (Cross-Site Request Forgery)
Cenário Atual: As ações POST de autenticação (

LoginAction
), cadastro, edição de conta, adição de endereço e submissão de checkout processam dados de mutação sem validação de um token anti-CSRF.
Recomendação:
Adicionar a dependência oficial [slim/csrf](https://github.com/slimphp/Slim-CSRF) no 

composer.json
.
Registrar o Slim\Csrf\Guard como middleware do Slim.
Injetar os tokens CSRF (csrf_name e csrf_value) nas variáveis globais do Twig para inclusão automática em todos os formulários (<input type="hidden">) e em requisições AJAX (X-CSRF-Token).
2. Adição de Cabeçalhos de Segurança HTTP (Security Headers)
Cenário Atual: O arquivo 

public_html/index.php
 não define cabeçalhos HTTP defensivos na resposta PSR-7.
Recomendação: Criar um SecurityHeadersMiddleware para injetar os seguintes cabeçalhos em todas as respostas:
http
X-Frame-Options: SAMEORIGIN
X-Content-Type-Options: nosniff
X-XSS-Protection: 1; mode=block
Referrer-Policy: strict-origin-when-cross-origin
Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' ...
Strict-Transport-Security: max-age=31536000; includeSubDomains (em HTTPS)
🛡️ Prioridade Média (Hardening de Sessões e Autenticação)
3. Flag Secure e Regeneração de ID de Sessão
Cenário Atual: Em 

LoginAction.php
, o cookie de sessão define HttpOnly e SameSite=Lax, porém a flag ; Secure não é ativada dinamicamente quando a requisição é feita via HTTPS.
Recomendação:
Adicionar a flag ; Secure condicionalmente quando a conexão for segura ($request->getUri()->getScheme() === 'https').
Forçar a regeneração do ID da sessão (session_regenerate_id(true)) no momento da transição de login bem-sucedido para mitigar ataques de Session Fixation.
4. Limitação de Taxa por IP (Rate Limiting com Redis)
Cenário Atual: O 

CustomerRepository
 possui controle de tentativas de login falhas (isLockedOut) filtrando pelo endereço de e-mail. No entanto, não há restrição de requisições por endereço IP.
Recomendação: Implementar um RateLimitMiddleware consumindo a conexão Redis já existente para limitar requisições em endpoints sensíveis:
Exemplo: Máximo de 10 tentativas por minuto por IP nas rotas /login, /cadastro e /recuperar-senha.
Máximo de 60 requisições por minuto por IP nas APIs públicas (/api/*).
5. Desativação do Modo de Depuração em Produção
Cenário Atual: Em 

public_html/index.php
, a opção 'debug' => true está fixa na configuração do Twig e $app->addErrorMiddleware(true, true, true) exibe detalhes de exceções em tela.
Recomendação: Condicionar estas opções à variável de ambiente APP_ENV:
php
$displayErrors = ($_ENV['APP_ENV'] ?? 'production') === 'development';
$errorMiddleware = $app->addErrorMiddleware($displayErrors, true, true);
🏢 Prioridade Arquitetural On-Premise & Multi-Tenant
6. Isolamento Rígido de Tenants (store_id)
Cenário Atual: A aplicação suporta múltiplas lojas/tenants.
Recomendação: Assegurar que todas as chamadas nos Data Mappers e Repositórios incluam a cláusula WHERE store_id = :store_id (ou utilizem um escopo global do repositório) para impedir vazamento ou mutação cruzada de dados entre lojas parceiras do On-Premise.
7. Proteção do Diretório de Uploads (public_html/image e storage/)
Cenário Atual: Arquivos de imagem e uploads de devoluções são armazenados localmente.
Recomendação:
Configurar o servidor web (Nginx ou Apache via .htaccess) para desabilitar a execução de scripts (.php, .phtml, .phar, .sh) dentro da pasta de uploads.
Validar uploads usando checagem real do MIME type (finfo_file) ao invés de confiar apenas na extensão enviada pelo cliente.
Converter nomes de arquivos enviados para hashes/UUIDs antes de salvar para prevenir estouramento de caminho ou Path Traversal.
8. Conformidade LGPD (Anonimização e Sanitização de Logs)
Recomendação:
Garantir que payloads de requisições enviadas para serviços de pagamento ou logs de erro limpem/ofusquem dados sensíveis (número de cartão de crédito, senhas, tokens de autenticação).
Criar um rotina de purga (Cron/Job) para anonimizar ou excluir dados de clientes e sessões inativas após o período de retenção legal.
🔍 Resumo dos Pontos Fortes Já Presentes no Projeto
✅ Password Hashing Seguro: O sistema faz uso do password_hash() com PASSWORD_DEFAULT (Bcrypt/Argon2id) e realiza rehash automático quando necessário (password_needs_rehash).
✅ Prevenção a SQL Injection: Arquitetura fundamentada no uso de DataAccessObject (DAO) e PDO com Prepared Statements parametrizados.
✅ Mitigação Nativa de XSS: Motor de templates Twig configurado com auto-escaping automático.
✅ Controle de Acesso Administrativo por Permissões: O 

AdminSessionMiddleware
 valida privilégios de visualização e alteração por grupo de usuário.


# 🛡️ Guia Técnico de Recomendações e Diretrizes de Segurança - Alpha Engine

## 📋 Visão Geral

Este documento compila o conjunto de recomendações, especificações técnicas e boas práticas de segurança cibernética para o On-Premise **Alpha Engine** (baseado em Slim 4, Twig, PHP 8+, Redis e MySQL). Seu objetivo é servir como guia de referência para futuras implementações, auditorias e refinamentos da arquitetura de segurança da aplicação.

---

## 📊 Matriz de Priorização e Riscos

| Prioridade | Domínio | Ameaça / Vulnerabilidade | Impacto | Esforço |
| :--- | :--- | :--- | :--- | :--- |
| **🔴 ALTA** | Aplicação Web | Falsificação de Requisição Entre Sites (CSRF) em formulários `POST` | Alto | Médio |
| **🔴 ALTA** | Infraestrutura HTTP | Ausência de Cabeçalhos de Segurança HTTP (Security Headers) | Médio-Alto | Baixo |
| **🟡 MÉDIA** | Autenticação | Cookie de sessão sem flag `Secure` sob HTTPS e fixação de sessão | Médio | Baixo |
| **🟡 MÉDIA** | Disponibilidade / Brute-Force | Ausência de Rate Limiting por IP para login e APIs públicas | Alto | Médio |
| **🟡 MÉDIA** | Divulgação de Dados | Exibição de rastros de erro e `debug = true` habilitado em produção | Médio | Baixo |
| **🟢 BAIXA** | Gestão de Arquivos | Execução acidental de scripts PHP no diretório de uploads | Alto | Baixo |
| **🟢 BAIXA** | On-Premise Multi-tenant | Risco de acesso cross-tenant por falta de escopo `store_id` | Crítico | Baixo |

---

## 🚀 Especificações de Implementação por Módulo

### 1. Proteção contra Ataques CSRF (Cross-Site Request Forgery)

#### Contexto e Risco
Atualmente, formulários de mutação (`POST`, `PUT`, `DELETE`), como login, cadastro, checkout, edição de endereço e ações do painel administrativo, processam requisições sem validar tokens anti-CSRF. Um atacante poderia induzir um usuário autenticado a executar ações indesejadas via sites terceiros.

#### Especificação Técnica
1. **Instalação da Dependência**:
   ```bash
   composer require slim/csrf
   ```
2. **Registro do Guard Middleware no Slim**:
   ```php
   use Slim\Csrf\Guard;
   
   $responseFactory = $app->getResponseFactory();
   $csrfMiddleware = new Guard($responseFactory);
   $app->add($csrfMiddleware);
   ```
3. **Injeção de Globais no Twig**:
   Disponibilizar os tokens `csrf_name` e `csrf_value` nos templates Twig:
   ```twig
   <form action="/pt-br/login" method="POST">
       <input type="hidden" name="{{ csrf.keys.name }}" value="{{ csrf.name }}">
       <input type="hidden" name="{{ csrf.keys.value }}" value="{{ csrf.value }}">
       <!-- campos do formulário -->
   </form>
   ```
4. **Requisições AJAX / Fetch API**:
   Adicionar meta tags no layout principal (`base.html.twig`) para leitura via JavaScript (`form-validator.js`):
   ```html
   <meta name="csrf-name" content="{{ csrf.name }}">
   <meta name="csrf-value" content="{{ csrf.value }}">
   ```
   Enviar no cabeçalho HTTP:
   ```javascript
   headers: {
       'X-CSRF-Token-Name': document.querySelector('meta[name="csrf-name"]').content,
       'X-CSRF-Token-Value': document.querySelector('meta[name="csrf-value"]').content
   }
   ```

---

### 2. Cabeçalhos de Segurança HTTP (Security Headers)

#### Contexto e Risco
Sem cabeçalhos HTTP defensivos, navegadores clientes ficam vulneráveis a ataques de **Clickjacking** (carregamento da loja em `<iframe>` malicioso), **MIME Sniffing** e injeção de scripts não autorizados (**XSS**).

#### Especificação Técnica
Criar a classe `core/Auth/Middleware/SecurityHeadersMiddleware.php`:

```php
<?php

declare(strict_types=1);

namespace Alpha\Auth\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Slim\Psr7\Response;

class SecurityHeadersMiddleware
{
    public function __invoke(Request $request, Handler $handler): Response
    {
        $response = $handler->handle($request);

        $isHttps = $request->getUri()->getScheme() === 'https';

        $response = $response
            ->withHeader('X-Frame-Options', 'SAMEORIGIN')
            ->withHeader('X-Content-Type-Options', 'nosniff')
            ->withHeader('X-XSS-Protection', '1; mode=block')
            ->withHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->withHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        if ($isHttps) {
            $response = $response->withHeader(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains; preload'
            );
        }

        return $response;
    }
}
```

---

### 3. Endurecimento de Sessões e Cookies (Cookie Flags & Rotation)

#### Contexto e Risco
Cookies de sessão sem a flag `Secure` sobre HTTPS podem sofrer interceptação (Man-in-the-Middle). Além disso, não regenerar o ID de sessão após a verificação de login expõe a aplicação ao ataque de **Session Fixation**.

#### Especificação Técnica
1. **Ativação Dinâmica da Flag `Secure`**:
   Em `CustomerAuthService`, `AdminAuthService` e `LoginAction`:
   ```php
   $isHttps = ($request->getUri()->getScheme() === 'https') || ($_SERVER['HTTPS'] ?? '') === 'on';

   $cookieValue = sprintf(
       'session_id=%s; Path=/; HttpOnly; SameSite=Lax; Max-Age=7200%s',
       $sessionId,
       $isHttps ? '; Secure' : ''
   );
   ```
2. **Regeneração de ID de Sessão no Login**:
   Sempre que um cliente ou administrador autenticar com sucesso, chamar `session_regenerate_id(true)` (caso utilize `$_SESSION`) ou gerar uma nova chave de hash de 256 bits no Redis, invalidando o identificador pré-autenticação.

---

### 4. Limitação de Taxa por IP (Rate Limiting com Redis)

#### Contexto and Risco
Embora o `CustomerRepository` e `UserRepository` controlem bloqueios de brute-force por e-mail (`isLockedOut`), um atacante pode desferir ataques distribuídos usando múltiplos e-mails ou sobrecarregar endpoints da API (`/api/*`).

#### Especificação Técnica
Criar um middleware de Rate Limiting `core/Auth/Middleware/RateLimitMiddleware.php` utilizando a instância do Redis:

```php
<?php

namespace Alpha\Auth\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Slim\Psr7\Response;
use Predis\Client as RedisClient;

class RateLimitMiddleware
{
    private RedisClient $redis;
    private int $maxRequests;
    private int $decaySeconds;

    public function __construct(RedisClient $redis, int $maxRequests = 60, int $decaySeconds = 60)
    {
        $this->redis = $redis;
        $this->maxRequests = $maxRequests;
        $this->decaySeconds = $decaySeconds;
    }

    public function __invoke(Request $request, Handler $handler): Response
    {
        $ip = $request->getServerParams()['REMOTE_ADDR'] ?? '127.0.0.1';
        $key = 'rate_limit:' . md5($ip . ':' . $request->getUri()->getPath());

        $current = (int)$this->redis->get($key);

        if ($current >= $this->maxRequests) {
            $response = new Response();
            $response->getBody()->write(json_encode([
                'error' => 'Muitas requisições. Por favor, aguarde antes de tentar novamente.'
            ]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withHeader('Retry-After', (string)$this->decaySeconds)
                ->withStatus(429);
        }

        $this->redis->incr($key);
        if ($current === 0) {
            $this->redis->expire($key, $this->decaySeconds);
        }

        return $handler->handle($request);
    }
}
```

---

### 5. Hardening do Ambiente de Produção & Vazamento de Informações

#### Contexto e Risco
Manter `displayErrorDetails => true` ou `debug => true` em produção pode expor trechos de código, credenciais do banco de dados e dados do ambiente em caso de exceções não tratadas.

#### Especificação Técnica
1. **Configuração de Variáveis de Ambiente no `.env`**:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   ```
2. **Ajuste do Bootstrap `public_html/index.php`**:
   ```php
   $isDev = ($_ENV['APP_ENV'] ?? 'production') === 'development';

   // Desativa debug do Twig em produção
   $twig = Twig::create(__DIR__ . '/../resources/views', [
       'cache'       => __DIR__ . '/../storage/cache/twig_slim',
       'auto_reload' => $isDev,
       'debug'       => $isDev,
   ]);

   // Slim Error Middleware
   $errorMiddleware = $app->addErrorMiddleware($isDev, true, true);
   ```

---

### 6. Isolamento Multi-Tenant e Segregação de Uploads

#### Contexto e Risco
1. **Vazamento Cross-Tenant**: Em um sistema On-Premise multi-loja, falhar no filtro de `store_id` pode expor dados de um cliente de uma loja para os administradores de outra.
2. **Execução de Código Distante (RCE) via Upload**: Uploads de arquivos (avatares, imagens de produtos, documentos) não devem permitir a execução de arquivos com extensão de script (`.php`, `.phtml`, `.phar`).

#### Especificação Técnica
1. **Proteção no Servidor Web (`.htaccess` na pasta de uploads `public_html/image/`)**:
   ```apache
   <FilesMatch "\.(php|phtml|phar|sh|pl|py|cgi)$">
       Order Deny,Allow
       Deny from all
   </FilesMatch>
   ```
2. **Validação Rigorosa de Uploads em PHP**:
   - Validar o tipo MIME real com `finfo_file(finfo_open(FILEINFO_MIME_TYPE), $filePath)`.
   - Gerar nomes randômicos únicos (ex: `bin2hex(random_bytes(16)) . '.jpg'`) para impedir **Path Traversal**.
3. **Escopo Obrigatório Multi-Tenant**:
   Todas as consultas SQL nos Mappers/Repositories devem explicitar a cláusula `store_id = :store_id`, obtida do contexto da requisição atual.

---

### 7. LGPD & Higienização de Logs (Data Privacy)

#### Especificação Técnica
1. **Sanitização em Logs**:
   Garantir que payloads gravados em `storage/logs/error.log` passem por um filtro que substitua chaves sensíveis (`password`, `cpf`, `card_number`, `cvv`, `token`) por `***REDACTED***`.
2. **Purga de Dados Sensíveis Expire**:
   Manter um script executado via Cron para limpar registros das tabelas `customer_login` e `session` com mais de 30 dias de inatividade.

---

## ✅ Checklist Definition of Done (DoD) de Segurança

Toda nova funcionalidade ou refatoração desenvolvida na **Alpha Engine** deve satisfazer os seguintes critérios antes do merge:

- [x] Todos os formulários HTML contêm o token anti-CSRF válido.
- [x] Entradas de texto do usuário passam por higienização adequada para evitar XSS e Injection.
- [x] As rotas administrativas mantêm verificação estrita pelo `AdminSessionMiddleware`.
- [x] As consultas ao banco filtram obrigatoriamente por `store_id` em operações multi-tenant.
- [x] Não há senhas, tokens ou chaves de API fixadas em código (*hardcoded*).
- [x] Testes de autenticação verificam o bloqueio após 5 tentativas mal-sucedidas.

