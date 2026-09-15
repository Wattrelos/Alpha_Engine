# UC_POS_010 - Processar Pagamento (POS Caixa)

## 📋 Informações do Caso de Uso

| Atributo | Detalhe |
| :--- | :--- |
| **Identificador** | `UC_POS_010` |
| **Nome** | Processar Pagamento no Caixa |
| **Módulo** | Ponto de Venda (POS) - Módulo Caixa |
| **Atores Primários** | Operador de Caixa (*Cashier*), Cliente Presencial (*Customer*) |
| **Atores Secundários** | Sistema Alpha Engine POS |
| **Tipo** | Condução / Generalização de Pagamentos |
| **Frequência de Uso** | Contínua / Muito Alta |
| **Rastreabilidade** | **RF:** [RF018](/docs/requirements/functional/functional_requirements.yaml) (Multi-meios de pagamento), [RF019](/docs/requirements/functional/functional_requirements.yaml) (Integrações financeiras), [RF020](/docs/requirements/functional/functional_requirements.yaml) (Faturamento)<br>**RN:** [RN016](/docs/requirements/business_rules/business_rules.yaml) (Desconto PIX/Dinheiro à vista)<br>**RNF:** [RNF003](/docs/requirements/non_functional/non_functional_requirements.yaml) (Segurança financeira) |

---

## 1. 🎯 Descrição Sumária
Caso de uso genérico que gerencia a escolha e recepção dos valores devidos pelo cliente presencial no caixa, suportando pagamento simples ou divisão em múltiplos meios de pagamento (ex: parte em dinheiro e parte em cartão), especializando-se nas formas concretas: PIX (`UC_POS_011`), Cartão TEF (`UC_POS_012`) e Dinheiro (`UC_POS_013`), e encaminhando para a finalização da venda (`UC_POS_014`).

---

## 2. ⚡ Pré-Condições
- Pré-venda carregada na tela do caixa com valor pendente maior que zero (`UC_POS_009`).

---

## 3. ✅ Pós-Condições
- Total do pedido integralmente quitado e registrado nas tabelas financeiras do PDV.

---

## 4. 🚀 Gatilho (Trigger)
O operador de caixa seleciona a tecla correspondente ao meio de pagamento informado pelo cliente.

---

## 5. 🔄 Fluxo Principal (Caminho Feliz)

1. **Ator:** Pergunta ao cliente a forma de pagamento (PIX, Cartão ou Dinheiro).
2. **Cliente:** Informa a modalidade escolhida.
3. **Ator:** Pressiona a tecla correspondente no terminal:
   - Se for PIX: o sistema executa `UC_POS_011 (Pagar com Pix - QR Code)`;
   - Se for Cartão de Crédito/Débito: o sistema executa `UC_POS_012 (Pagar com Cartão - TEF)`;
   - Se for Dinheiro: o sistema executa `UC_POS_013 (Pagar com Dinheiro - Cálculo de Troco)`.
4. **Sistema:** Registra a liquidação do valor e confirma que o saldo restante a pagar é `R$ 0,00`.
5. **Sistema:** Prossegue automaticamente para `UC_POS_014 (Finalizar Pagamento e Venda)`.

---

## 6. 🔀 Fluxos Alternativos

- **FA01 - Pagamento Dividido / Múltiplos Meios (Split Payment):**
  1. O cliente deseja pagar R$ 500,00 em dinheiro e R$ 1.200,00 no cartão de crédito.
  2. O caixa registra primeiramente os R$ 500,00 em dinheiro (`UC_POS_013`).
  3. O sistema abate o valor e exibe: *"Saldo Restante a Pagar: R$ 1.200,00"*.
  4. O caixa aciona o pagamento por cartão para os R$ 1.200,00 restantes (`UC_POS_012`).
  5. Concluídas ambas as etapas, o sistema avança para a finalização.

---

## 7. ⚠️ Fluxos de Exceção

- **FE01 - Cancelamento da Venda no Caixa (Desistência do Cliente):**
  1. O cliente não possui fundos e opta por desistir da compra.
  2. O operador pressiona `[F12] Cancelar Pré-Venda`.
  3. O sistema altera o status da comanda para `Cancelado`, estorna a reserva de estoque e libera os produtos para o catálogo geral.

---

## 8. 📜 Regras de Negócio Aplicadas

- **RN005 & RN016:** Consistência de saldo e desconto para modalidades à vista.

---

## 9. 🖥️ Interface & Campos de Entrada/Saída

### Entradas:
- Modalidade selecionada e valor a receber.

### Saídas:
- Extrato de pagamentos lançados e saldo devedor zerado.
