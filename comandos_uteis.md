## 🚀 Comandos Úteis (suit de testes)

| Comando | Descrição |
|---|---|
| `npm run test:e2e:visual` | Executa todos os testes de regressão visual |
| `npm run test:e2e:visual:update` | Atualiza/regenera os snapshots de referência visual |
| `npx playwright test e2e/specs/cart/checkout-flow.spec.ts` | Executa isoladamente o fluxo funcional de compras |
| `npx playwright test e2e/specs/network --project=chromium` | Executa isoladamente o fluxo funcional de compras |
| `npx playwright test e2e/specs/visual --project=chromium --update-snapshots` | Executa isoladamente o fluxo funcional de compras |
| `npx playwright test e2e/specs/visual --project=chromium` | Executa isoladamente o fluxo funcional de compras |
| `npm run test:behat` | Executa todos os testes de comportamento |
| `npm run test:behat:pretty` | Executa todos os testes de comportamento |
| `npm run test:behat:architecture` | Executa testes de arquitetura |
| `npm run test:behat:security` | Executa testes de segurança | 
| `npm run test:behat:use_cases` | Executa testes de caso de uso |
| `npm run test:behat:frontend` | Executa testes de front-end |
| `npm run test:behat:cart` | Executa testes de carrinho |
| `npm run test:behat:checkout` | Executa testes de checkout |
| `npm run test:behat:api` | Executa testes de API |
| `npm run test:phpunit` | Executa os testes unitários |
| `npm run test:e2e` | Executa todos os testes E2E |
| `npm run test:e2e:chromium` | Executa todos os 20 testes E2E do projeto |
| `npm run test:all` | Executa todos os testes E2E e de unidade |
| `npm run test:all:full` | Executa todos os testes E2E e de unidade |
| `composer test:all` | Validação da Pirâmide Completa:  |
