# DP-88: Implementação de Calculadora de Materiais de Construção (Pisos e Revestimentos)

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-08-24 15:34:36
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/88

## Descrição

# Plano de Implementação - Calculadora de Materiais de Construção (Pisos e Revestimentos)

Este plano detalha a implementação da calculadora de área e conversão para caixas comerciais de pisos e revestimentos cerâmicos na Página de Detalhes do Produto (PDP), baseada nos requisitos funcionais de [`construction_materials_calculator.yaml`](/docs/requirements/functional/construction_materials_calculator.yaml).

Na Fase 1, o cliente terá campos intuitivos para inserir as dimensões do cômodo, aberturas (portas/janelas), margem de perda (padrão 10%) e o rendimento comercial ($m^2/\text{caixa}$).

---

## User Review Required

> [!IMPORTANT]
> **Modo de Apresentação na PDP**: A calculadora será implementada como um **Modal responsivo moderno** acionado por um botão de destaque *"Calcular quantidade de caixas"* posicionado logo ao lado/abaixo do seletor de quantidade da PDP. Essa abordagem evita sobrecarregar visualmente a página do produto e oferece foco total no cálculo.

---

## Proposed Changes

### 1. Documentação e Modelagem de Workflow

#### [MODIFY] [area_calculation_for_ceramic_flooring_and_wall_tiles.puml](/docs/workflows/sequence_diagrams/area_calculation_for_ceramic_flooring_and_wall_tiles.puml)
- Documentar o fluxo de interação completo em formato PlantUML:
  - Cliente acessa a PDP de pisos/revestimentos e clica em "Calcular Material".
  - Abertura do modal da calculadora com seleção do tipo de aplicação (Parede ou Piso).
  - Entrada dos parâmetros (comprimento, largura, altura/pé-direito, vãos de portas/janelas, perda %, $m^2/\text{caixa}$).
  - Execução das fórmulas matemáticas em tempo real no cliente.
  - Transferência do resultado (número inteiro de caixas via `CEIL`) para o campo de quantidade da PDP.

---

### 2. Frontend & Interface do Usuário (PDP)

#### [NEW] [materials-calculator.twig](/backend/resources/views/components/molecules/materials-calculator.twig)
- Criação do componente reutilizável do modal da calculadora:
  - **Aba de Seleção de Tipo**: "Revestimento de Parede" vs "Piso / Pavimento".
  - **Seção de Dimensões do Ambiente**:
    - Comprimento ($m$)
    - Largura ($m$)
    - Pé-direito / Altura ($m$) — ativo apenas para paredes.
  - **Seção de Vãos e Aberturas** (apenas para paredes):
    - Entradas dinâmicas para portas (largura $\times$ altura $\times$ quantidade).
    - Entradas dinâmicas para janelas (largura $\times$ altura $\times$ quantidade).
    - Botões para adicionar/remover aberturas adicionais.
  - **Seção de Parâmetros de Material**:
    - Margem de perda/recorte configurável (padrão de $10\%$, com atalhos para $10\%$, $15\%$, $20\%$).
    - Rendimento por caixa ($m^2/\text{caixa}$) digitado pelo usuário.
  - **Painel de Resultados em Tempo Real**:
    - Área Bruta ($m^2$)
    - Deduções ($m^2$)
    - Área Líquida ($m^2$)
    - Área Total com Perda ($m^2$)
    - **Total de Caixas Necessárias** (destaque visual).
  - **Alerta Legal e Disclaimer Técnico (RF032)**:
    - `"A calculadora é apenas para estimativa. Necessário validar a quantidade final de caixas com um profissional de sua confiança."`
  - **Ações**:
    - Botão "Aplicar Quantidade ao Pedido" (atualiza o `#input-quantity` da PDP e fecha o modal).
    - Botão "Adicionar e ir para o Carrinho" (aplica a quantidade e submete o formulário `#form-product-purchase`).

#### [MODIFY] [show.html.twig](/backend/resources/views/pages/product/show.html.twig)
- Inclusão do botão de acionamento da calculadora (`.egen-btn-calculator`) próximo ao controle de quantidade da PDP.
- Inclusão do partial `components/molecules/materials-calculator.twig`.
- Inclusão dos scripts e estilos da calculadora.

---

### 3. Estilos e Interatividade (Vanilla CSS & JS)

#### [NEW] [materials-calculator.css](/public_html/css/custom/materials-calculator.css)
- Estilos modernos com base no design system `egen-*`:
  - Backdrop blur e animação suave de abertura do modal.
  - Layout em grid com cards de resumo de resultados com destaque em cores da marca.
  - Design responsivo otimizado para celulares e desktops.
  - Alerta de disclaimer com visual informativo suave.

#### [NEW] [materials-calculator.js](/public_html/js/custom/materials-calculator.js)
- Motor de cálculo reativo no cliente:
  - Cálculo de área de parede: $\text{Área Bruta} = 2 \times (\text{comp} \times \text{alt}) + 2 \times (\text{larg} \times \text{alt})$.
  - Deduções de aberturas: $\sum(\text{larg} \times \text{alt} \times \text{qtd})$.
  - Área de piso: $\text{comp} \times \text{larg}$.
  - Área com perda: $\text{Área Líquida} \times (1 + \text{margem\_perda})$.
  - Caixas: $\lceil \text{Área com perda} / \text{rendimento\_cx} \rceil$.
  - Atualização instantânea a cada digitação (`input` event).
  - Integração com `#input-quantity` e disparo de feedback visual ao usuário.

---

### 4. Testes e Validação de Conformidade

