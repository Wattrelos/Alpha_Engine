# UC_POS_009 - Localizar Pré-Venda por Ticket (POS Caixa)

## 📋 Informações do Caso de Uso

| Atributo | Detalhe |
| :--- | :--- |
| **Identificador** | `UC_POS_009` |
| **Nome** | Localizar Pré-Venda por Ticket |
| **Módulo** | Ponto de Venda (POS) - Módulo Caixa |
| **Atores Primários** | Operador de Caixa (*Cashier*), Cliente Presencial (*Customer*) |
| **Atores Secundários** | Sistema Alpha Engine POS |
| **Tipo** | Condução / Operação de Caixa |
| **Frequência de Uso** | Contínua / Muito Alta |
| **Rastreabilidade** | **RF:** [RF006](file:///var/www/html/agsonhos/docs/requirements/functional/functional_requirements.yaml) (Inventário), [RF018](file:///var/www/html/agsonhos/docs/requirements/functional/functional_requirements.yaml) (Recebimento de vendas)<br>**RN:** [RN005](file:///var/www/html/agsonhos/docs/requirements/business_rules/business_rules.yaml) (Controle de estoque), [RN016](file:///var/www/html/agsonhos/docs/requirements/business_rules/business_rules.yaml) (Descontos por modalidade)<br>**RNF:** [RNF001](file:///var/www/html/agsonhos/docs/requirements/non_functional/non_functional_requirements.yaml) (Operação ágil por leitor óptico) |

---

## 1. 🎯 Descrição Sumária
Permite ao operador de caixa resgatar e carregar na tela do terminal de pagamento (`/pos/caixa`) a comanda de pré-venda gerada no balcão, realizando a leitura óptica do código de barras ou QR Code impresso no ticket do cliente (ou digitando manualmente o número do pedido), exibindo todos os itens, valores e preparando o fluxo de quitação financeira (`UC_POS_010`).

---

## 2. ⚡ Pré-Condições
- Caixa autenticado no terminal POS Caixa.
- Cliente apresentando o ticket impresso pelo vendedor (`UC_POS_008`).

---

## 3. ✅ Pós-Condições
- Dados da pré-venda carregados no terminal do caixa em estado ativo para processamento de pagamento.

---

## 4. 🚀 Gatilho (Trigger)
O operador de caixa bipa o ticket do cliente com o leitor de código de barras ou digita o número da comanda.

---

## 5. 🔄 Fluxo Principal (Caminho Feliz)

1. **Cliente:** Entrega o ticket `#150` ao operador de caixa.
2. **Ator:** Aponta o leitor óptico para o código de barras impresso no ticket.
3. **Sistema:** Captura a leitura, extrai o número da comanda (`order_id = 150`) e consulta o registro na tabela `tbkk_pos_order`.
4. **Sistema:** Valida que a comanda está com status `Pendente`.
5. **Sistema:** Carrega na tela do caixa:
   - Número do Pedido e Nome do Vendedor emissor;
   - Nome e CPF/CNPJ do Cliente;
   - Grade discriminada de mercadorias com quantidades e preços;
   - Valor Bruto, Descontos de Atacado/Volume e Valor Total a Pagar;
   - Teclas de atalho para as formas de pagamento disponíveis (`[F1] Dinheiro`, `[F2] Cartão Débito`, `[F3] Cartão Crédito`, `[F4] PIX QR Code`).
6. **Ator:** Confirma os itens com o cliente e pergunta a forma de pagamento desejada (`UC_POS_010`).

---

## 6. 🔀 Fluxos Alternativos

- **FA01 - Digitação Manual do Número do Ticket:**
  1. O código de barras do papel está amassado ou ilegível.
  2. O caixa digita `150` no teclado numérico e pressiona `Enter`.
  3. O sistema carrega a comanda normalmente.

---

## 7. ⚠️ Fluxos de Exceção

- **FE01 - Ticket Já Pago Anteriormente:**
  1. O ticket informado já foi liquidado em outro momento.
  2. O sistema emite alerta sonoro de bloqueio: *"Este pedido já foi pago e finalizado em [Data/Hora]."*
- **FE02 - Ticket Cancelado ou Inexistente:**
  1. A comanda foi cancelada ou o número digitado não existe.
  2. O sistema alerta: *"Pré-venda não localizada ou cancelada."*

---

## 8. 📜 Regras de Negócio Aplicadas

- **RN005 (Estoque):** Garantia de que a comanda resgatada mantém a reserva temporária ativa.

---

## 9. 🖥️ Interface & Campos de Entrada/Saída

### Entradas:
- Código de barras lido ou número do ticket digitado.

### Saídas:
- Painel do caixa carregado com o resumo da venda pronto para recebimento.
