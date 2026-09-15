# DP-93: Implementação da Pirâmide de Testes e a Divisão de Responsabilidades

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-08-27 20:40:49
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/93

## Descrição

_A instalação e configuração do **Playwright** foram concluídas com sucesso. O ambiente foi estruturado com o padrão **Page Object Model (POM)** em **TypeScript** e integrado harmoniosamente à pirâmide de testes do projeto (**PHPUnit** e **Gherkin / Behat**)._

---

### 🏛️ Estrutura da Pirâmide de Testes Completa

| Camada | Ferramenta | Escopo & Responsabilidade |
| :--- | :--- | :--- |
| **Topo (E2E & UI Real)** | **Playwright** | Navegador real com execução de **JavaScript** (AJAX, carrinhos dinâmicos, modais, máscaras), testes de **Acessibilidade (WCAG)** com `@axe-core/playwright`, **Cross-Browser** (Chromium, Firefox, WebKit/Safari) e **Mobile** (Pixel 5, iPhone 12). |
| **Meio (BDD & Regras)** | **Behat / Gherkin** | Especificações executáveis (`.feature`) orientadas a regras de negócio, pipeline de middlewares PSR-15 (Slim), RBAC, Rate Limiting e contratos de API com *Mink BrowserKit*. |
| **Base (Unit & Integração)** | **PHPUnit** | Validação unitária e de integração no backend (`tests/Validation/`, `core/`), Repositórios, Domain Services, Unit of Work e transações ACID. |

---

### 📦 Pacotes Instalados & Configurações Realizadas

1. **Pacotes NPM Adicionados:**
   - [`@axe-core/playwright`](/package.json): Varredura e validação automatizada de acessibilidade (WCAG 2.1 AA).
   - [`dotenv`](/package.json): Injeção automática das variáveis de ambiente (`BASE_URL`, banco e credenciais).
   - [`typescript`](/package.json): Tipagem estática completa para Page Objects e fixtures.

2. **Ajustes no [`playwright.config.ts`](/playwright.config.ts):**
   - Configuração de `baseURL` dinâmica apontando para `http://localhost`.
   - Projetos configurados para **Desktop Chrome**, **Desktop Firefox**, **Desktop Safari (WebKit)**, **Mobile Chrome (Pixel 5)** e **Mobile Safari (iPhone 12)**.
   - Captura automática de **Screenshots** e gravação de **Vídeos** em caso de falhas.
   - Relatórios em **HTML interativo** (`playwright-report/`) e **JSON** (`test-results/`).

3. **Arquitetura Page Object Model (POM):**
   - [`e2e/pages/BasePage.ts`](/e2e/pages/BasePage.ts): Métodos base de navegação com i18n (`/pt-br/`), captura de tokens CSRF, cabeçalhos de segurança e detecção de erros de console JS.
   - [`e2e/pages/HomePage.ts`](/e2e/pages/HomePage.ts): Elementos de topo, logo, busca e navegação.
   - [`e2e/pages/LoginPage.ts`](/e2e/pages/LoginPage.ts): Formulário de autenticação, CSRF e validações.
   - [`e2e/pages/SearchPage.ts`](/e2e/pages/SearchPage.ts): Catálogo de produtos, filtros e ordenação.
   - [`e2e/pages/ProductDetailPage.ts`](/e2e/pages/ProductDetailPage.ts): Detalhe do produto (PDP), variantes, quantidade e compra.
   - [`e2e/pages/CartPage.ts`](/e2e/pages/CartPage.ts): Carrinho de compras, cálculo de frete, cupom e checkout.
   - [`e2e/fixtures/test-fixtures.ts`](/e2e/fixtures/test-fixtures.ts): Fixture que disponibiliza os Page Objects e a engine do Axe em todos os testes.

4. **Especificações E2E Criadas:**
   - [`e2e/specs/frontend/home.spec.ts`](/e2e/specs/frontend/home.spec.ts): Validação de layout da Home, SEO, tokens CSRF e Acessibilidade (Axe WCAG).
   - [`e2e/specs/auth/login.spec.ts`](/e2e/specs/auth/login.spec.ts): Renderização, proteção CSRF e tratamento de credenciais inválidas.
   - [`e2e/specs/frontend/search.spec.ts`](/e2e/specs/frontend/search.spec.ts): Fluxo de busca global e feedback para termos sem resultados.
   - [`e2e/specs/cart/cart-flow.spec.ts`](/e2e/specs/cart/cart-flow.spec.ts): Estado de carrinho vazio e navegação até PDP.
   - [`e2e/specs/security/security-headers.spec.ts`](/e2e/specs/security/security-headers.spec.ts): Cabeçalhos OWASP no browser real (CSP, X-Frame-Options, cookies HttpOnly).
   - [`e2e/specs/responsive/mobile-view.spec.ts`](/e2e/specs/responsive/mobile-view.spec.ts): Comportamento responsivo em dispositivos móveis.

---

### 🚀 Como Executar os Testes

#### 1. Execução E2E direta via NPM
```bash
# Executa todos os testes em todos os browsers e dispositivos móveis (55 testes)
npm run test:e2e

# Execução rápida focada no Chrome (Desktop Chromium)
npm run test:e2e:chromium

# Modo Interativo com interface visual do Playwright
npm run test:e2e:ui

# Abrir relatório HTML detalhado
npm run test:e2e:report
```

#### 2. Execução Integrada via Composer
```bash
# Executa apenas os testes E2E do Playwright
composer test:e2e:chromium

# Executa a suíte completa: PHPUnit + Behat (Gherkin) + Playwright (E2E)
composer test:all
```

---

### 💡 Sugestões de Evolução

1. **Visual Regression Testing**: Utilizar o `expect(page).toHaveScreenshot()` do Playwright para capturar snapshots visuais dos componentes principais (Header, Cards de Produto, Rodapé) e detectar quebras de CSS automaticamente.
2. **Mocking de APIs de Terceiros**: Utilizar `page.route()` do Playwright para interceptar requisições externas (como a consulta ao ViaCEP ou Gateways de Pagamento) e simular cenários de timeout, recusa ou sucesso sem depender de rede externa.
3. **Continuous Integration (CI/CD)**: O arquivo de workflow [`.github/workflows/playwright.yml`](/.github/workflows/playwright.yml) gerado pode executar `composer test:all` em cada Pull Request para garantir 100% de confiabilidade antes de qualquer deploy.

