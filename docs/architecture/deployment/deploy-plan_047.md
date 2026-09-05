# DP-47: Aperfeiçoamento da Pasta de Documentação (`docs/`)

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-07-16 16:51:01
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/47

## Descrição

# Plano de Implementação - Aperfeiçoamento da Pasta de Documentação (`docs/`)

Este plano propõe melhorias organizacionais, correções de inconsistências de caminhos e a eliminação de arquivos e pastas vazias/órfãs na pasta `docs/`, garantindo que ela sirva como a fonte única da verdade consistente tanto para desenvolvedores humanos quanto para Agentes de IA.

## Alterações Propostas

### 1. Correções de Nomes e Padronização de Arquivos de Requisitos
*   Renomear [`RF001_kits.yaml`](file:///var/www/html/agsonhos/docs/requirements/functional/RF001_kits.yaml) para `functional_requirements.yaml`.
    > [!NOTE]
    > O arquivo atual contém todos os 25 requisitos funcionais do sistema, e não apenas o de kits. A mudança alinha o nome ao seu conteúdo real e resolve a referência a `functional_requirements.yaml` em `traceability-rules.yaml`.
*   Renomear [`RNF001_performance.yaml`](file:///var/www/html/agsonhos/docs/requirements/non_functional/RNF001_performance.yaml) para `non_functional_requirements.yaml`.
    > [!NOTE]
    > O arquivo atual contém todos os 8 requisitos não-funcionais do sistema. A alteração padroniza a nomenclatura junto ao arquivo de requisitos funcionais.

---

### 2. Correção de Typos e Árvores no Diagrama de Pastas
#### [MODIFY] [diagrama_de_pastas_so-pastas.puml](file:///var/www/html/agsonhos/docs/diagrama_de_pastas_so-pastas.puml)
*   Corrigir grafia do diretório `businness/` para `business/` (linhas 89 e 101).
*   Corrigir grafia do diretório `requeriments/` para `requirements/` (linha 96).
*   Padronizar caracteres de indentação de árvore (trocar pipes avulsos `|` por caracteres estruturais `│` ou `└──`).

---

### 3. Criação de Documento de Glossário de Regras de Negócio
#### [NEW] [glossary.md](file:///var/www/html/agsonhos/docs/requirements/business_rules/glossary.md)
Preenchimento do arquivo de glossário que estava vazio com as definições de termos de negócio do domínio de materiais de construção, incluindo:
*   **Venda Fracionada (Pisos/Azulejos):** Lógica de conversão entre peça, m² e caixas.
*   **Kits/Combos de Produtos:** Definição técnica e comercial de agrupamentos de venda.
*   **BOPIS (Buy Online, Pick Up In Store):** Regra de negócio para retirada física na loja e suas implicações legais (não-aplicabilidade dos 7 dias de arrependimento do CDC para retirada presencial).
*   **Saneamento de Pseudo-Null (FK=0):** Tradução comercial para ausência de relacionamento.
*   **Preços Progressivos / Atacado e Varejo:** Lógica aplicada a volume e segmentação de cliente (PF/PJ).

---

### 4. Resolução de Pastas Vazias
Criação de arquivos `README.md` explicativos para direcionar o propósito de diretórios atualmente vazios:
*   #### [NEW] [README.md](file:///var/www/html/agsonhos/docs/business/glossary/README.md)
    Aponta a centralização de termos técnicos no glossário de agentes e o de regras de negócio.
*   #### [NEW] [README.md](file:///var/www/html/agsonhos/docs/business/use-cases/README.md)
    Orienta onde estão documentados os diagramas e requisitos funcionais.
*   #### [NEW] [README.md](file:///var/www/html/agsonhos/docs/workflows/activity_diagrams/README.md)
    Explica a utilidade da pasta para diagramas de atividade futuros (PlantUML/Mermaid).

---

## Plano de Verificação

### Verificação Manual
1.  Confirmar que as movimentações de arquivo (`git mv` ou renomeação manual) foram realizadas e que os caminhos originais não deixaram resíduos perdidos.
2.  Validar se a sintaxe do diagrama de pastas `diagrama_de_pastas_so-pastas.puml` está correta e abre no PlantUML sem quebras.
3.  Validar as regras de rastreabilidade (traceability) nos novos arquivos YAML.

- `[x]` Renomear arquivos de requisitos (`RF001_kits.yaml` -> `functional_requirements.yaml`, `RNF001_performance.yaml` -> `non_functional_requirements.yaml`)
- `[x]` Corrigir typos e caracteres de árvore no `docs/diagrama_de_pastas_so-pastas.puml`
- `[x]` Preencher `docs/requirements/business_rules/glossary.md` com definições de negócio do domínio
- `[x]` Criar arquivos README explicativos para as pastas vazias (`docs/business/glossary`, `docs/business/use-cases`, `docs/workflows/activity_diagrams`)
- `[x]` Validar caminhos e testes das alterações na documentação

# Walkthrough - Otimizações Realizadas na Documentação da Alpha Engine

Concluímos com sucesso as melhorias, padronizações e correções nos diretórios e arquivos de documentação da **Alpha Engine** sob a pasta `docs/`.

---

## 🛠️ Alterações Realizadas

### 1. Renomeação e Alinhamento de Arquivos de Requisitos
*   **Arquivos Renomeados:**
    *   [`RF001_kits.yaml`](file:///var/www/html/agsonhos/docs/requirements/functional/RF001_kits.yaml) ➔ [`functional_requirements.yaml`](file:///var/www/html/agsonhos/docs/requirements/functional/functional_requirements.yaml)
    *   [`RNF001_performance.yaml`](file:///var/www/html/agsonhos/docs/requirements/non_functional/RNF001_performance.yaml) ➔ [`non_functional_requirements.yaml`](file:///var/www/html/agsonhos/docs/requirements/non_functional/non_functional_requirements.yaml)
*   **Motivação:** Os arquivos continham a listagem integral de todos os requisitos do sistema (RF001 a RF025 e RNF001 a RNF008), tornando a nomenclatura original restrita/imprecisa. O arquivo `traceability-rules.yaml` já referia-se a `functional_requirements.yaml`, o que causava uma inconsistência.

---

### 2. Correção de Typos e Árvores no Diagrama de Pastas
*   **Arquivo Modificado:** [`diagrama_de_pastas_so-pastas.puml`](file:///var/www/html/agsonhos/docs/diagrama_de_pastas_so-pastas.puml)
*   **Melhorias:**
    *   Correção de grafia dos caminhos `businness/` para `business/` e `requeriments/` para `requirements/`.
    *   Remoção do diretório inexistente `data_base_new_schema/`.
    *   Correção de escopo do diretório `diagramsForStackholders/` (movido do subdiretório `docs/` para a raiz do workspace `.`, que é onde ele reside no disco).
    *   Adicionado o diretório `BusinessKnowledgeBase/` contendo o contexto corporativo e regras de negócio.
    *   Atualização da representação do diretório `tests/` para detalhar as pastas `newTables/` (scripts SQL) e `test_mensageria_rebbit/` (RabbitMQ).
    *   Correção e padronização dos caracteres estruturais de árvore de arquivos (substituição de pipes soltos `|` por conectores como `│` e `├──`).

---

### 3. Preenchimento de Arquivo Vazio de Glossário de Regras
*   **Arquivo Modificado:** [`glossary.md`](file:///var/www/html/agsonhos/docs/requirements/business_rules/glossary.md)
*   **Conteúdo Adicionado:** Um glossário de negócios rico detalhando regras cruciais do domínio de comércio eletrônico de materiais de construção, tais como:
    *   *Venda Fracionada (RN001):* Cálculo de peças por metro quadrado (`m²`) e caixas.
    *   *Especificações por Categoria (RN003):* Campos técnicos obrigatórios (ex: voltagem para ferramentas, secagem para cimento).
    *   *Direito de Arrependimento e BOPIS (RN011):* Implicações e regras do CDC sobre a retirada de produtos em lojas físicas (BOPIS) e a obrigação de devolução com embalagem original.
    *   *Descontos Dinâmicos e Progressivos (RN015, RN017):* Diferenças e regras de precificação entre clientes de varejo (CPF) e atacado (PJ).

---

### 4. Organização e Explicação de Diretórios Vazios
Criados arquivos `README.md` que contextualizam e explicam o propósito de pastas que anteriormente estavam vazias no repositório:
*   [`docs/business/glossary/README.md`](file:///var/www/html/agsonhos/docs/business/glossary/README.md) - Explica a divisão do glossário técnico (para agentes de IA) e do glossário operacional de domínio (para humanos/desenvolvedores).
*   [`docs/business/use-cases/README.md`](file:///var/www/html/agsonhos/docs/business/use-cases/README.md) - Mapeia a localização de requisitos e diagramas de processos de negócio.
*   [`docs/workflows/activity_diagrams/README.md`](file:///var/www/html/agsonhos/docs/workflows/activity_diagrams/README.md) - Orienta o propósito de uso de futuros diagramas de atividade.

---

## 🔍 Resultados da Validação

*   **Verificação de Rastreabilidade:** A correspondência de arquivos em [`traceability-rules.yaml`](file:///var/www/html/agsonhos/docs/business/traceability-rules.yaml) agora está 100% consistente, apontando para o arquivo [`functional_requirements.yaml`](file:///var/www/html/agsonhos/docs/requirements/functional/functional_requirements.yaml) renomeado.
*   **Status do Git:** Todas as modificações, renomeações de arquivo via `git mv` e adições foram devidamente indexadas e organizadas no stage da área de preparação:
    ```bash
    Changes to be committed:
        new file:   docs/business/glossary/README.md
        new file:   docs/business/use-cases/README.md
        modified:   docs/diagrama_de_pastas_so-pastas.puml
        modified:   docs/requirements/business_rules/glossary.md
        renamed:    docs/requirements/functional/RF001_kits.yaml -> docs/requirements/functional/functional_requirements.yaml
        renamed:    docs/requirements/non_functional/RNF001_performance.yaml -> docs/requirements/non_functional/non_functional_requirements.yaml
        new file:   docs/workflows/activity_diagrams/README.md
    ```