#### [NEW] [MaterialsCalculatorTest.php](/tests/Validation/MaterialsCalculatorTest.php)
- Testes unitários para validação dos cenários exatos descritos em [`construction_materials_calculator.yaml`](/docs/requirements/functional/construction_materials_calculator.yaml):
  - **Cenário Parede**: Cômodo $4.0 \times 3.0 \times 2.8\text{m}$, 2 portas ($0.8 \times 2.1\text{m}$), 2 janelas ($1.2 \times 1.0\text{m}$), perda $10\%$, rendimento $1.95\text{m}^2/\text{cx}$ $\rightarrow$ **19 caixas**.
  - **Cenário Piso**: Cômodo $4.0 \times 3.0\text{m}$, perda $10\%$, rendimento $2.28\text{m}^2/\text{cx}$ $\rightarrow$ **6 caixas**.
  - **Casos de borda**: Aberturas maiores que a parede, valores zero, arredondamento estrito para cima (`ceil`).

---

## Verification Plan

### Automated Tests
- Execução dos testes unitários com PHPUnit:
  ```bash
  cd /var/www/html/agsonhos/backend && ./vendor/bin/phpunit ../tests/Validation/MaterialsCalculatorTest.php
  ```

### Manual Verification
- Acessar a PDP no navegador via servidor web local.
- Abrir o modal da calculadora.
- Inserir os dados dos cenários de teste (Parede e Piso) e verificar se os totais de $m^2$ e quantidade de caixas coincidem com os exemplos do YAML.
- Clicar em "Aplicar Quantidade ao Pedido" e verificar se o campo de quantidade da PDP é atualizado para a quantidade calculada.
- Adicionar ao carrinho e verificar se a quantidade de caixas persiste corretamente no fluxo de checkout.

# Walkthrough - Implementação da Calculadora de Materiais de Construção

Implementamos com sucesso a **Calculadora de Materiais de Construção (Pisos e Revestimentos)** na Página de Detalhes do Produto (PDP), atendendo integralmente aos requisitos funcionais descritos em [`construction_materials_calculator.yaml`](/docs/requirements/functional/construction_materials_calculator.yaml) (RF026 a RF032).

---

## 🚀 O que foi Implementado

### 1. Documentação e Workflow
- **Diagrama de Sequência PlantUML**: [`area_calculation_for_ceramic_flooring_and_wall_tiles.puml`](/docs/workflows/sequence_diagrams/area_calculation_for_ceramic_flooring_and_wall_tiles.puml)
  - Mapeamento completo da jornada do cliente: abertura do modal na PDP, alternância entre Parede e Piso, dedução dinâmica de vãos (portas/janelas), aplicação da margem de perda ($10\%$, $15\%$, $20\%$), cálculo de caixas (`CEIL`) e transferência para o formulário de compra/carrinho.

### 2. Motor de Cálculo no Backend & Testes Automatizados
- **Classe de Suporte**: [`MaterialsCalculator.php`](/backend/core/Support/MaterialsCalculator.php)
  - Métodos estáticos `calculateWallCoating()` e `calculateFloorCoating()` com conformidade estrita de tipagem e PHPStan (Nível 0 erros).
- **Testes Unitários (PHPUnit)**: [`MaterialsCalculatorTest.php`](/tests/Validation/MaterialsCalculatorTest.php)
  - Validou os cenários de simulação exatos do documento YAML:
    - **Cenário Parede ($4\times3\times2.8\text{m}$, 2 portas, 2 janelas, $10\%$ perda, $1.95\text{m}^2/\text{cx}$)** $\rightarrow$ **19 caixas** (Área bruta: $39.20\text{m}^2$, deduções: $5.76\text{m}^2$, líquida: $33.44\text{m}^2$, total c/ perda: $36.78\text{m}^2$).
    - **Cenário Piso ($4\times3\text{m}$, $10\%$ perda, $2.28\text{m}^2/\text{cx}$)** $\rightarrow$ **6 caixas** (Área bruta: $12.00\text{m}^2$, total c/ perda: $13.20\text{m}^2$).
    - Casos de borda: vãos maiores que a parede, valores zero e divisão protegida.

### 3. Interface do Usuário e Estilização (PDP)
- **Componente Twig**: [`materials-calculator.twig`](/backend/resources/views/components/molecules/materials-calculator.twig)
  - Modal com efeito glassmorphism, abas de seleção ("Revestimento de Parede" e "Piso / Pavimento"), grid responsivo de dimensões, gerenciador dinâmico de portas/janelas, chips de margem de perda e card com resumo de métricas.
  - Alerta com **Disclaimer Técnico (RF032)** informando o caráter estimativo.
- **Folha de Estilos**: [`materials-calculator.css`](/public_html/css/custom/materials-calculator.css)
  - Visual moderno e integrado à identidade visual da loja (`#fc9003`).
- **Motor Client-Side em Vanilla JS**: [`materials-calculator.js`](/public_html/js/custom/materials-calculator.js)
  - Recálculo instantâneo a cada digitação (`input` event).
  - Transferência direta do número de caixas calculadas para o campo `#input-quantity` da PDP com feedback de animação ao aplicar.
- **Página de Produto (PDP)**: [`show.html.twig`](/backend/resources/views/pages/product/show.html.twig)
  - Inclusão do botão de acionamento `Calcular Quantidade de Caixas (m²)` e vinculação dos scripts e estilos.

---

## 🧪 Resultados dos Testes

```bash
PHPUnit 13.3.1 by Sebastian Bergmann and contributors.

Runtime:       PHP 8.4.24
Configuration: /var/www/html/agsonhos/backend/phpunit.xml

...                                                                 3 / 3 (100%)

Time: 00:00.003, Memory: 20.00 MB

OK (3 tests, 16 assertions)
```

```bash
PHPStan analysis:
Note: Using configuration file /var/www/html/agsonhos/backend/phpstan.neon.

 [OK] No errors
```

