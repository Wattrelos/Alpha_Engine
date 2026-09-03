# UC_CLI_004 - Selecionar Variantes & Opções

## 📋 Informações do Caso de Uso

| Atributo | Detalhe |
| :--- | :--- |
| **Identificador** | `UC_CLI_004` |
| **Nome** | Selecionar Variantes & Opções |
| **Módulo** | Loja Virtual - Catálogo, Busca & Mídia |
| **Atores Primários** | Visitante (*Guest*), Cliente Logado (*Customer*) |
| **Atores Secundários** | Sistema Alpha Engine |
| **Tipo** | Extensão de `UC_CLI_003` (`<<extend>>`) |
| **Frequência de Uso** | Alta |
| **Rastreabilidade** | **RF:** [RF004](file:///var/www/html/agsonhos/docs/requirements/functional/functional_requirements.yaml) (Venda múltiplas unidades), [RF012](file:///var/www/html/agsonhos/docs/requirements/functional/functional_requirements.yaml) (PDP com variações)<br>**RN:** [RN001](file:///var/www/html/agsonhos/docs/requirements/business_rules/business_rules.yaml) (Variações de unidades m²/cx/peça), [RN003](file:///var/www/html/agsonhos/docs/requirements/business_rules/business_rules.yaml) (Voltagem e atributos)<br>**RNF:** [RNF001](file:///var/www/html/agsonhos/docs/requirements/non_functional/non_functional_requirements.yaml) (Usabilidade) |

---

## 1. 🎯 Descrição Sumária
Estende o caso de uso `UC_CLI_003` quando o produto visualizado possui variantes configuráveis (ex: voltagem 110V/220V/Bivolt, cor de tinta, dimensões de piso, ou unidade de venda por metro quadrado vs. caixa fechada). Permite ao usuário selecionar uma opção e atualiza dinamicamente o preço, o SKU filho, a foto principal e o saldo de estoque.

---

## 2. ⚡ Pré-Condições
- O produto pai deve possuir ao menos uma opção ou variante filha configurada no catálogo.

---

## 3. ✅ Pós-Condições
- A combinação de opções selecionada é fixada na interface, atualizando o preço final, o estoque do SKU específico e habilitando a adição correta ao carrinho.

---

## 4. 🚀 Gatilho (Trigger)
O usuário clica em um botão seletor de voltagem, cor, tamanho ou unidade de venda na PDP.

---

## 5. 🔄 Fluxo Principal (Caminho Feliz)

1. **Ator:** Na PDP de um produto com variações (ex: *Furadeira de Impacto*), visualiza os botões de opção `[ 110V ]` e `[ 220V ]`.
2. **Ator:** Clica na opção desejada (ex: `220V`).
3. **Sistema:** Captura o evento de seleção via JavaScript, localiza a variante correspondente no JSON da PDP.
4. **Sistema:** Atualiza na interface:
   - O código SKU específico da variante;
   - O preço correspondente (se houver acréscimo/desconto na opção);
   - A disponibilidade de estoque da variante selecionada;
   - A imagem principal correspondente à variação (se configurada).
5. **Sistema:** Valida que a opção é obrigatória para compra e marca o estado como pronto para envio ao carrinho.

---

## 6. 🔀 Fluxos Alternativos

- **FA01 - Seleção de Unidade de Revestimento (Calculadora de m²):**
  1. O produto é um porcelanato com venda em m² e caixa fechada.
  2. O ator insere a metragem necessária de sua obra (ex: $25\text{ m²}$).
  3. O sistema calcula automaticamente a quantidade de caixas inteiras necessárias (arredondando para cima conforme RN001) e exibe a metragem real total faturada e o valor final.

---

## 7. ⚠️ Fluxos de Exceção

- **FE01 - Variante Selecionada Sem Estoque:**
  1. O ator clica na opção `110V`, mas o estoque dessa voltagem está zerado.
  2. O sistema risca visualmente a opção (badge "Esgotado"), impede a seleção para compra e exibe botão "Avise-me quando chegar".

---

## 8. 📜 Regras de Negócio Aplicadas

- **RN001 (Variações de Unidades de Medida):** Garante a correta conversão de peças/caixas para metros quadrados em materiais de acabamento.

---

## 9. 🖥️ Interface & Campos de Entrada/Saída

### Entradas:
- Seleção de `option_value_id` (ex: voltagem, cor, tamanho).
- Quantidade em m² informada no simulador de área.

### Saídas:
- Atualização visual instantânea do preço, estoque e imagem do SKU filho.
