# UC_POS_013 - Pagar com Dinheiro com Cálculo de Troco (POS)

## 📋 Informações do Caso de Uso

| Atributo | Detalhe |
| :--- | :--- |
| **Identificador** | `UC_POS_013` |
| **Nome** | Pagar com Dinheiro com Cálculo de Troco |
| **Módulo** | Ponto de Venda (POS) - Módulo Caixa |
| **Atores Primários** | Cliente Presencial (*Customer*), Operador de Caixa (*Cashier*) |
| **Atores Secundários** | Gaveta de Dinheiro Eletrônica (Acionamento RJ11) |
| **Tipo** | Especialização de `UC_POS_010` (Generalização de Pagamento) |
| **Frequência de Uso** | Muito Alta |
| **Rastreabilidade** | **RF:** [RF018](/docs/requirements/functional/functional_requirements.yaml) (Meios de pagamento)<br>**RN:** [RN016](/docs/requirements/business_rules/business_rules.yaml) (Desconto em espécie à vista)<br>**RNF:** [RNF001](/docs/requirements/non_functional/non_functional_requirements.yaml) (Cálculo automático de troco em tela grande de alto contraste) |

---

## 1. 🎯 Descrição Sumária
Especializa a liquidação financeira no caixa quando o cliente realiza o pagamento em cédulas/moedas físicas, aplicando o desconto legal de pagamento em dinheiro à vista (RN016), computando instantaneamente o troco exato a ser devolvido ao cliente e disparando o pulso elétrico para abertura automática da gaveta de valores do caixa.

---

## 2. ⚡ Pré-Condições
- Caixa operando com gaveta conectada à impressora fiscal/PDV.

---

## 3. ✅ Pós-Condições
- Valor recebido e troco computados e registrados no fechamento diário do operador de caixa.
- Gaveta de valores aberta e fechada com segurança.

---

## 4. 🚀 Gatilho (Trigger)
O operador pressiona `[F1] Dinheiro` e informa a quantia entregue em cédulas pelo cliente.

---

## 5. 🔄 Fluxo Principal (Caminho Feliz)

1. **Ator:** Pressiona `[F1]` para pagamento em Dinheiro.
2. **Sistema:** Aplica a regra RN016 (desconto à vista para pagamento em dinheiro) e exibe o valor líquido (ex: `Total: R$ 85,50`).
3. **Cliente:** Entrega uma nota de `R$ 100,00` ao operador.
4. **Ator:** Digita `100` no campo "Valor Recebido" e pressiona `Enter`.
5. **Sistema:** Calcula em milissegundos o troco (`R$ 100,00 - R$ 85,50 = R$ 14,50`) e exibe em fontes grandes de alto contraste: ***TROCO: R$ 14,50***.
6. **Sistema:** Envia o pulso de comando para a gaveta de dinheiro, que destrava e se abre automaticamente.
7. **Ator:** Guarda a cédula recebida, retira os `R$ 14,50` de troco, entrega ao cliente e fecha a gaveta.
8. **Sistema:** Registra a quitação e prossegue para `UC_POS_014`.

---

## 6. 🔀 Fluxos Alternativos

- **FA01 - Pagamento com Valor Exato (Sem Troco):**
  1. O cliente entrega o valor exato da compra (`R$ 85,50`).
  2. O caixa pressiona a tecla de atalho `[Enter]` (Valor Exato).
  3. O sistema computa troco `R$ 0,00` e abre a gaveta para guarda do numerário.

---

## 7. ⚠️ Fluxos de Exceção

- **FE01 - Valor Informado Menor que o Total Devido:**
  1. O operador digita `50` para uma compra de `R$ 85,50`.
  2. O sistema não considera o pagamento concluído, registra o recebimento parcial de R$ 50,00 e solicita a forma de pagamento para os R$ 35,50 restantes.

---

## 8. 📜 Regras de Negócio Aplicadas

- **RN016:** Aplicação de desconto à vista em compras pagas em espécie.

---

## 9. 🖥️ Interface & Campos de Entrada/Saída

### Entradas:
- `amount_received` (valor entregue pelo cliente).

### Saídas:
- `change_due` (valor do troco) destacado na interface e pulso de abertura da gaveta.
