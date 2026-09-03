# UC_CLI_008 - Aplicar Cupom de Desconto

## 📋 Informações do Caso de Uso

| Atributo | Detalhe |
| :--- | :--- |
| **Identificador** | `UC_CLI_008` |
| **Nome** | Aplicar Cupom de Desconto |
| **Módulo** | Loja Virtual - Carrinho & Compras |
| **Atores Primários** | Visitante (*Guest*), Cliente Logado (*Customer*) |
| **Atores Secundários** | Sistema Alpha Engine |
| **Tipo** | Extensão de `UC_CLI_009` (`<<extend>>`) |
| **Frequência de Uso** | Média |
| **Rastreabilidade** | **RF:** [RF009](file:///var/www/html/agsonhos/docs/requirements/functional/functional_requirements.yaml) (Carrinho de compras)<br>**RN:** [RN018](file:///var/www/html/agsonhos/docs/requirements/business_rules/business_rules.yaml) (Campanhas e cupons promocionais)<br>**RNF:** [RNF001](file:///var/www/html/agsonhos/docs/requirements/non_functional/non_functional_requirements.yaml) (Feedback visual) |

---

## 1. 🎯 Descrição Sumária
Estende o fluxo de fechamento de compras permitindo ao cliente inserir um código promocional de cupom (ex: `OBRAPRIMEIRA`, `SEMANADOPISO10`), validando regras de vigência, valor mínimo de pedido, categorias elegíveis e limites de uso por CPF/CNPJ, aplicando o abatimento percentual ou nominal no total da compra.

---

## 2. ⚡ Pré-Condições
- Carrinho com itens ativos.
- Código de cupom fornecido pelo cliente.

---

## 3. ✅ Pós-Condições
- Valor do desconto calculado e deduzido da linha de totais do pedido (`tbkk_order_total` / cupom gravado na sessão).

---

## 4. 🚀 Gatilho (Trigger)
O usuário digita o código do cupom no campo "Cupom de Desconto" e clica em "Aplicar".

---

## 5. 🔄 Fluxo Principal (Caminho Feliz)

1. **Ator:** Insere o código do cupom no formulário e clica no botão "Aplicar".
2. **Sistema:** Consulta a tabela de cupons (`tbkk_coupon`) e valida:
   - Status do cupom (ativo = 1);
   - Data de validade (dentro do intervalo `date_start` e `date_end`);
   - Valor mínimo do subtotal atendido;
   - Limite global e limite de uso por cliente;
   - Categorias ou produtos vinculados ao cupom (RN018).
3. **Sistema:** Calcula o valor do abatimento (ex: 10% sobre os itens elegíveis ou R$ 50,00 fixos).
4. **Sistema:** Atualiza a linha "Desconto (Cupom: XYZ)" no resumo do carrinho/checkout e recalcula o valor total final.
5. **Sistema:** Exibe notificação de sucesso com badge verde em tela.

---

## 6. 🔀 Fluxos Alternativos

- **FA01 - Remoção de Cupom Aplicado:**
  1. O usuário clica no botão `[ X ] Remover Cupom`.
  2. O sistema estorna o desconto do total e restaura o valor original.

---

## 7. ⚠️ Fluxos de Exceção

- **FE01 - Cupom Expirado ou Inexistente:**
  1. O código digitado não confere ou o prazo expirou.
  2. O sistema exibe alerta em vermelho: *"Cupom inválido ou expirado."*
- **FE02 - Valor Mínimo Não Atingido:**
  1. O cupom exige compra mínima de R$ 300,00, mas o carrinho possui apenas R$ 180,00.
  2. O sistema informa: *"Este cupom é válido apenas para compras acima de R$ 300,00."*
- **FE03 - Cupom Já Utilizado pelo Cliente:**
  1. O cliente logado já atingiu o limite de 1 utilização por CPF.
  2. O sistema bloqueia a aplicação e alerta sobre o limite de uso.

---

## 8. 📜 Regras de Negócio Aplicadas

- **RN018 (Campanhas por Categoria):** Validação de regras específicas para cupons sazonais e categorias delimitadas.

---

## 9. 🖥️ Interface & Campos de Entrada/Saída

### Entradas:
- `coupon_code` (string).

### Saídas:
- Linha de desconto adicionada ao resumo de totais e mensagem de validação.
