# UC_POS_006 - Adicionar Itens ao Carrinho (POS)

## 📋 Informações do Caso de Uso

| Atributo | Detalhe |
| :--- | :--- |
| **Identificador** | `UC_POS_006` |
| **Nome** | Adicionar Itens ao Carrinho do Balcão |
| **Módulo** | Ponto de Venda (POS) - Módulo Vendedor |
| **Atores Primários** | Vendedor de Balcão (*Sales Representative*) |
| **Atores Secundários** | Sistema Alpha Engine POS |
| **Tipo** | Inclusão de `UC_POS_005` (`<<include>>`) / Operação de Terminal |
| **Frequência de Uso** | Contínua / Muito Alta |
| **Rastreabilidade** | **RF:** [RF004](file:///var/www/html/agsonhos/docs/requirements/functional/functional_requirements.yaml) (Unidades fracionadas), [RF006](file:///var/www/html/agsonhos/docs/requirements/functional/functional_requirements.yaml) (Validação de saldo de estoque), [RF009](file:///var/www/html/agsonhos/docs/requirements/functional/functional_requirements.yaml) (Carrinho)<br>**RN:** [RN001](file:///var/www/html/agsonhos/docs/requirements/business_rules/business_rules.yaml) (Caixas de pisos e revestimentos), [RN005](file:///var/www/html/agsonhos/docs/requirements/business_rules/business_rules.yaml) (Estoque em tempo real), [RN015](file:///var/www/html/agsonhos/docs/requirements/business_rules/business_rules.yaml) (Desconto por volume)<br>**RNF:** [RNF002](file:///var/www/html/agsonhos/docs/requirements/non_functional/non_functional_requirements.yaml) (Tempo de resposta < 50ms) |

---

## 1. 🎯 Descrição Sumária
Invocado a cada bipagem de código de barras ou inclusão manual de produto na pré-venda do balcão, validando o saldo físico disponível, calculando o valor unitário com a tabela do cliente, aplicando eventuais descontos de atacado/volume e inserindo a linha correspondente na grade da comanda.

---

## 2. ⚡ Pré-Condições
- Pré-venda aberta no terminal e produto selecionado.

---

## 3. ✅ Pós-Condições
- Linha de item adicionada ou atualizada na grade de produtos do POS com recálculo automático do subtotal da venda.

---

## 4. 🚀 Gatilho (Trigger)
O vendedor pressiona `Enter` após digitar a quantidade ou o leitor de código de barras efetua a leitura.

---

## 5. 🔄 Fluxo Principal (Caminho Feliz)

1. **Sistema:** Recebe o código do produto e a quantidade informada (ex: `20 sacos de cimento`).
2. **Sistema:** Valida o saldo de estoque físico em tempo real (RN005).
3. **Sistema:** Consulta a tabela de preços do cliente vinculado (Varejo ou Atacado - RN017).
4. **Sistema:** Aplica regra de desconto progressivo por quantidade (RN015) se o volume atingir a faixa promocional.
5. **Sistema:** Insere o item na tabela do terminal com:
   - Nº do Item (sequencial `1, 2, 3...`);
   - SKU e Descrição Comercial;
   - Unidade de Medida (`UN`, `M2`, `CX`, `KG`, `SC`);
   - Quantidade e Preço Unitário;
   - Desconto Concedido e Valor Total da Linha.
6. **Sistema:** Recalcula o subtotal geral da pré-venda e posiciona o cursor no campo de busca para o próximo item.

---

## 6. 🔀 Fluxos Alternativos

- **FA01 - Alteração de Quantidade de Item Já Inserido:**
  1. O vendedor digita `[Item] * [Nova Quantidade]` (ex: `1*50`).
  2. O sistema revalida o estoque e atualiza a quantidade daquela linha.
- **FA02 - Exclusão de Item da Grade:**
  1. O vendedor seleciona a linha e pressiona `[DEL] Excluir Item`.
  2. O sistema remove o item e recalcula o total.

---

## 7. ⚠️ Fluxos de Exceção

- **FE01 - Saldo Físico Insuficiente para a Quantidade:**
  1. O vendedor solicita 100 unidades, mas há apenas 30 na loja.
  2. O sistema bloqueia a adição do excedente e alerta: *"Saldo em loja insuficiente. Disponível no momento: 30 unidades."*

---

## 8. 📜 Regras de Negócio Aplicadas

- **RN001 & RN005 & RN015:** Precisão de unidades, consistência de estoque e regras de precificação progressiva.

---

## 9. 🖥️ Interface & Campos de Entrada/Saída

### Entradas:
- `product_code`, `quantity`, `discount_override` (se autorizado).

### Saídas:
- Linha adicionada na grade do POS com áudio de confirmação (*beep*).
