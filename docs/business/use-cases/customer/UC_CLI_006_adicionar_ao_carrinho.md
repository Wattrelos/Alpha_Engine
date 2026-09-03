# UC_CLI_006 - Adicionar ao Carrinho & Gerenciar Itens

## 📋 Informações do Caso de Uso

| Atributo | Detalhe |
| :--- | :--- |
| **Identificador** | `UC_CLI_006` |
| **Nome** | Adicionar ao Carrinho & Gerenciar Itens |
| **Módulo** | Loja Virtual - Carrinho & Compras |
| **Atores Primários** | Visitante (*Guest*), Cliente Logado (*Customer*) |
| **Atores Secundários** | Sistema Alpha Engine |
| **Tipo** | Condução / Operacional |
| **Frequência de Uso** | Muito Alta |
| **Rastreabilidade** | **RF:** [RF004](file:///var/www/html/agsonhos/docs/requirements/functional/functional_requirements.yaml) (Venda fracionada), [RF009](file:///var/www/html/agsonhos/docs/requirements/functional/functional_requirements.yaml) (Carrinho persistente)<br>**RN:** [RN001](file:///var/www/html/agsonhos/docs/requirements/business_rules/business_rules.yaml) (Cálculo m²/caixa), [RN005](file:///var/www/html/agsonhos/docs/requirements/business_rules/business_rules.yaml) (Controle de estoque em tempo real), [RN015](file:///var/www/html/agsonhos/docs/requirements/business_rules/business_rules.yaml) (Desconto por volume)<br>**RNF:** [RNF001](file:///var/www/html/agsonhos/docs/requirements/non_functional/non_functional_requirements.yaml) (Interatividade) |

---

## 1. 🎯 Descrição Sumária
Permite ao usuário adicionar produtos ao carrinho de compras (definindo quantidade, opções de variação ou área em metros quadrados para revestimentos), visualizar a sacola lateral (*drawer*), alterar volumes, remover mercadorias, verificar o subtotal acumulado com descontos progressivos por volume e persistir o estado do carrinho na sessão ou conta de usuário.

---

## 2. ⚡ Pré-Condições
- O produto desejado deve estar ativo e possuir saldo positivo no estoque.

---

## 3. ✅ Pós-Condições
- O item é incluído na estrutura do carrinho (`$_SESSION['cart']` em Redis ou tabela `tbkk_cart` para usuário logado).
- O contador do cabeçalho (*badge*) e o valor subtotal são atualizados dinamicamente.

---

## 4. 🚀 Gatilho (Trigger)
O usuário clica no botão "Adicionar ao Carrinho" ou "Comprar" na PDP ou vitrine.

---

## 5. 🔄 Fluxo Principal (Caminho Feliz)

1. **Ator:** Na PDP de um produto (ex: *Cimento CP-II 50kg*), define a quantidade desejada (ex: 20 sacos) e clica em "Adicionar ao Carrinho".
2. **Sistema:** Valida via AJAX se as opções obrigatórias foram selecionadas e consulta a disponibilidade em tempo real no estoque (RN005).
3. **Sistema:** Adiciona o item ao carrinho, calcula o subtotal e verifica se há desconto por volume aplicável (RN015).
4. **Sistema:** Abre o painel lateral (*mini-cart drawer*) exibindo:
   - Imagem, título e SKU do produto;
   - Quantidade selecionada com botões `[ - ]` e `[ + ]`;
   - Preço unitário e subtotal do item;
   - Subtotal geral do pedido;
   - Botões "Continuar Comprando" e "Finalizar Compra" (leva para `/cart` ou `/checkout`).
5. **Ator:** Clica em "Finalizar Compra".
6. **Sistema:** Redireciona o usuário para a página de conferência do carrinho ou checkout (`UC_CLI_009`).

---

## 6. 🔀 Fluxos Alternativos

- **FA01 - Alteração de Quantidade no Carrinho:**
  1. No carrinho, o ator clica no botão `[ + ]` para aumentar a quantidade.
  2. O sistema revalida o estoque, recalcula o subtotal e atualiza os valores instantaneamente via AJAX.
- **FA02 - Remoção de Item:**
  1. O ator clica no ícone de lixeira ao lado de um item.
  2. O sistema remove a mercadoria do carrinho, recalcula o total e exibe confirmação.

---

## 7. ⚠️ Fluxos de Exceção

- **FE01 - Quantidade Solicitada Superior ao Saldo em Estoque:**
  1. O usuário tenta adicionar 50 unidades de um item que possui apenas 15 unidades no estoque físico.
  2. O sistema impede a adição do excesso, exibe alerta *"Apenas 15 unidades disponíveis em estoque"* e ajusta a quantidade no carrinho para o saldo máximo disponível.
- **FE02 - Carrinho Vazio:**
  1. O usuário remove todos os itens do carrinho.
  2. O sistema exibe o estado *"Seu carrinho está vazio"* com link para a página principal.

---

## 8. 📜 Regras de Negócio Aplicadas

- **RN001 (Venda Fracionada):** Cálculo de caixas fechadas quando o produto for cerâmica/porcelanato.
- **RN005 (Controle de Estoque):** Bloqueio de inclusão caso o estoque físico não atenda ao pedido.
- **RN015 (Descontos por Volume):** Aplicação de desconto progressivo automático em compras volumosas de materiais brutos (cimento, areia, tijolos).

---

## 9. 🖥️ Interface & Campos de Entrada/Saída

### Entradas:
- `product_id`, `option_value_id` (se houver), `quantity`.

### Saídas:
- Payload JSON com status de sucesso, quantidade total de itens, subtotal atualizado e HTML do drawer do carrinho.
