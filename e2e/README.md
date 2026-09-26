# Suíte de Testes End-to-End (E2E) com Playwright & TypeScript

## 📌 Contexto e Posicionamento na Pirâmide de Testes

Esta suíte de testes E2E com **Playwright** e **TypeScript** complementa a estratégia de qualidade de software do projeto **Alpha Engine**, integrando-se perfeitamente com os testes unitários (**PHPUnit**) e de comportamento BDD (**Gherkin / Behat**):

```
       / \
      / E2E \       <- Playwright (DOM Real, JavaScript, A11y, VRT, Mocks, Mobile)
     /-------\
    /   BDD   \     <- Gherkin / Behat (Regras de Negócio, Middlewares, API & Cenários)
   /-----------\
  /   Unit &    \   <- PHPUnit (Classes, Repositórios, Domain Services, ACID, UoW)
 / Integration   \
/_________________\
```

### 🎯 Recursos Avançados Implementados:

1. **Execução de JavaScript Real**: Validação de scripts dinâmicos de carrinho, formulários, máscaras e AJAX no cliente.
2. **Acessibilidade Automatizada (WCAG 2.1 AA)**: Integração nativa com `@axe-core/playwright` para detecção precoce de problemas de acessibilidade.
3. **Testes de Regressão Visual (VRT)**: Snapshots visuais de componentes com `expect(locator).toHaveScreenshot()` e tolerância a ruídos de renderização.
4. **Mocking & Interceptação de Rede**: Helpers para interceptar serviços externos (como ViaCEP) testando sucesso imediato, CEPs inválidos e resiliência a falhas de conexão.
5. **Cross-Browser & Dispositivos Móveis**: Execução paralela em Chromium, Firefox, WebKit, Mobile Chrome (Pixel 5) e Mobile Safari (iPhone 12).

---

## 🚀 Como Executar os Testes

### 1. Via NPM

```bash
# Executa todos os testes E2E no Chromium (Rápido)
npm run test:e2e:chromium

# Executa apenas os testes de Regressão Visual (VRT)
npm run test:e2e:visual

# Atualiza / Regenera os snapshots de referência visual
npm run test:e2e:visual:update

# Executa apenas os testes de Mock de Rede & Resiliência
npm run test:e2e:network

# Executa todos os testes em todos os navegadores e dispositivos móveis
npm run test:e2e

# Modo Interativo com interface visual rica do Playwright
npm run test:e2e:ui

# Abre o relatório HTML da última execução
npm run test:e2e:report
```

### 2. Via Composer (Integrado)

```bash
# Executa os testes E2E pelo Composer
composer test:e2e:chromium

# Executa a bateria completa do projeto (PHPUnit + Behat + Playwright)
composer test:all
```

---

## 📁 Estrutura de Diretórios

```
e2e/
├── fixtures/
│   └── test-fixtures.ts          # Extensão do test com Page Objects, AxeBuilder e Mocks
├── helpers/
│   └── mock-routes.ts            # Utilitários para interceptação de rotas e APIs externas (ViaCEP)
├── pages/                        # Page Object Model (POM)
│   ├── BasePage.ts               # Métodos base: navegação i18n, CSRF, SEO, console errors
│   ├── HomePage.ts               # Componentes da Home (Header, Logo, Menu, Busca, Footer)
│   ├── LoginPage.ts              # Formulário de login, CSRF e alertas
│   ├── SearchPage.ts             # Listagem de catálogo, filtros e compra rápida
│   ├── ProductDetailPage.ts      # Detalhes do produto (PDP), variantes e compra
│   ├── CartPage.ts               # Carrinho de compras, simulador de frete e checkout
│   ├── CheckoutPage.ts           # Checkout multi-etapas (Identificação, Endereço e Pagamento)
│   └── OrderSuccessPage.ts       # Tela de confirmação e pedido concluído com sucesso
├── specs/                        # Especificações executáveis de testes E2E
│   ├── auth/
│   │   └── login.spec.ts         # Validação de formulário, CSRF e credenciais
│   ├── cart/
│   │   ├── cart-flow.spec.ts     # Estado do carrinho e navegação PDP
│   │   └── checkout-flow.spec.ts # Jornada completa de compras E2E (Busca até Sucesso)
│   ├── frontend/
│   │   ├── home.spec.ts          # Renderização, SEO, integridade JS e Acessibilidade (Axe)
│   │   └── search.spec.ts        # Busca global e termos sem resultados
│   ├── network/
│   │   └── viacep-mock.spec.ts   # Mocks de rede do ViaCEP e cálculo de frete
│   ├── responsive/
│   │   └── mobile-view.spec.ts   # Comportamento responsivo em dispositivos móveis
│   ├── security/
│   │   └── security-headers.spec.ts # Validação de cabeçalhos OWASP e cookies de sessão
│   └── visual/
│       ├── home-visual.spec.ts   # Regressão visual de Header e Rodapé
│       ├── components-visual.spec.ts # Regressão visual de Login e Card de Produto
│       └── checkout-flow-visual.spec.ts # Regressão visual de todo o funil de compras (6 snapshots)
└── README.md                     # Esta documentação
```
