# Planejamento: Resolução de CSRF em RFQ e Integração do Fluxo "Adicionar ao Orçamento" na PDP (RF033, RF036, RF037)

## Objetivo
Documentar, planejar e validar a correção de segurança CSRF na submissão de orçamentos (RFQ) e a estabilização completa do fluxo *"Adicionar ao Orçamento"* a partir da página de detalhes do produto (PDP), garantindo compatibilidade com a arquitetura Slim 4, integridade das entidades de domínio e validação contínua via E2E (Playwright), BDD (Behat) e PHPUnit.

## Lista de Entregas
1. **Segurança & CSRF em Formulários de Cotação:**
   - Inclusão dos campos ocultos de validação de token CSRF em [`project-rfq-form.twig`](/backend/resources/views/pages/quotation/project-rfq-form.twig) e [`customer-boq-view.twig`](/backend/resources/views/pages/quotation/customer-boq-view.twig).
   - Implementação dos helpers universais de CSRF `appendCsrf` e `getCsrfHeaders` em [`public_html/js/custom/quotation.js`](/public_html/js/custom/quotation.js), garantindo conformidade com o `CsrfGuardMiddleware` para requisições multipart/form-data e AJAX JSON (`X-CSRF-Token`).
2. **Resolução de Erro 500 na Action de Inclusão de Itens (`AddProductToQuoteAction`):**
   - Correção do namespace em [`ProductRepository.php`](/backend/core/Model/Domain/Repositories/ProductRepository.php), importando `QueryBuilder` e `DataAccessObject` para o método `getProductDescription()`.
   - Criação da entidade tipada [`ProductDescription.php`](/backend/core/Model/Domain/Entities/ProductDescription.php) no domínio.
   - Aplicação de verificação defensiva de existência de classe (`class_exists`) no método `hydrate()` do [`DataAccessObject.php`](/backend/core/Model/DataAccessObject/DataAccessObject.php), eliminando riscos de `ReflectionException`.
3. **Persistência de Dados e Compatibilidade de Banco de Dados:**
   - Adicionados valores padrão dinâmicos para `date_added` e `date_modified` nas entidades `ProjectRfq`, `ProjectBoq`, `ProjectBoqItem`, `ProjectBid` e `ServiceProviderProfile` para sanar restrições `NOT NULL` do MySQL.
4. **Resiliência do Frontend na PDP (Modal de Seleção de Obras):**
   - Suporte dinâmico à internacionalização de rotas de API (`/${currentLang}/api/projetos/adicionar-item` com fallback para `/api/projetos/adicionar-item`).
   - Tratamento explícito de estado não autenticado com flag `require_login` e redirecionamento para login.
5. **Automação e Cobertura de Testes:**
   - Playwright E2E: novos cenários validando publicação de RFQ sem rejeição CSRF e adição de insumo ao BoQ a partir da PDP.
   - Behat BDD: 11 cenários de cotação e prestadores executando com 100% de aprovação via `ProviderContext.php` e `FrontendContext.php`.
   - PHPUnit: 120 testes de unidade e integração aprovados.

---

# Walkthrough: Resolução de CSRF e "Adicionar ao Orçamento" na PDP

## 🎯 Resumo da Entrega
O fluxo de cotações de projetos (RFQ) e levantamento quantitativo de materiais (BoQ) recebeu refinamentos críticos de estabilidade e segurança. Os clientes da Alpha Engine agora conseguem publicar solicitações de orçamento sem interrupções por tokens de sessão expirados ou ausentes, e podem navegar pelo catálogo de materiais de construção adicionando qualquer SKU diretamente a uma de suas obras cadastradas, com cálculo automático de custos e sincronização imediata.

---

## 🛠️ Componentes e Mudanças Implementadas

| Componente | Tipo | Descrição da Melhoria |
| :--- | :--- | :--- |
| [`ProductRepository.php`](/backend/core/Model/Domain/Repositories/ProductRepository.php) | Backend / Domínio | Importação de `Alpha\Model\DataAccessObject\QueryBuilder` e `DataAccessObject`, corrigindo o erro 500 no resgate da descrição do produto. |
| [`ProductDescription.php`](/backend/core/Model/Domain/Entities/ProductDescription.php) | Backend / Domínio | Criação da entidade de domínio para mapeamento estruturado de descrições e títulos de produtos por idioma. |
| [`DataAccessObject.php`](/backend/core/Model/DataAccessObject/DataAccessObject.php) | Backend / Infra | Proteção em `hydrate()` com `class_exists()` para evitar exceções de reflexão quando entidades dinâmicas não existirem. |
| [`project-rfq-form.twig`](/backend/resources/views/pages/quotation/project-rfq-form.twig) | View / Frontend | Adição dos inputs ocultos `csrf_key` e `csrf_token` gerados pelo Slim CsrfGuard. |
| [`customer-boq-view.twig`](/backend/resources/views/pages/quotation/customer-boq-view.twig) | View / Frontend | Inclusão de tokens CSRF nas ações de conversão do BoQ em carrinho. |
| [`quotation.js`](/public_html/js/custom/quotation.js) | Frontend / JS | Resiliência de chamadas fetch com CSRF nos headers e corpo, tratamento de erros e suporte a prefixos de linguagem. |
| [`Entities/Quotation/*.php`](/backend/core/Model/Domain/Entities/Quotation/) | Backend / Entidades | Preenchimento automático de datas (`date_added`, `date_modified`) para integridade relacional. |
| [`service-provider.spec.ts`](/e2e/specs/frontend/service-provider.spec.ts) | Testes / E2E | Cobertura Playwright automatizada para criação de RFQ sem CSRF e adição de item via PDP. |

---

## 🧱 Rastreabilidade e Requisitos

- **RF033 (Publicação de RFQ):** Formulário de publicação em `/projetos/novo` com persistência direta e proteção CSRF ativa.
- **RF036 (Takeoff & BoQ):** Criação e vinculação automática de lista quantitativa de materiais quando o projeto não possuir BoQ prévio.
- **RF037 (Add to Quote / Catálogo → BoQ):** Botão "Adicionar ao Orçamento" na PDP permitindo vincular qualquer material a uma obra aberta, recalculando os totais instantaneamente.

---

## 🧪 Resultados dos Testes Automatizados

| Suíte de Testes | Cenários / Asserções | Status |
| :--- | :--- | :--- |
| **Playwright E2E** (`service-provider.spec.ts`) | 6 cenários end-to-end completos (Navegação, Login, Matching, Takeoff, RFQ sem CSRF, Add to Quote PDP) | **6 / 6 Passed (100%)** |
| **Behat BDD** (`test:behat:provider`) | 11 cenários de cotação e prestadores (74 passos) | **11 / 11 Passed (100%)** |
| **Behat BDD Geral** (`test:behat`) | 117 cenários cobrindo todo o sistema (772 passos) | **117 / 117 Passed (100%)** |
| **PHPUnit 11** (`test:phpunit`) | 120 testes de unidade e integração (451 asserções) | **120 / 120 Passed (100%)** |

---

## 📁 Artefatos Relacionados
- 📄 [deploy-plan_105.md](/docs/architecture/deployment/deploy-plan_105.md) — Entrega inicial do Portal do Prestador.
- 📄 [ADR 0009](/docs/architecture/adr/0009-geomatching-haversine-prestador.md) — Decisões de arquitetura para matching e BoQ.
- 📄 [DOD_PORTAL_PRESTADOR.md](/docs/DoD/DOD_PORTAL_PRESTADOR.md) — Definition of Done consolidada do módulo.
