# DP-97: Implementação de Auto-Login no Cadastro e Atualização de Testes E2E

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-08-28 20:48:33
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/97

## Descrição

# Implementação de Auto-Login no Cadastro e Atualização de Testes E2E

Implementar o início automático de sessão (Auto-Login) no momento em que um novo cliente se cadastra na loja (seja pela página `/cadastro` ou durante a Etapa 1 do `/checkout`), eliminando a necessidade de login pós-cadastro, adaptando o checkout à nova política de compra que veda pedidos de visitantes anônimos e atualizando os testes E2E e unitários.

## Proposed Changes

### Backend (Alpha Engine Auth & Controller)

#### [MODIFY] [RegisterAction.php](file:///var/www/html/agsonhos/backend/core/Controller/Actions/Customer/Auth/RegisterAction.php)
- Injetar `CustomerAuthService` no construtor via `AppContainer`.
- Após `CustomerRepository->registerCustomer($params)` criar o cliente com sucesso:
  - Recuperar os dados do cliente criado (`id`, `name`, `email`, `telephone`, `customer_group_id`, `role`).
  - Executar `$sessionId = $this->authService->createSession($userData)` para gravar a sessão no Redis/PHP Session.
  - Anexar o header `Set-Cookie` com cookie seguro `session_id` através do `CookieHelper::makeCookieHeader`.
  - Suportar parâmetro `redirect` (ex: `/pt-br/checkout` se veio do checkout, ou rota padrão `account.index`).
  - Retornar JSON estruturado com status HTTP 200, contendo `success: true`, `customer_id` e a URL de redirecionamento.

---

### Frontend (Checkout & Formulários)

#### [MODIFY] [checkout.js](file:///var/www/html/agsonhos/public_html/js/cart/checkout.js)
- Na função assíncrona `validateStepAsync(1)`:
  - Quando a opção for `register`:
    - Coletar dados do formulário `#checkout-register-form`.
    - Executar requisição AJAX POST para `/{lang}/cadastro` com os dados cadastrais e CSRF.
    - Se o backend retornar erros (ex: e-mail já existente, senhas não conferem), exibir alertas nos campos e impedir o avanço.
    - Se o cadastro for concluído com sucesso (status 200), a sessão já está iniciada via cookie. Sincronizar o carrinho local com `/api/carrinho/sincronizar` e preencher os dados cadastrais nos campos de endereço da Etapa 2.
    - Avançar automaticamente e suavemente para a **Etapa 2 (Endereço)** sem recarregar a tela.

---

### Testes Automatizados (E2E & Unitários)

#### [NEW] [RegisterPage.ts](file:///var/www/html/agsonhos/e2e/pages/RegisterPage.ts)
- Criar Page Object para a tela de cadastro (`/{lang}/cadastro`).

#### [NEW] [register.spec.ts](file:///var/www/html/agsonhos/e2e/specs/auth/register.spec.ts)
- Testes E2E para o fluxo de cadastro:
  - Renderização do formulário e validações.
  - Cadastro de novo usuário com verificação de **auto-login imediato** (sessão ativa e redirecionamento para Minha Conta sem passar pela tela de login).
  - Teste de e-mail duplicado.

#### [MODIFY] [test-fixtures.ts](file:///var/www/html/agsonhos/e2e/fixtures/test-fixtures.ts)
- Adicionar a fixture `registerPage` ao `test-fixtures.ts`.

#### [MODIFY] [checkout-flow.spec.ts](file:///var/www/html/agsonhos/e2e/specs/cart/checkout-flow.spec.ts)
- Assegurar que o teste de checkout de ponta a ponta valide o cadastro com auto-login e confirme que o pedido final é gerado com ID de cliente autenticado.

#### [NEW] [CustomerAutoLoginRegisterTest.php](file:///var/www/html/agsonhos/tests/Validation/CustomerAutoLoginRegisterTest.php)
- Teste unitário/integração PHPUnit validando que `RegisterAction` emite cookie de sessão, inicia `$_SESSION['customer_id']` e retorna a resposta de redirecionamento esperada.

## Verification Plan

### Automated Tests
- Executar testes unitários PHPUnit:
  ```bash
  composer test:phpunit
  ```
- Executar os testes E2E do Playwright:
  ```bash
  npx playwright test e2e/specs/auth/register.spec.ts --project=chromium
  npx playwright test e2e/specs/cart/checkout-flow.spec.ts --project=chromium
  ```

### Manual Verification
- Testar visualmente a submissão de cadastro avulso e a submissão de cadastro dentro do checkout na Etapa 1, verificando o avanço direto para a etapa de endereço com o usuário autenticado.

# Walkthrough - Implementação de Auto-Login no Cadastro e Atualização de Testes E2E

Implementamos com sucesso a solução de **Auto-Login no Cadastro de Clientes**, adaptando a loja ao novo cenário onde compras de visitantes anônimos não são mais permitidas e garantindo uma experiência contínua sem quebras de navegação.

## Resumo das Alterações Realizadas

