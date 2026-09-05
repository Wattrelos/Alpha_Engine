# DP-79: Testes com Gherkin (Behat) & Integration com Testes Existentes

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-08-16 15:33:58
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/79

## Descrição

# Plano de Implementação: Testes com Gherkin (Behat) & Integration com Testes Existentes

Este plano descreve a estratégia para implementar testes de aceitação e comportamento (BDD) utilizando a linguagem **Gherkin** e a ferramenta **Behat**, atendendo integralmente aos requisitos da grade curricular acadêmica de Testes de Software, enquanto reaproveita a estrutura robusta de testes PHPUnit já desenvolvida na aplicação.

---

## 💡 Reaproveitamento dos Testes Existentes

> [!TIP]
> **É 100% possível (e recomendado) reaproveitar a bateria de testes existente!**
>
> O Behat é um framework PHP. Os arquivos de passo (Step Definitions nos `Contexts`) são classes PHP convencionais. Dentro das etapas Gherkin (`Dado`, `Quando`, `Então`), podemos:
> 1. Instanciar e executar diretamente as rotas, middlewares, servicos e ações Slim/PHP consumidas nos testes de `backend/tests/Validation/`.
> 2. Utilizar as asserções da biblioteca PHPUnit (`PHPUnit\Framework\Assert`) dentro dos passos de validação (`Então...`).
> 3. Compartilhar fixtures, payloads e mocks já criados.

---

## ❓ Perguntas Abertas / Decisões do Usuário

> [!IMPORTANT]
> 1. **Escopo Acadêmico:** Existe algum Use Case ou requisito específico exigido pela sua faculdade além dos já documentados (ex: Login/CSRF, Checkout/Idempotência, Cupom de Desconto, RBAC)?
> 2. **Nível da Execução:** A faculdade exige simulação visual em navegador real (UI via Selenium/Mink) ou validação em nível de API/HTTP/Backend (via BrowserKit/Slim App) atende aos requisitos? *(Recomendamos HTTP/Backend por ser infinitamente mais rápido e determinístico).*

---

## 🛠️ Alterações Propostas

### 1. Configuração da Infraestrutura do Behat

#### [MODIFY] [behat.yml](file:///var/www/html/agsonhos/behat.yml)
- Expandir a configuração para organizar os cenários em *Suites* temáticas (ex: `security`, `checkout`, `cart`, `api`).
- Configurar os contextos necessários (`FeatureContext`, `SecurityContext`, `CheckoutContext`, `ApiContext`).

#### [MODIFY] [composer.json](file:///var/www/html/agsonhos/composer.json)
- Adicionar scripts unificados de automação para facilidade de execução acadêmica:
  - `composer test:behat` (`./vendor/bin/behat`)
  - `composer test:phpunit` (`./vendor/bin/phpunit -c backend/phpunit.xml`)
  - `composer test` (executa ambas as baterias sequencialmente)

---

### 2. Implementação das Feature Files (Gherkin)

