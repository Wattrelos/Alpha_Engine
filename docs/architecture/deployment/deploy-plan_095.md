# DP-95: mplementação de Suíte Completa de Testes E2E & Visuais do Fluxo de Compras

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-08-28 15:19:36
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/95

## Descrição

# Plano de Implementação - Suíte Completa de Testes E2E & Visuais do Fluxo de Compras

Este plano transforma as etapas descritas no documento de rascunho [rascunho_teste_fluxo_compras.md](file:///var/www/html/agsonhos/e2e/rascunho_teste_fluxo_compras.md) em uma suíte completa de testes automatizados com **Playwright**, englobando tanto **testes funcionais E2E** quanto **testes de regressão visual (VRT)** com Page Objects dedicados e mocks de rede resilientes (ViaCEP).

---

## 🎯 Escopo e Cobertura do Fluxo

O fluxo de compras cobre o ciclo completo da jornada do cliente:
1. **Busca e Catálogo**: Pesquisa por `"ceramica"`, visualização de resultados e adição de produtos ao carrinho.
2. **Carrinho de Compras**: Visualização de itens, cálculo de frete por CEP (com mock ViaCEP), exibição de opções e avanço ao checkout.
3. **Checkout - Etapa 1 (Identificação/Cadastro)**: Seleção da opção *"Quero me cadastrar"*, preenchimento dos dados cadastrais (nome, e-mail, telefone, senha) e validação.
4. **Checkout - Etapa 2 (Endereço de Entrega/Cobrança)**: Preenchimento do CEP com preenchimento automático de Logradouro, Bairro, Cidade e UF via mock ViaCEP, complemento e avanço.
5. **Checkout - Etapa 4 (Pagamento)**: Seleção do método de pagamento (ex: Pix / Pagar na Entrega) e finalização com proteção contra cliques duplos (Idempotency).
6. **Confirmação do Pedido**: Verificação da página de sucesso (`/checkout/sucesso`), mensagem de confirmação e botão para retorno à página inicial.

---

## 📐 Arquitetura Proposta

```
e2e/
├── fixtures/
│   └── test-fixtures.ts          # [MODIFY] Injeção de CheckoutPage e OrderSuccessPage
├── pages/
│   ├── SearchPage.ts             # [MODIFY] Adiciona método addProductToCart(index)
│   ├── CartPage.ts               # [MODIFY] Adiciona simulador de frete e proceedToCheckout()
│   ├── CheckoutPage.ts           # [NEW] Page Object do checkout multi-etapas
│   └── OrderSuccessPage.ts       # [NEW] Page Object da tela de sucesso
└── specs/
    ├── cart/
    │   └── checkout-flow.spec.ts # [NEW] Teste funcional E2E ponta a ponta do fluxo de compras
    └── visual/
        └── checkout-flow-visual.spec.ts # [NEW] Teste de regressão visual com snapshots de cada etapa
```

---

## 🛠️ Alterações Propostas

### 1. Page Objects (`e2e/pages/`)

#### [MODIFY] [SearchPage.ts](file:///var/www/html/agsonhos/e2e/pages/SearchPage.ts)
- Adicionar helper `addProductToCart(index: number = 0)` para interagir diretamente com o botão de compra rápida no card do produto (`.egen-prod-btn-cart`).

#### [MODIFY] [CartPage.ts](file:///var/www/html/agsonhos/e2e/pages/CartPage.ts)
- Adicionar locators:
  - `shippingCepInput: Locator` (`#shipping-cep`)
  - `shippingCalculateBtn: Locator` (`#btn-calculate-shipping`)
  - `shippingResults: Locator` (`#shipping-results`)
  - `checkoutBtn: Locator` (`#btn-checkout, .cart-btn-checkout`)
- Adicionar métodos:
  - `calculateShipping(cep: string): Promise<void>`
  - `proceedToCheckout(): Promise<void>`

#### [NEW] [CheckoutPage.ts](file:///var/www/html/agsonhos/e2e/pages/CheckoutPage.ts)
- Modelar todas as etapas do checkout multi-step:
  - **Etapa 1 (Identificação)**:
    - Seleção de tipo: `chooseIdentityOption('register' | 'guest' | 'login')`
    - Preenchimento do formulário de cadastro: `fillRegisterForm(data)`
    - Navegação: `nextStep()`
  - **Etapa 2 (Endereço)**:
    - Seleção: `chooseAddressOption('same-address' | 'different-address')`
    - Preenchimento do endereço com CEP: `fillAddress(data)`
    - Autocomplete de CEP e validação de campos preenchidos
    - Navegação: `nextStep()`
  - **Etapa 4 (Pagamento)**:
    - Seleção do método: `selectPaymentMethod('pix' | 'cod' | 'transferencia' | 'link_pagamento')`
    - Observações: `fillNotes(note)`
    - Submissão: `submitOrder()`

#### [NEW] [OrderSuccessPage.ts](file:///var/www/html/agsonhos/e2e/pages/OrderSuccessPage.ts)
- Modelar elementos da tela `/checkout/sucesso`:
  - `successCard: Locator` (`.cart-success-box`)
  - `successTitle: Locator` (`.cart-success-title`)
  - `successDescription: Locator` (`.cart-success-desc`)
  - `returnHomeBtn: Locator` (`a.egen-btn-primary`)
  - Método `clickReturnHome(): Promise<void>`

---

### 2. Fixtures do Playwright (`e2e/fixtures/`)

#### [MODIFY] [test-fixtures.ts](file:///var/www/html/agsonhos/e2e/fixtures/test-fixtures.ts)
- Estender o tipo `CustomFixtures` adicionando `checkoutPage: CheckoutPage` e `orderSuccessPage: OrderSuccessPage`.

---

### 3. Especificações de Testes (`e2e/specs/`)

#### [NEW] [checkout-flow.spec.ts](file:///var/www/html/agsonhos/e2e/specs/cart/checkout-flow.spec.ts)
- Implementar o teste funcional E2E com:
  - Interceptação e mock da API ViaCEP para garantir estabilidade e rapidez.
  - Validação de estados do DOM em cada transição de etapa.
  - Verificação de sucesso na criação do pedido e redirecionamento.

#### [NEW] [checkout-flow-visual.spec.ts](file:///var/www/html/agsonhos/e2e/specs/visual/checkout-flow-visual.spec.ts)
- Implementar testes visuais VRT com `expect(locator/page).toHaveScreenshot()` nos pontos-chave:
  1. `checkout-01-search-results.png`: Listagem de produtos filtrados.
  2. `checkout-02-cart-with-shipping.png`: Carrinho com itens e frete calculado.
  3. `checkout-03-step-register.png`: Checkout Etapa 1 com campos de cadastro preenchidos.
  4. `checkout-04-step-address.png`: Checkout Etapa 2 com campos de endereço autopreenchidos.
  5. `checkout-05-step-payment.png`: Checkout Etapa 4 com opção de pagamento selecionada.
  6. `checkout-06-order-success.png`: Card de pedido realizado com sucesso.

---

### 4. Documentação

#### [MODIFY] [README.md](file:///var/www/html/agsonhos/e2e/README.md)
- Atualizar a árvore de diretórios e adicionar a descrição das novas specs no README do E2E.

---

## 🧪 Plano de Verificação

### 1. Verificação Estática
- Executar `npx tsc --noEmit` para assegurar que todos os tipos TypeScript e Page Objects estão corretos sem erros de compilação.

### 2. Execução dos Testes Funcionais
- Executar `npx playwright test e2e/specs/cart/checkout-flow.spec.ts --project=chromium` e validar o fluxo completo passando sem falhas.

### 3. Geração e Validação dos Snapshots Visuais
- Executar `npx playwright test e2e/specs/visual/checkout-flow-visual.spec.ts --project=chromium --update-snapshots` para gerar os snapshots de referência.
- Re-executar `npm run test:e2e:visual` para validar a comparação visual com 100% de sucesso.

### 4. Regressão Global
- Executar `npm run test:e2e:chromium` para garantir que nenhuma suite anterior foi afetada.

# Walkthrough - Implementação da Suíte Completa de Testes E2E e Visuais do Fluxo de Compras

Transformamos com sucesso o rascunho de fluxo de compras ([rascunho_teste_fluxo_compras.md](file:///var/www/html/agsonhos/e2e/rascunho_teste_fluxo_compras.md)) em uma suíte robusta e completa de testes automatizados com **Playwright** e **TypeScript**, cobrindo tanto testes funcionais ponta a ponta quanto testes de regressão visual (VRT).

---

## 📦 O que foi Implementado

### 1. Page Object Model (POM)
- **[SearchPage.ts](file:///var/www/html/agsonhos/e2e/pages/SearchPage.ts)**: Adicionado o método `addProductToCart(index)` para interação direta com os cards de produto da listagem.
- **[CartPage.ts](file:///var/www/html/agsonhos/e2e/pages/CartPage.ts)**: Adicionados locators e métodos para o simulador de frete (`calculateShipping(cep)`) e avanço para checkout (`proceedToCheckout()`).
- **[CheckoutPage.ts](file:///var/www/html/agsonhos/e2e/pages/CheckoutPage.ts)**: Novo Page Object modelando todas as 4 etapas do checkout multi-step:
  - Etapa 1: Opções de identificação (*"Quero me cadastrar"*), formulário de registro e validação de senhas.
  - Etapa 2: Endereço de cobrança e entrega com autocomplete de CEP (ViaCEP).
  - Etapa 4: Seleção do método de pagamento e finalização com proteção de idempotência.
- **[OrderSuccessPage.ts](file:///var/www/html/agsonhos/e2e/pages/OrderSuccessPage.ts)**: Novo Page Object para a tela de confirmação (`/checkout/sucesso`) e retorno à home.
- **[test-fixtures.ts](file:///var/www/html/agsonhos/e2e/fixtures/test-fixtures.ts)**: Injeção tipada de `checkoutPage` e `orderSuccessPage` nas fixtures do Playwright.

### 2. Suíte de Testes Funcionais E2E
- **[checkout-flow.spec.ts](file:///var/www/html/agsonhos/e2e/specs/cart/checkout-flow.spec.ts)**:
  - Executa a jornada completa do cliente: Home $\rightarrow$ Busca por `"ceramica"` $\rightarrow$ Adição de múltiplos produtos $\rightarrow$ Visualização do Carrinho e cálculo de CEP $\rightarrow$ Cadastro $\rightarrow$ Endereço $\rightarrow$ Pagamento $\rightarrow$ Tela de Sucesso.
  - Mocks de rede do ViaCEP integrados para estabilidade absoluta e rapidez.

### 3. Suíte de Testes de Regressão Visual (VRT)
- **[checkout-flow-visual.spec.ts](file:///var/www/html/agsonhos/e2e/specs/visual/checkout-flow-visual.spec.ts)**:
  - 6 snapshots visuais de alta fidelidade cobrindo os estados críticos do funil:
    1. `checkout-01-search-product-card.png`: Card de produto na pesquisa.
    2. `checkout-02-cart-summary-with-shipping.png`: Resumo do carrinho com frete calculado.
    3. `checkout-03-step-register.png`: Formulário de cadastro preenchido (com máscara no e-mail dinâmico).
    4. `checkout-04-step-billing-address.png`: Formulário de endereço preenchido com autocomplete de CEP.
    5. `checkout-05-step-payment.png`: Método de pagamento selecionado.
    6. `checkout-06-order-success.png`: Card de pedido realizado com sucesso.

---

## 🧪 Resultados dos Testes

### 1. Verificação Estática e TypeScript
```bash
npx tsc --noEmit
# Status: OK (0 erros de tipagem)
```

### 2. Execução da Suíte Visual (VRT)
```bash
npm run test:e2e:visual
# Resultado: 5 passed (28.3s)
```

### 3. Bateria Completa de Testes E2E (Chromium)
```bash
npm run test:e2e:chromium
# Resultado: 20 passed (40.4s)
```

---

## 🚀 Comandos Úteis

| Comando | Descrição |
|---|---|
| `npm run test:e2e:visual` | Executa todos os testes de regressão visual |
| `npm run test:e2e:visual:update` | Atualiza/regenera os snapshots de referência visual |
| `npm run test:e2e:chromium` | Executa todos os 20 testes E2E do projeto |
| `npx playwright test e2e/specs/cart/checkout-flow.spec.ts` | Executa isoladamente o fluxo funcional de compras |