### 1. Backend (Alpha Engine Auth & Controller)
- **[`RegisterAction.php`](file:///var/www/html/agsonhos/backend/core/Controller/Actions/Customer/Auth/RegisterAction.php)**:
  - Injetamos [`CustomerAuthService`](file:///var/www/html/agsonhos/backend/core/Auth/Services/CustomerAuthService.php) na Action via injeção de dependências do [`AppContainer`](file:///var/www/html/agsonhos/backend/Containers/AppContainer.php).
  - Após persistir o cadastro com [`CustomerRepository::registerCustomer`](file:///var/www/html/agsonhos/backend/core/Model/Domain/Repositories/CustomerRepository.php), a sessão é iniciada imediatamente via `$this->authService->createSession($userData)`.
  - O cabeçalho `Set-Cookie` com cookie seguro `session_id` (`HttpOnly`, `SameSite`) é emitido com sucesso.
  - A resposta JSON retorna `success: true`, `customer_id`, `redirect` e os tokens `csrf` atualizados.
- **[`CustomerAuthService.php`](file:///var/www/html/agsonhos/backend/core/Auth/Services/CustomerAuthService.php)**:
  - Atualizado para preservar dados pré-existentes na sessão PHP (tokens CSRF, itens temporários do carrinho) e sincronizar as chaves `email` e `telephone` em `$_SESSION`.
- **[`SubmitCheckoutAction.php`](file:///var/www/html/agsonhos/backend/core/Controller/Actions/Cart/SubmitCheckoutAction.php)**:
  - Robustecida a resolução de `email`, `telephone`, `firstname` e `lastname` a partir das chaves legadas e novas de sessão do cliente autenticado.

### 2. Frontend (Fluxo de Checkout)
- **[`checkout.js`](file:///var/www/html/agsonhos/public_html/js/cart/checkout.js)**:
  - Ao selecionar "Quero me cadastrar" na Etapa 1 do checkout e clicar em "Avançar", o checkout submete os dados de cadastro via AJAX para `/{lang}/cadastro` incluindo `agree: '1'`.
  - Com o retorno de sucesso e a sessão iniciada no cookie/backend, a página atualiza seu estado (`data-logged="true"`), sincroniza os produtos do carrinho local com `/api/carrinho/sincronizar`, atualiza os tokens CSRF e avança suavemente para a **Etapa 2 (Endereço)** sem exigir login ou recarregar a tela.

### 3. Testes Automatizados

#### Testes Unitários e de Integração (PHPUnit)
- **[`CustomerAutoLoginRegisterTest.php`](file:///var/www/html/agsonhos/tests/Validation/CustomerAutoLoginRegisterTest.php)**:
  - `testRegisterActionPerformsAutoLoginAndEmitsSessionCookie`: Valida criação de conta, emissão de cookie `Set-Cookie` e inicialização de `$_SESSION['customer_id']`.
  - `testRegisterActionSupportsCustomRedirect`: Valida suporte a redirecionamento customizado (ex: `/pt-br/checkout`).
  - `testRegisterActionFailsWithValidationErrors`: Valida tratamento de erros sem emissão indevida de sessão.

#### Testes de Ponta a Ponta (Playwright E2E)
- **[`RegisterPage.ts`](file:///var/www/html/agsonhos/e2e/pages/RegisterPage.ts)**: Novo Page Object para `/cadastro`.
- **[`test-fixtures.ts`](file:///var/www/html/agsonhos/e2e/fixtures/test-fixtures.ts)**: Registrada fixture `registerPage`.
- **[`register.spec.ts`](file:///var/www/html/agsonhos/e2e/specs/auth/register.spec.ts)**:
  - Renderização do formulário com tokens CSRF.
  - Cadastro de novo usuário com auto-login e redirecionamento direto para a Minha Conta (`/account`).
  - Validação de erros em tempo real com senhas divergentes.
- **[`checkout-flow.spec.ts`](file:///var/www/html/agsonhos/e2e/specs/cart/checkout-flow.spec.ts)**:
  - Jornada completa: busca ➔ adicionar 2 produtos ao carrinho ➔ cálculo de frete ➔ ir para checkout ➔ cadastro com auto-login na Etapa 1 ➔ preenchimento de endereço na Etapa 2 ➔ seleção de pagamento na Etapa 4 ➔ finalização com sucesso e confirmação do pedido!

---

## Resultados da Validação

```bash
# PHPUnit (101 testes)
composer test:phpunit
# Tests: 101, Assertions: 368 -> OK!

# E2E Módulo Auth (5 testes)
npx playwright test e2e/specs/auth --project=chromium
# 5 passed (19.2s) -> OK!

# E2E Fluxo Completo de Checkout (1 teste)
npx playwright test e2e/specs/cart/checkout-flow.spec.ts --project=chromium
# 1 passed (16.0s) -> OK!

# Behat Checkout (16 cenários)
composer test:behat:checkout
# 16 cenários (16 passaram) -> OK!
```

