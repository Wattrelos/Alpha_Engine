# Especificações BDD: Segurança, Autenticação, OWASP & Proteções HTTP

Este diretório contém os arquivos de especificação executável em **Gherkin (.feature)** para garantir as defesas cibernéticas da plataforma **Alpha Engine**, estruturados modularmente em arquivos especializados.

---

## 🛡️ Matriz de Especificações de Segurança

| Arquivo Feature | Requisitos & Proteções | Mecanismo Backend / Camada | Contexto Behat |
| :--- | :--- | :--- | :--- |
| [cabecalhos_owasp.feature](/features/security/cabecalhos_owasp.feature) | Cabeçalhos HTTP defensivos (`CSP`, `HSTS`, `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`) em rotas públicas e administrativas | `SecurityHeadersMiddleware` | `SecurityContext` |
| [rate_limiting_brute_force.feature](/features/security/rate_limiting_brute_force.feature) | Bloqueio de IP após tentativas inválidas consecutivas de login (`HTTP 429`) e rate limit com `Retry-After` em rotas sensíveis | `RateLimitMiddleware` | `SecurityContext` |
| [controle_acesso_rbac.feature](/features/security/controle_acesso_rbac.feature) | Redirecionamento de não autenticados (`302`) e bloqueio de privilégios insuficientes (`403 Forbidden`) | `AdminSessionMiddleware` | `SecurityContext` |
| [protecao_csrf.feature](/features/security/protecao_csrf.feature) | Geração de pares de tokens descartáveis e bloqueio de forjamento de requisição no checkout | `CsrfGuardMiddleware` | `CheckoutContext` |
| [prevencao_sqli_xss.feature](/features/security/prevencao_sqli_xss.feature) | Sanitização de XSS em comentários/avaliações e neutralização de SQL Injection com PDO Prepared Statements | `TwigEnvironment` & `ProductRepository` | `SecurityContext` |
| [gestao_sessoes_cookies.feature](/features/security/gestao_sessoes_cookies.feature) | Regeneração de ID de sessão pós-login (Anti-Fixation) e flags seguras de cookies (`HttpOnly`, `Secure`, `SameSite=Lax`) | `SessionManager` & `Redis` | `SecurityContext` |
| [prevencao_idor_acesso.feature](/features/security/prevencao_idor_acesso.feature) | Validação de posse de recurso em pedidos e endereços (`403`/`404`) prevenindo acesso cruzado não autorizado | `OrderRepository` & Policies | `SecurityContext` |

---

## 🚀 Como Executar

```bash
# Execução direta via Composer
composer test:behat:security

# Execução direta via Behat CLI
./vendor/bin/behat features/security/ --no-snippets
```