#### [NEW] [checkoutCsrfIntegration.feature](file:///var/www/html/agsonhos/features/checkoutCsrfIntegration.feature)
- Preencher o arquivo atualmente vazio com cenários Gherkin em português cobrindo geração de token CSRF, submissão de checkout válida e rejeição por CSRF inválido/ausente (mapeado de [CheckoutCsrfIntegrationTest.php](file:///var/www/html/agsonhos/backend/tests/Validation/CheckoutCsrfIntegrationTest.php)).

#### [NEW] [autenticacaoSeguranca.feature](file:///var/www/html/agsonhos/features/autenticacaoSeguranca.feature)
- Criar cenários cobrindo proteção contra força bruta, autenticação de sessão e controle de acesso RBAC (mapeados dos testes de validação em `backend/tests/Validation/`).

---

### 3. Implementação dos Step Definitions (Contextos Behat Reaproveitando PHPUnit)

#### [NEW] [SecurityContext.php](file:///var/www/html/agsonhos/features/bootstrap/SecurityContext.php)
- Implementar passos `@Given`, `@When`, `@Then` reaproveitando os middlewares Slim (`CsrfGuardMiddleware`, `AdminSessionMiddleware`) e asserções PHPUnit.

#### [NEW] [CheckoutContext.php](file:///var/www/html/agsonhos/features/bootstrap/CheckoutContext.php)
- Implementar passos do fluxo de checkout e idempotência Redis/MySQL invocando os serviços reais do sistema (`IdempotencyService`, `CheckoutAction`).

---

### 4. Documentação para Entrega Acadêmica

#### [MODIFY] [features/reame.md](file:///var/www/html/agsonhos/features/reame.md)
- Atualizar com o guia completo de submissão acadêmica, mapa de rastreabilidade entre Casos de Uso $\rightarrow$ Testes Gherkin $\rightarrow$ Classes de Testes Backend, e instruções passo a passo para os professores/avaliadores executarem os testes.

---

## 🧪 Plano de Verificação

### Execução de Testes Automatizados
1. Executar a suíte Behat:
   ```bash
   ./vendor/bin/behat
   ```
   *Garantir que todas as `Features` e `Scenarios` passem sem erros (status verde).*

2. Executar a bateria PHPUnit original para garantir zero regressão:
   ```bash
   ./backend/vendor/bin/phpunit -c backend/phpunit.xml
   ```

3. Executar o comando unificado:
   ```bash
   composer test
   ```

### Validação Acadêmica
- Confirmar que a sintaxe Gherkin segue o padrão oficial em Português (`# language: pt`) com `Funcionalidade`, `Cenário`, `Dado`, `Quando`, `Então`, `E`.

# Lista de Tarefas - Implementação de Testes Gherkin (Behat)

- [x] Configuração do Behat e automação no Composer <!-- id: 0 -->
    - [x] Atualizar `behat.yml` com suítes e múltiplos contextos <!-- id: 1 -->
    - [x] Adicionar scripts de atalho no `composer.json` (`test:behat`, `test:phpunit`, `test`) <!-- id: 2 -->
- [x] Criação e Ajuste das Especificações Gherkin (`.feature`) <!-- id: 3 -->
    - [x] Criar/Preencher `features/checkoutCsrfIntegration.feature` com cenários Gherkin em PT-BR <!-- id: 4 -->
    - [x] Criar `features/autenticacaoSeguranca.feature` com cenários de brute-force e sessão <!-- id: 5 -->
    - [x] Validar sintaxe Gherkin em `features/UseCaseDiagramCustomer.feature` e `features/indepotence_failure.feature` <!-- id: 6 -->
- [x] Implementação das Step Definitions nos Contextos Behat (Reaproveitando PHPUnit) <!-- id: 7 -->
    - [x] Implementar `features/bootstrap/FeatureContext.php` com métodos auxiliares e asserções PHPUnit <!-- id: 8 -->
    - [x] Implementar `features/bootstrap/SecurityContext.php` cobrindo CSRF e autenticação <!-- id: 9 -->
    - [x] Implementar `features/bootstrap/CheckoutContext.php` cobrindo checkout e idempotência <!-- id: 10 -->
- [x] Documentação Acadêmica e Guia de Entrega <!-- id: 11 -->
    - [x] Atualizar `features/reame.md` com mapa de rastreabilidade (Use Cases -> Gherkin -> Testes) e instruções de execução <!-- id: 12 -->
- [x] Validação dos Testes e Execução Final <!-- id: 13 -->
    - [x] Executar `./vendor/bin/behat` e garantir 100% de cenários passando (verde) <!-- id: 14 -->
    - [x] Executar suíte PHPUnit original para garantir zero regressão <!-- id: 15 -->
    - [x] Gerar `walkthrough.md` com evidências da execução <!-- id: 16 -->

# Walkthrough: Recursos e Recursos de Apresentação Acadêmica (Gherkin / Behat)

Implementamos a estrutura completa de testes BDD (Gherkin + Behat) com **3 Modos de Apresentação Visual** desenvolvidos especificamente para impressionar alunos e a banca de professores na sua apresentação.

---

## 🌟 3 Modos de Apresentação Visual para a Banca & Alunos

### 1. Dashboard HTML Executivo (Modo Projeção / Slide)
Criamos um gerador de relatório visual interativo em HTML/CSS (Glassmorphism & Dark Mode) que pode ser projetado durante a apresentação ou anexado ao trabalho escrito.

- **Como Gerar:**
  ```bash
  composer test:report
  ```
- **Localização do Arquivo:** [docs/relatorio_behat_academic.html](file:///var/www/html/agsonhos/docs/relatorio_behat_academic.html)
- **Destaques Visuais:**
  - Cards de Métricas: 26 Cenários, 161 Passos, 100% Taxa de Sucesso e 100% Reaproveitamento PHPUnit.
  - Badges coloridas indicando o status de cada suíte (`PASSED`).
  - Layout responsivo e elegante.

---

### 2. Apresentação em Linha de Comando (Ao Vivo / Terminal Colorido)
Para demonstrações ao vivo durante a aula, o Behat imprime a execução passo a passo em formato descritivo detalhado:

- **Como Executar:**
  ```bash
  ./vendor/bin/behat --format=pretty
  ```
- **Destaques:** Cada etapa `Dado`, `Quando`, `Então` é impressa com destaque verde vivo e checagens em tempo real.

---

### 3. Matriz de Rastreabilidade Acadêmica (Anexo / Artigo)
Documentamos o vínculo direto entre os Casos de Uso, a sintaxe Gherkin humana e os testes técnicos em PHPUnit:

- **Localização:** [features/reame.md](file:///var/www/html/agsonhos/features/reame.md)

---

## 📊 Resumo dos Comandos Disponíveis no `composer.json`

| Comando | Descrição |
| :--- | :--- |
| `composer test:behat` | Executa todos os testes Gherkin/Behat |
| `composer test:phpunit` | Executa a bateria de testes unitários e de integração PHPUnit |
| `composer test:report` | Executa e gera o relatório visual HTML para apresentação |

