# Documentação & Suíte de Testes BDD com Gherkin (Behat)

## 📌 Contexto Acadêmico
Esta suíte de testes BDD (Behavior-Driven Development) utilizando a sintaxe **Gherkin** e a ferramenta **Behat** foi projetada para atender integralmente aos requisitos da disciplina de **Testes de Software**.

Ela integra-se diretamente com a aplicação **Alpha Engine**, reaproveitando e estendendo a robusta biblioteca de testes unitários e de integração desenvolvidos em PHPUnit (`backend/tests/Validation/`).

---

## 🚀 Como Executar os Testes

### 1. Execução dos Testes BDD (Gherkin / Behat)
```bash
# Execução direta via Behat
./vendor/bin/behat --no-snippets

# Ou utilizando o atalho via Composer
composer test:behat
```

### 2. Execução da Bateria Integrada (Behat + PHPUnit)
```bash
# Executa os testes de validação unitária e de integração em PHPUnit
composer test:phpunit
```

---

## 🗺️ Tabela de Rastreabilidade Acadêmica

| Caso de Uso / Requisito | Arquivo Feature (Gherkin) | Contexto Behat | Validação / Teste Backend Reutilizado |
| :--- | :--- | :--- | :--- |
| **Proteção CSRF no Checkout** | [checkoutCsrfIntegration.feature](file:///var/www/html/agsonhos/features/checkoutCsrfIntegration.feature) | `CheckoutContext` | [CheckoutCsrfIntegrationTest.php](file:///var/www/html/agsonhos/backend/tests/Validation/CheckoutCsrfIntegrationTest.php) |
| **Autenticação & Rate Limiting** | [autenticacaoSeguranca.feature](file:///var/www/html/agsonhos/features/autenticacaoSeguranca.feature) | `SecurityContext` | [AuthenticationBruteForceTest.php](file:///var/www/html/agsonhos/backend/tests/Validation/AuthenticationBruteForceTest.php) |
| **Cabeçalhos OWASP & XSS** | [autenticacaoSeguranca.feature](file:///var/www/html/agsonhos/features/autenticacaoSeguranca.feature) | `SecurityContext` | [SecurityHeadersAndCsrfTest.php](file:///var/www/html/agsonhos/backend/tests/Validation/SecurityHeadersAndCsrfTest.php) |
| **Controle de Acesso RBAC** | [autenticacaoSeguranca.feature](file:///var/www/html/agsonhos/features/autenticacaoSeguranca.feature) | `SecurityContext` | [RbacAccessControlTest.php](file:///var/www/html/agsonhos/backend/tests/Validation/RbacAccessControlTest.php) |
| **Controle de Idempotência** | [indepotence_failure.feature](file:///var/www/html/agsonhos/features/indepotence_failure.feature) | `CheckoutContext` | `IdempotencyService` / Redis Lock |
| **Jornada do Cliente (Carrinho/Checkout)** | [UseCaseDiagramCustomer.feature](file:///var/www/html/agsonhos/features/UseCaseDiagramCustomer.feature) | `CheckoutContext` | [CouponLogicTest.php](file:///var/www/html/agsonhos/backend/tests/Validation/CouponLogicTest.php) |
| **Arquitetura & DDD** | [architectureDiagram.feature](file:///var/www/html/agsonhos/features/architectureDiagram.feature) | `FeatureContext` | Middlewares, AppContainer e UnitOfWork |

---

## ⚙️ Estrutura de Diretórios dos Testes

```
agsonhos/
├── behat.yml                            # Configuração global de suítes e contextos Behat
├── composer.json                        # Scripts de automação ('test:behat', 'test:phpunit')
├── features/                            # Especificações executáveis escritas em Gherkin (.feature)
│   ├── autenticacaoSeguranca.feature
│   ├── checkoutCsrfIntegration.feature
│   ├── indepotence_failure.feature
│   ├── UseCaseDiagramCustomer.feature
│   ├── architectureDiagram.feature
│   ├── reame.md                         # Esta documentação acadêmica
│   └── bootstrap/                       # Implementação dos Step Definitions
│       ├── FeatureContext.php           # Passos gerais de sistema e arquitetura
│       ├── SecurityContext.php          # Passos de segurança, OWASP, rate limit e RBAC
│       └── CheckoutContext.php          # Passos de checkout, CSRF, idempotência e carrinho
```

---

## 🧠 Arquitetura de Reaproveitamento de Código

Os testes Gherkin **não duplicam a lógica** da aplicação. Em vez disso:
1. **Passos (`Given`, `When`, `Then`)** invocam diretamente os middlewares Slim 4 (ex: `CsrfGuardMiddleware`, `AdminSessionMiddleware`, `SecurityHeadersMiddleware`).
2. **Validações (`Then`)** empregam a biblioteca de asserções estáticas do PHPUnit (`PHPUnit\Framework\Assert::assertEquals`, `Assert::assertTrue`, `Assert::assertContains`), assegurando padronização total com a suíte unitária.
3. **Mocks e Stubs** usam a infraestrutura nativa do PHPUnit através de classes helper (`BehatTestHelper`).
