# UC_POS_011 - Pagar com Pix via QR Code (POS)

## 📋 Informações do Caso de Uso

| Atributo | Detalhe |
| :--- | :--- |
| **Identificador** | `UC_POS_011` |
| **Nome** | Pagar com Pix via QR Code Dinâmico |
| **Módulo** | Ponto de Venda (POS) - Módulo Caixa |
| **Atores Primários** | Cliente Presencial (*Customer*), Operador de Caixa (*Cashier*) |
| **Atores Secundários** | Gateway Pix / Banco Central, Display do Cliente (*PinPad / Visor*) |
| **Tipo** | Especialização de `UC_POS_010` (Generalização de Pagamento) |
| **Frequência de Uso** | Muito Alta |
| **Rastreabilidade** | **RF:** [RF018](file:///var/www/html/agsonhos/docs/requirements/functional/functional_requirements.yaml) (Checkout multi-meios), [RF019](file:///var/www/html/agsonhos/docs/requirements/functional/functional_requirements.yaml) (Gateway Pix)<br>**RN:** [RN016](file:///var/www/html/agsonhos/docs/requirements/business_rules/business_rules.yaml) (Desconto automático no PIX à vista)<br>**RNF:** [RNF002](file:///var/www/html/agsonhos/docs/requirements/non_functional/non_functional_requirements.yaml) (Confirmação bancária em < 3 segundos) |

---

## 1. 🎯 Descrição Sumária
Especializa a liquidação financeira no caixa quando o cliente escolhe pagar via PIX, aplicando automaticamente o desconto de pagamento à vista (RN016), gerando o payload do PIX dinâmico com o valor exato da compra e exibindo o QR Code na tela secundária/visor voltado para o cliente, processando a conciliação bancária instantânea.

---

## 2. ⚡ Pré-Condições
- Caixa operando com valor a receber na comanda.

---

## 3. ✅ Pós-Condições
- Transação Pix confirmada pelo banco com ID de liquidação (End-to-End Id) e valor registrado como quitado.

---

## 4. 🚀 Gatilho (Trigger)
O operador de caixa pressiona `[F4] PIX` no terminal.

---

## 5. 🔄 Fluxo Principal (Caminho Feliz)

1. **Ator:** Pressiona `[F4]` para pagamento PIX.
2. **Sistema:** Aplica a regra RN016 (dedução de 5% de desconto à vista sobre os produtos elegíveis) e recalcula o total a cobrar.
3. **Sistema:** Gera a cobrança instantânea via API do gateway Pix e projeta o QR Code dinâmico no visor do cliente (Display voltado para o público).
4. **Cliente:** Aponta a câmera do celular no aplicativo do seu banco, confere o valor e o nome da loja e confirma o Pix.
5. **Sistema:** Recebe a notificação de confirmação bancária instantânea (Websocket / Webhook interno).
6. **Sistema:** Emite aviso sonoro de pagamento aprovado (*ding*), altera a tela do caixa para verde com a mensagem *"PIX APROVADO COM SUCESSO"* e retorna para `UC_POS_010`.

---

## 6. 🔀 Fluxos Alternativos

- N/A.

---

## 7. ⚠️ Fluxos de Exceção

- **FE01 - Timeout ou Desistência do Pix:**
  1. O cliente enfrenta instabilidade em seu banco e não consegue ler o código.
  2. O caixa clica em `[ESC] Cancelar PIX`, o sistema invalida o QR Code gerado e retorna para o menu de formas de pagamento sem perder os itens da venda.

---

## 8. 📜 Regras de Negócio Aplicadas

- **RN016 (Desconto por Modalidade):** Concessão mandatória de desconto financeiro para pagamentos à vista no PIX.

---

## 9. 🖥️ Interface & Campos de Entrada/Saída

### Entradas:
- Comando de seleção de PIX.

### Saídas:
- QR Code dinâmico no visor do cliente, End-to-End ID gravado e confirmação instantânea.
