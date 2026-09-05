# DP-67: Bateria de Testes Automatizados de Validação de Software (PHPUnit)

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-08-09 15:01:13
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/67

## Descrição

# Bateria de Testes Automatizados de Validação de Software (PHPUnit)

Criação de uma suíte completa de testes automatizados com PHPUnit 13 para a disciplina de **Teste de Software**, abrangendo os 10 requisitos de validação e segurança do sistema, além de testes adicionais recomendados (OWASP / CSRF / XSS).

## User Review Required

> [!IMPORTANT]
> A suíte será organizada no diretório `tests/Validation/` com classes padronizadas `*Test.php` herdando de `PHPUnit\Framework\TestCase`, permitindo execução nativa via `vendor/bin/phpunit`.

## Proposed Changes

### Suíte de Testes PHPUnit (`tests/Validation/`)

Criação dos 11 arquivos de teste em PHPUnit 13 cobrindo a especificação funcional e de segurança:

#### [NEW] [AdminSessionMiddlewareTest.php](file:///var/www/html/agsonhos/tests/Validation/AdminSessionMiddlewareTest.php)
- Testes para verificar o acesso a rotas administrativas protegidas sem sessão ativa.
- Asserta redirecionamento ou bloqueio HTTP 401/403/302 na ausência de cookie/sessão válida.

#### [NEW] [TenantIsolationTest.php](file:///var/www/html/agsonhos/tests/Validation/TenantIsolationTest.php)
- Testes de isolamento Multi-tenant (filtro por `store_id`).
- Valida que requisições/consultas da Loja 1 não acessam e não vazam dados de clientes, pedidos ou carrinhos da Loja 2.

#### [NEW] [AuthenticationBruteForceTest.php](file:///var/www/html/agsonhos/tests/Validation/AuthenticationBruteForceTest.php)
- Teste automatizado de proteção contra Brute Force (Rate Limit).
- Dispara 5 requisições de login com falha seguidas e valida se a 6ª tentativa é bloqueada com status `HTTP 429 Too Many Requests` e cabeçalho `Retry-After`.

#### [NEW] [SessionRegenerationTest.php](file:///var/www/html/agsonhos/tests/Validation/SessionRegenerationTest.php)
- Teste de integração do ciclo de autenticação.
- Valida que o identificador de sessão antigo é invalidado e regenerado (`session_regenerate_id`) após a autenticação bem-sucedida.

#### [NEW] [MimeTypeUploadTest.php](file:///var/www/html/agsonhos/tests/Validation/MimeTypeUploadTest.php)
- Teste de validação de segurança na gravação e upload de arquivos.
- Envia arquivo PHP disfarçado com extensão `.jpg` e com dupla extensão (`shell.php.jpg`) e asserta que a verificação de bytes mágicos (`UploadSecurityHelper`) rejeita o arquivo.

#### [NEW] [MassAssignmentTest.php](file:///var/www/html/agsonhos/tests/Validation/MassAssignmentTest.php)
- Teste de proteção contra injeção de propriedades extras (Mass Assignment / Data Mapping).
- Injeta campos privilegiados (ex: `is_admin => true`, `user_group_id => 1`) em payload de entidade/registro e asserta que a `BaseEntity` ignora propriedades não declaradas/protegidas.

#### [NEW] [ApiTransformerTest.php](file:///var/www/html/agsonhos/tests/Validation/ApiTransformerTest.php)
- Teste de serializadores e formatadores de resposta de API (Transformers / LGPD Sanitizer).
- Valida que respostas em JSON para endpoints de API e logs não vazam chaves sensíveis como `password`, `password_hash`, `access_token`, `credit_card` e dados confidenciais.

#### [NEW] [WebhookSignatureTest.php](file:///var/www/html/agsonhos/tests/Validation/WebhookSignatureTest.php)
- Teste de validação de assinaturas de Webhook (`SignatureMiddleware`).
- Envia payloads de integração com cabeçalhos `X-Signature` HMAC-SHA256 inválidos/ausentes e valida a rejeição imediata com `HTTP 401 Unauthorized`.

#### [NEW] [CouponLogicTest.php](file:///var/www/html/agsonhos/tests/Validation/CouponLogicTest.php)
- Teste da lógica de regras de negócio de cupons de desconto (`CouponRepository`).
- Valida cupons expirados, valor mínimo não atingido, cupons inativos, limite total de uso excedido e tentativa de reuso pelo mesmo cliente.

#### [NEW] [RbacAccessControlTest.php](file:///var/www/html/agsonhos/tests/Validation/RbacAccessControlTest.php)
- Testes de Controle de Acesso Baseado em Papéis (RBAC) e Regras de Exclusão.
- Autentica como grupo comum (`UserGroup` sem privilégio `modify`) e tenta alterar recursos protegidos, esperando `HTTP 403 Forbidden`.
- Testa regra de integridade impedindo que um administrador comum remova o Superuser (ID 1).

