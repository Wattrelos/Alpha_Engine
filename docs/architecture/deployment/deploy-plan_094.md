# DP-94: Evolução da Suíte E2E Playwright

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-08-27 20:49:05
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/94

## Descrição

# Plano de Implementação: Evolução da Suíte E2E Playwright

Este plano descreve a implementação das 3 frentes de evolução técnica para a suíte de testes E2E do projeto **Alpha Engine (agsonhos)**, elevando a confiabilidade, velocidade e cobertura visual e de integração da aplicação.

---

## 🎯 Objetivos do Projeto

1. **Testes de Regressão Visual (Visual Regression Testing - VRT)**:
   - Implementar testes de snapshot visual automatizados com `toHaveScreenshot()` no Playwright.
   - Proteger componentes essenciais (Header, Logo, Banners, Cards de Produto e Rodapé) contra regressões acidentais de CSS/HTML.
   - Configurar limites de tolerância (`maxDiffPixelRatio` / `threshold`) e mascaramento de elementos dinâmicos (como datas, tokens CSRF e badges mutáveis).

2. **Mocking & Interceptação de Rede (Network Interception & Resiliência)**:
   - Criar utilitários reutilizáveis de mock de rede em `e2e/helpers/mock-routes.ts`.
   - Interceptar chamadas externas ao **ViaCEP** (`https://viacep.com.br/ws/**`) para simular:
     - Sucesso imediato com endereço populado (sem latência de rede externa).
     - CEP não encontrado (`{ "erro": "true" }`).
     - Falha de conexão / Timeout da API externa (500 / Network Error) para validar a resiliência e mensagens de erro do frontend.
   - Interceptar simulações de frete e regras de cálculo dinâmico.

