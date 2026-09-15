# UC_POS_002 - Selecionar Variações do Produto (POS)

## 📋 Informações do Caso de Uso

| Atributo | Detalhe |
| :--- | :--- |
| **Identificador** | `UC_POS_002` |
| **Nome** | Selecionar Variações do Produto (POS Balcão) |
| **Módulo** | Ponto de Venda (POS) - Módulo Vendedor |
| **Atores Primários** | Vendedor de Balcão (*Sales Representative*) |
| **Atores Secundários** | Sistema Alpha Engine POS |
| **Tipo** | Extensão de `UC_POS_001` (`<<extend>>`) |
| **Frequência de Uso** | Alta |
| **Rastreabilidade** | **RF:** [RF004](/docs/requirements/functional/functional_requirements.yaml) (Venda múltiplas unidades), [RF012](/docs/requirements/functional/functional_requirements.yaml) (Opções)<br>**RN:** [RN001](/docs/requirements/business_rules/business_rules.yaml) (Cálculo de m² e conversão de caixas fechadas), [RN003](/docs/requirements/business_rules/business_rules.yaml) (Voltagem e opções)<br>**RNF:** [RNF001](/docs/requirements/non_functional/non_functional_requirements.yaml) (Atalhos de teclado rápidos F1-F12) |

---

## 1. 🎯 Descrição Sumária
Estende a navegação no catálogo do PDV (`UC_POS_001`) quando o item solicitado pelo cliente presencial possui variações (como voltagem 110V/220V em ferramentas, tonalidade de tinta ou venda fracionada por metro quadrado vs. caixa completa de pisos), permitindo ao vendedor selecionar a variação através do teclado e calcular a metragem e caixas necessárias.

---

## 2. ⚡ Pré-Condições
- Produto com variantes filhas selecionado na tela de atendimento.

---

## 3. ✅ Pós-Condições
- Variante e unidade correta selecionada com preço e estoque do SKU específico fixados para adição na comanda.

---

## 4. 🚀 Gatilho (Trigger)
O vendedor pressiona a tecla de atalho correspondente à opção (ex: `[1] 110V`, `[2] 220V`) ou informa a metragem de piso no terminal.

---

## 5. 🔄 Fluxo Principal (Caminho Feliz)

1. **Ator:** Seleciona um piso porcelanato e o cliente informa: *"Preciso cobrir uma sala de 35 metros quadrados."*
2. **Ator:** Digita `35` no campo de metragem e pressiona `Enter`.
3. **Sistema:** Executa a regra RN001: calcula que cada caixa cobre $2{,}14\text{ m²}$, determina a necessidade de 17 caixas fechadas ($36{,}38\text{ m²}$) com margem de recorte.
4. **Sistema:** Exibe em tela o resumo: *17 caixas = 36,38 m² - Total: R$ 2.546,60*.
5. **Ator:** Confirma com o cliente e prossegue com a inclusão no pedido (`UC_POS_006`).

---

## 6. 🔀 Fluxos Alternativos

- **FA01 - Seleção de Voltagem em Ferramenta Elétrica:**
  1. O vendedor seleciona a furadeira e pressiona `[2]` para 220V.
  2. O sistema seleciona o SKU correspondente e verifica o estoque da voltagem 220V.

---

## 7. ⚠️ Fluxos de Exceção

- **FE01 - Variante Específica sem Estoque Físico:**
  1. A variação 110V está zerada no estoque da loja, mas a 220V possui 10 unidades.
  2. O sistema sinaliza em vermelho a opção 110V como esgotada e impede a inclusão.

---

## 8. 📜 Regras de Negócio Aplicadas

- **RN001:** Regra de arredondamento mandatório para caixas comerciais completas em pisos e revestimentos.

---

## 9. 🖥️ Interface & Campos de Entrada/Saída

### Entradas:
- Código da opção ou área em metros quadrados.

### Saídas:
- Quantidade de caixas computadas, metragem total e SKU exato da variante.
