## 🚀 Comandos Úteis (suit de testes)

| Comando | Descrição |
|---|---|
| `npm run test:e2e:visual` | Executa todos os testes de regressão visual |
| `npm run test:e2e:visual:update` | Atualiza/regenera os snapshots de referência visual |
| `npx playwright test e2e/specs/cart/checkout-flow.spec.ts` | Executa isoladamente o fluxo funcional de compras |
| `npx playwright test e2e/specs/network --project=chromium` | Executa isoladamente o fluxo funcional de compras |
| `npx playwright test e2e/specs/visual --project=chromium --update-snapshots` | Executa isoladamente o fluxo funcional de compras |
| `npx playwright test e2e/specs/visual --project=chromium` | Executa isoladamente o fluxo funcional de compras |
| `composer test:behat` | Executa todos os testes de comportamento |
| `composer test:behat:pretty` | Executa todos os testes de comportamento |
| `composer test:behat:architecture` | Executa testes de arquitetura |
| `composer test:behat:security` | Executa testes de segurança | 
| `composer test:behat:use_cases` | Executa testes de caso de uso |
| `composer test:behat:frontend` | Executa testes de front-end |
| `composer test:behat:cart` | Executa testes de carrinho |
| `composer test:behat:checkout` | Executa testes de checkout |
| `composer test:behat:api` | Executa testes de API |
| `composer test:phpunit` | Executa os testes unitários |
| `composer test:e2e` | Executa todos os testes E2E |
| `composer test:e2e:chromium` | Executa todos os 20 testes E2E do projeto |
| `composer test:all` | Executa todos os testes E2E e de unidade |
| `composer test:all:full` | Executa todos os testes E2E e de unidade |
| `composer test:all` | Validação da Pirâmide Completa:  |


Searched for "video"
Viewed playwright.config.ts:1-113
Viewed SearchPage.ts:30-56

O motivo de apenas um cenário ter gerado vídeo no relatório é a configuração atual do Playwright em [playwright.config.ts](file:///var/www/html/agsonhos/playwright.config.ts#L62):

```typescript
// playwright.config.ts
use: {
  video: 'retain-on-failure', // 👈 Só mantém o vídeo se o teste FALHAR
}
```

Como os testes de **Chromium, Firefox, WebKit e Mobile Safari passaram**, o Playwright gravou mas descartou os vídeos deles. Apenas o **Mobile Chrome falhou**, retendo o vídeo no relatório.

---

### Como gerar vídeos para o Firefox (ou todos os testes)

Você tem 3 formas de configurar:

#### 1. Via linha de comando (sem alterar arquivos)
Para rodar especificamente o **Firefox** gravando vídeo mesmo se passar:

```bash
npx playwright test e2e/specs/cart/checkout-flow.spec.ts --project=firefox --video=on
```

Ou para rodar **todos os navegadores** gravando vídeo:
```bash
npx playwright test e2e/specs/cart/checkout-flow.spec.ts --video=on
```

---

#### 2. Configurar gravação de vídeo fixa apenas no Firefox
Se quiser que o projeto Firefox **sempre** gere vídeo no relatório, edite o bloco do Firefox em [playwright.config.ts](file:///var/www/html/agsonhos/playwright.config.ts#L84-L90):

```typescript
{
  name: 'firefox',
  use: { 
    ...devices['Desktop Firefox'],
    viewport: { width: 1440, height: 900 },
    video: 'on', // 👈 Adicione esta linha
  },
},
```

---

#### 3. Configurar globalmente para todos os projetos
Para manter o vídeo de todos os testes (sucesso e falha) em todos os navegadores, altere a linha 62 de [playwright.config.ts](file:///var/www/html/agsonhos/playwright.config.ts#L62):

```typescript
use: {
  /* Gravação de vídeo de todas as execuções */
  video: 'on',
}
```

---

### Opções disponíveis para `video` no Playwright:
| Valor | Comportamento |
|---|---|
| `'on'` | Grava vídeo em **todos os testes** (sucessos e falhas) e anexa no relatório. |
| `'retain-on-failure'` | Grava mas **só mantém e anexa se o teste falhar** (economiza espaço em disco). |
| `'on-first-retry'` | Grava vídeo apenas na retentativa após falha. |
| `'off'` | Desativa gravação de vídeo. |

---

> **Dica sobre a falha no `mobile-chrome`:** 
> O erro ocorreu em [SearchPage.ts:53](file:///var/www/html/agsonhos/e2e/pages/SearchPage.ts#L53) porque em tela mobile o elemento `<aside class="egen-aside-filters">` interceptou o clique no botão do carrinho. Se quiser ajustar, podemos aplicar `await addBtn.click({ force: true })` ou fazer scroll explícito antes do clique.