3. **Automação de CI/CD com GitHub Actions**:
   - Modernizar o workflow [`.github/workflows/playwright.yml`](file:///var/www/html/agsonhos/.github/workflows/playwright.yml).
   - Configurar pipeline com passos para:
     - Validação estática / PHPUnit (backend)
     - Validação BDD Gherkin / Behat (regras de negócio)
     - Execução Playwright E2E em modo headless no Chromium
     - Upload automático de relatórios HTML, traces e screenshots em caso de falha.

---

## 🗺️ Arquitetura e Estrutura dos Arquivos

```
.github/workflows/
└── playwright.yml                  # [MODIFY] Pipeline integrada de CI/CD

e2e/
├── fixtures/
│   └── test-fixtures.ts            # [MODIFY] Inclusão de helpers de mock nas fixtures
├── helpers/
│   └── mock-routes.ts              # [NEW] Utilitários para interceptação de rotas e APIs
├── specs/
│   ├── network/
│   │   ├── viacep-mock.spec.ts     # [NEW] Testes de preenchimento e resiliência ViaCEP
│   │   └── shipping-mock.spec.ts   # [NEW] Testes de simulação de frete com mocks
│   └── visual/
│       ├── home-visual.spec.ts     # [NEW] Regressão visual da Home e componentes
│       └── components-visual.spec.ts # [NEW] Regressão visual de Header, Footer e Cards
└── README.md                       # [MODIFY] Atualização com instruções de VRT e Mocks
```

---

## 🛠️ Detalhamento das Alterações Propostas

### 1. Helpers de Interceptação de Rede (`e2e/helpers/mock-routes.ts`)
#### [NEW] [`e2e/helpers/mock-routes.ts`](file:///var/www/html/agsonhos/e2e/helpers/mock-routes.ts)
- Funções utilitárias:
  - `mockViaCepSuccess(page, cep, data)`: Retorna payload padrão (Logradouro, Bairro, Cidade, UF) instantaneamente.
  - `mockViaCepNotFound(page, cep)`: Retorna `{ erro: "true" }`.
  - `mockViaCepFailure(page, status = 500)`: Simula queda de serviço externo ou abort de rede (`route.abort('failed')`).

---

### 2. Especificações de Testes de Rede & Resiliência
#### [NEW] [`e2e/specs/network/viacep-mock.spec.ts`](file:///var/www/html/agsonhos/e2e/specs/network/viacep-mock.spec.ts)
- Testa o simulador de frete / formulários de endereço:
  - Preenchimento automático com CEP válido mockado.
  - Exibição de toast / feedback amigável para CEP inexistente.
  - Tratamento gracioso de erro quando a API externa está offline.

---

### 3. Especificações de Regressão Visual (VRT)
#### [NEW] [`e2e/specs/visual/home-visual.spec.ts`](file:///var/www/html/agsonhos/e2e/specs/visual/home-visual.spec.ts)
- Validação visual com `toHaveScreenshot()`:
  - Snapshot do Header e Barra de Navegação.
  - Snapshot do Rodapé Institucional.
  - Snapshot de Cards de Produtos (`.egen-prod-card`).
  - Mascaramento de elementos dinâmicos (ex: tokens CSRF em meta tags, relógio/contadores).

---

### 4. Scripts e Configurações
#### [MODIFY] [`playwright.config.ts`](file:///var/www/html/agsonhos/playwright.config.ts)
- Configurar diretório de snapshots visuais (`snapshotPathTemplate`).
- Configurar tolerância padrão para testes visuais (`maxDiffPixelRatio: 0.05`).

#### [MODIFY] [`package.json`](file:///var/www/html/agsonhos/package.json)
- Adicionar scripts específicos:
  - `"test:e2e:visual"`: `playwright test e2e/specs/visual`
  - `"test:e2e:visual:update"`: `playwright test e2e/specs/visual --update-snapshots`
  - `"test:e2e:network"`: `playwright test e2e/specs/network`

---

### 5. Pipeline GitHub Actions
#### [MODIFY] [`.github/workflows/playwright.yml`](file:///var/www/html/agsonhos/.github/workflows/playwright.yml)
- Adicionar etapas de build, setup de dependências Node.js, execução de testes E2E e geração de artefatos de CI.

---

## 🔍 Plano de Verificação

### Testes Automatizados
1. **Testes de Rede / Mocking**:
   ```bash
   npx playwright test e2e/specs/network --project=chromium
   ```
2. **Geração e Validação de Snapshots Visuais**:
   ```bash
   npx playwright test e2e/specs/visual --project=chromium --update-snapshots
   npx playwright test e2e/specs/visual --project=chromium
   ```
3. **Execução de Toda a Bateria E2E**:
   ```bash
   npm run test:e2e:chromium
   ```
4. **Validação da Pirâmide Completa**:
   ```bash
   composer test:all
   ```
# Walkthrough: Evoluções Técnicas da Suíte E2E com Playwright

Implementação e validação completa das 3 frentes de evolução técnica para a suíte de testes E2E do projeto **Alpha Engine (agsonhos)**.

---

## 🚀 O que foi Implementado

### 1. Testes de Regressão Visual (Visual Regression Testing - VRT)
- **Snapshots Comparativos**: Implementados testes que validam a consistência de pixels com `expect(locator).toHaveScreenshot()` para:
  - [`e2e/specs/visual/home-visual.spec.ts`](file:///var/www/html/agsonhos/e2e/specs/visual/home-visual.spec.ts): Header/Barra de Navegação e Rodapé Institucional.
  - [`e2e/specs/visual/components-visual.spec.ts`](file:///var/www/html/agsonhos/e2e/specs/visual/components-visual.spec.ts): Formulário de Login e Card de Produto no Catálogo.
- **Configuração de Tolerância**: Em [`playwright.config.ts`](file:///var/www/html/agsonhos/playwright.config.ts), configurado `maxDiffPixelRatio: 0.05` e `animations: 'disabled'`.
- **Scripts NPM**:
  - `npm run test:e2e:visual`: Executa a suíte de regressão visual.
  - `npm run test:e2e:visual:update`: Atualiza os snapshots de referência visual.

### 2. Mocking & Interceptação de Rede (Network Resiliency)
- **Utilitários de Mock**: Criado [`e2e/helpers/mock-routes.ts`](file:///var/www/html/agsonhos/e2e/helpers/mock-routes.ts) com funções para interceptar chamadas ao ViaCEP:
  - `mockViaCepSuccess`: Retorna payload válido imediatamente sem latência de rede externa.
  - `mockViaCepNotFound`: Retorna `{ erro: "true" }`.
  - `mockViaCepFailure`: Simula queda de conexão ou erro de servidor.
- **Testes de Resiliência**: Criado [`e2e/specs/network/viacep-mock.spec.ts`](file:///var/www/html/agsonhos/e2e/specs/network/viacep-mock.spec.ts) validando o cálculo de frete por CEP na página de produto (PDP) para casos de sucesso, CEP inexistente e fallback gracioso em queda de conexão.
- **Script NPM**: `npm run test:e2e:network`.

### 3. Pipeline de Integração Contínua (GitHub Actions)
- **Workflow Otimizado**: [`..github/workflows/playwright.yml`](file:///var/www/html/agsonhos/.github/workflows/playwright.yml) configurado com cache do Node.js, instalação de dependências de sistema do Chromium, execução de testes e upload de relatórios HTML e traces como artefatos de build.

---

## 🧪 Resultados da Validação

### 1. Suíte de Mocks de Rede
```bash
$ npm run test:e2e:network
3 passed (15.6s)
```

### 2. Suíte de Regressão Visual
```bash
$ npm run test:e2e:visual
4 passed (13.8s)
```

### 3. Suíte E2E Playwright Completa (Chromium)
```bash
$ npm run test:e2e:chromium
18 passed (26.2s)
```

### 4. Validação da Pirâmide Completa (`composer test:all`)
```bash
$ composer test:all
- PHPUnit: 93 testes, 339 asserções (100% OK)
- Behat (Gherkin): 104 cenários, 679 passos (100% OK)
- Playwright E2E: 18 testes (100% OK)
```