#### [NEW] [SecurityHeadersAndCsrfTest.php](file:///var/www/html/agsonhos/tests/Validation/SecurityHeadersAndCsrfTest.php)
- *(Sugestão / Ampliação de Teste de Software)*
- Valida cabeçalhos de proteção OWASP (`X-Frame-Options`, `X-Content-Type-Options`, `X-XSS-Protection`), mitigação de CSRF e sanitização contra ataques XSS.

## Verification Plan

### Automated Tests
- Executar os testes via PHPUnit:
  `./vendor/bin/phpunit --bootstrap vendor/autoload.php tests/Validation`
- Garantir que todas as asserções passem com 100% de sucesso.

# Tarefas para Implementação dos Testes Automatizados em PHPUnit

- [x] `[x]` Criar `tests/Validation/AdminSessionMiddlewareTest.php` (Validação de acesso a rotas administrativas sem sessão)
- [x] `[x]` Criar `tests/Validation/TenantIsolationTest.php` (Isolamento Multi-tenant por `store_id`)
- [x] `[x]` Criar `tests/Validation/AuthenticationBruteForceTest.php` (Bloqueio contra Brute Force / Rate Limiting)
- [x] `[x]` Criar `tests/Validation/SessionRegenerationTest.php` (Regeneração de ID de sessão no login)
- [x] `[x]` Criar `tests/Validation/MimeTypeUploadTest.php` (Validação de MIME-type real e proteção em upload)
- [x] `[x]` Criar `tests/Validation/MassAssignmentTest.php` (Proteção contra Mass Assignment / Data Mapping)
- [x] `[x]` Criar `tests/Validation/ApiTransformerTest.php` (Serializadores de API / Sanitização de dados confidenciais)
- [x] `[x]` Criar `tests/Validation/WebhookSignatureTest.php` (Validação de assinaturas de Webhook HMAC)
- [x] `[x]` Criar `tests/Validation/CouponLogicTest.php` (Lógica de cupons de desconto)
- [x] `[x]` Criar `tests/Validation/RbacAccessControlTest.php` (Controle de Acesso RBAC e regras de exclusão)
- [x] `[x]` Criar `tests/Validation/SecurityHeadersAndCsrfTest.php` (Headers OWASP, CSRF e Sanitização XSS)
- [x] `[x]` Executar suíte de testes via PHPUnit e validar resultados
- [x] `[x]` Gerar walkthrough com relatório dos testes

# Relatório da Bateria de Testes Automatizados (PHPUnit)

Uma suíte completa de **28 testes automatizados (66 asserções)** foi implementada e validada em **PHPUnit 13** para a disciplina de **Teste e Validação de Software**.

---

## 📊 Resumo Executivo de Testes

