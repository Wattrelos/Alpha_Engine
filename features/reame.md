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
| **Módulo Carrinho: Adição & Variantes** | [adicionar_produto.feature](file:///var/www/html/agsonhos/features/cart/adicionar_produto.feature) | `CartContext` | `AddCartAction` / Validação de estoque e variantes |
| **Módulo Carrinho: Gestão de Itens** | [gerenciar_itens_carrinho.feature](file:///var/www/html/agsonhos/features/cart/gerenciar_itens_carrinho.feature) | `CartContext` | `EditCartAction` & `RemoveCartAction` |
| **Módulo Carrinho: Cálculo de Frete** | [calculo_frete_carrinho.feature](file:///var/www/html/agsonhos/features/cart/calculo_frete_carrinho.feature) | `CartContext` | `SaveShippingCepAction` / Correios, Transportadora & BOPIS |
| **Módulo Carrinho: Sincronização** | [sincronizacao_carrinho.feature](file:///var/www/html/agsonhos/features/cart/sincronizacao_carrinho.feature) | `CartContext` | `SyncCartAction` / Mesclagem visitante -> logado |
| **Módulo Checkout: Fluxo do Cliente** | [fluxo_checkout_cliente.feature](file:///var/www/html/agsonhos/features/checkout/fluxo_checkout_cliente.feature) | `CheckoutContext` | `Checkout.php` / Multi-endereços, CSRF & Cupons |
| **Módulo Checkout: Visitante (Guest)** | [checkout_visitante_guest.feature](file:///var/www/html/agsonhos/features/checkout/checkout_visitante_guest.feature) | `CheckoutContext` | `SubmitCheckoutAction` / Validação CPF e dados |
| **Módulo Checkout: Pagamentos & UoW** | [processamento_pagamentos.feature](file:///var/www/html/agsonhos/features/checkout/processamento_pagamentos.feature) | `CheckoutContext` | `ProcessPaymentAction` / PIX, Boleto, Cartão & RabbitMQ |
| **Módulo Checkout: Idempotência** | [idempotencia_checkout.feature](file:///var/www/html/agsonhos/features/checkout/idempotencia_checkout.feature) | `CheckoutContext` | `SubmitCheckoutAction` / Redis Lock anti-duplicidade |
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
│   ├── cart/                            # BDD do Carrinho de Compras
│   │   ├── adicionar_produto.feature
│   │   ├── gerenciar_itens_carrinho.feature
│   │   ├── calculo_frete_carrinho.feature
│   │   └── sincronizacao_carrinho.feature
│   ├── checkout/                        # BDD do Fechamento e Pagamento de Pedidos
│   │   ├── fluxo_checkout_cliente.feature
│   │   ├── checkout_visitante_guest.feature
│   │   ├── processamento_pagamentos.feature
│   │   └── idempotencia_checkout.feature
│   ├── autenticacaoSeguranca.feature
│   ├── checkoutCsrfIntegration.feature
│   ├── indepotence_failure.feature
│   ├── UseCaseDiagramCustomer.feature
│   ├── architectureDiagram.feature
│   ├── reame.md                         # Esta documentação acadêmica
│   └── bootstrap/                       # Implementação dos Step Definitions
│       ├── FeatureContext.php           # Passos gerais de sistema e arquitetura
│       ├── SecurityContext.php          # Passos de segurança, OWASP, rate limit e RBAC
│       ├── CartContext.php              # Passos de catálogo, carrinho, frete e mesclagem
│       └── CheckoutContext.php          # Passos de checkout, CSRF, idempotência e pagamentos
```

---

## 🧠 Arquitetura de Reaproveitamento de Código

Os testes Gherkin **não duplicam a lógica** da aplicação. Em vez disso:
1. **Passos (`Given`, `When`, `Then`)** invocam diretamente os middlewares Slim 4 (ex: `CsrfGuardMiddleware`, `AdminSessionMiddleware`, `SecurityHeadersMiddleware`).
2. **Validações (`Then`)** empregam a biblioteca de asserções estáticas do PHPUnit (`PHPUnit\Framework\Assert::assertEquals`, `Assert::assertTrue`, `Assert::assertContains`), assegurando padronização total com a suíte unitária.
3. **Mocks e Stubs** usam a infraestrutura nativa do PHPUnit através de classes helper (`BehatTestHelper`).