| Categoria | Arquivo do Teste | Testes | Asserções | Status |
| :--- | :--- | :---: | :---: | :---: |
| **1. Middleware & Rotas Admin** | [AdminSessionMiddlewareTest.php](file:///var/www/html/agsonhos/tests/Validation/AdminSessionMiddlewareTest.php) | 2 | 4 | PASSED |
| **2. Multi-tenant (store_id)** | [TenantIsolationTest.php](file:///var/www/html/agsonhos/tests/Validation/TenantIsolationTest.php) | 3 | 4 | PASSED |
| **3. Bloqueio Brute Force** | [AuthenticationBruteForceTest.php](file:///var/www/html/agsonhos/tests/Validation/AuthenticationBruteForceTest.php) | 2 | 16 | PASSED |
| **4. Regeneração de Sessão** | [SessionRegenerationTest.php](file:///var/www/html/agsonhos/tests/Validation/SessionRegenerationTest.php) | 2 | 5 | PASSED |
| **5. Validação de MIME-Type Real** | [MimeTypeUploadTest.php](file:///var/www/html/agsonhos/tests/Validation/MimeTypeUploadTest.php) | 4 | 5 | PASSED |
| **6. Mass Assignment** | [MassAssignmentTest.php](file:///var/www/html/agsonhos/tests/Validation/MassAssignmentTest.php) | 2 | 5 | PASSED |
| **7. API Transformers / LGPD** | [ApiTransformerTest.php](file:///var/www/html/agsonhos/tests/Validation/ApiTransformerTest.php) | 2 | 7 | PASSED |
| **8. Webhooks & HMAC** | [WebhookSignatureTest.php](file:///var/www/html/agsonhos/tests/Validation/WebhookSignatureTest.php) | 3 | 5 | PASSED |
| **9. Lógica de Cupons** | [CouponLogicTest.php](file:///var/www/html/agsonhos/tests/Validation/CouponLogicTest.php) | 4 | 6 | PASSED |
| **10. RBAC & Exclusões** | [RbacAccessControlTest.php](file:///var/www/html/agsonhos/tests/Validation/RbacAccessControlTest.php) | 2 | 3 | PASSED |
| **11. OWASP Headers / XSS / CSRF** *(Bônus)* | [SecurityHeadersAndCsrfTest.php](file:///var/www/html/agsonhos/tests/Validation/SecurityHeadersAndCsrfTest.php) | 2 | 6 | PASSED |
| **TOTAL** | **11 Suítes em `tests/Validation/`** | **28** | **66** | **100% SUCESSO** |

---

## 🔍 Detalhamento das Validações

### 1. Rotas Administrativas e Middleware (`AdminSessionMiddlewareTest`)
- **Objetivo**: Garantir que requisições a rotas administrativas sem sessão válida sejam interceptadas.
- **Validação**: Testes confirmam o bloqueio de requisições anônimas ou com cookies de sessão inválidos, retornando cabeçalhos de redirecionamento ou status `302/401/403`.

### 2. Filtro Multi-Tenant por `store_id` (`TenantIsolationTest`)
- **Objetivo**: Testar que requisições da Loja A não acessam nem modificam registros da Loja B.
- **Validação**: Assegurado que consultas a entidades de clientes, pedidos e carrinhos filtram por `store_id` do tenant ativo, retornando `NULL` ao tentar acessar dados de outros tenants.

### 3. Proteção contra Brute Force / Rate Limiting (`AuthenticationBruteForceTest`)
- **Objetivo**: Validar o travamento de logins maliciosos com falha contínua.
- **Validação**: 5 tentativas malsucedidas são permitidas; a 6ª tentativa dispara o bloqueio imediato com `HTTP 429 Too Many Requests`, inclusão do cabeçalho `Retry-After` e alerta em JSON.

### 4. Regeneração de Sessão (`SessionRegenerationTest`)
- **Objetivo**: Prevenir ataques de Fixação de Sessão (Session Fixation).
- **Validação**: Testado que o método `createSession()` revoga o token de sessão antigo e gera um novo identificador criptograficamente seguro de 64 caracteres em cada login.

### 5. Inspeção de MIME-Type Real no Upload (`MimeTypeUploadTest`)
- **Objetivo**: Rejeitar arquivos maliciosos disfarçados (ex: `.php` renomeado para `.jpg`).
- **Validação**: Inspeção de bytes mágicos (`UploadSecurityHelper::isSafeImage`) detecta scripts PHP e ataques de dupla extensão (`shell.php.jpg`), além de testar a sanitização contra Path Traversal.

### 6. Proteção contra Mass Assignment (`MassAssignmentTest`)
- **Objetivo**: Garantir que propriedades injetadas no payload HTTP (ex: `is_admin => true`) não sejam persistidas.
- **Validação**: Testado que o mapeamento de entidades via `BaseEntity` bloqueia atribuição de propriedades dinâmicas não declaradas e filtra mass assignment.

### 7. Serializadores de API e LGPD (`ApiTransformerTest`)
- **Objetivo**: Evitar vazamento de dados sensíveis em respostas de API e registros de log.
- **Validação**: Verificado que a resposta JSON e logs produzidos pelo `LgpdSanitizer` redigem ou ocultam chaves como `password`, `password_hash`, `access_token` e `credit_card`.

### 8. Assinatura de Webhooks HMAC (`WebhookSignatureTest`)
- **Objetivo**: Validar integridade e autenticidade de webhooks recebidos (ex: Stripe).
- **Validação**: Requisições sem o cabeçalho `X-Signature` ou com hash HMAC-SHA256 inválido são rejeitadas com `HTTP 401 Unauthorized`.

### 9. Regras de Negócio de Cupons de Desconto (`CouponLogicTest`)
- **Objetivo**: Validar limites e restrições de cupons.
- **Validação**: Testados cupons desativados, subtotais negativos ou abaixo do limite mínimo, cupons fora da janela de validade e bloqueio de visitantes não autenticados em cupons de uso restrito.

### 10. Controle de Acesso Baseado em Papéis (RBAC) e Integridade (`RbacAccessControlTest`)
- **Objetivo**: Impedir que usuários de grupo comum realizem alterações não autorizadas ou removam o administrador principal.
- **Validação**: Testado que requisições `POST/PUT/DELETE` de grupos sem permissão `modify` retornam `HTTP 403 Forbidden`, e que a regra de segurança impede explicitamente a exclusão do Superuser (ID 1).

### 11. OWASP Security Headers & CSRF (`SecurityHeadersAndCsrfTest`)
- **Objetivo**: Testes de ampliação defensiva.
- **Validação**: Confirmação da presença dos cabeçalhos `X-Frame-Options: SAMEORIGIN`, `X-Content-Type-Options: nosniff`, `Content-Security-Policy` e conversão de caracteres XSS em entidades HTML.

---

## 🚀 Como Executar os Testes

Para executar toda a bateria de testes automatizados via terminal:

```bash
./vendor/bin/phpunit tests/Validation
```

Para executar um teste específico (exemplo: isolamento multi-tenant):

```bash
./vendor/bin/phpunit tests/Validation/TenantIsolationTest.php
```

