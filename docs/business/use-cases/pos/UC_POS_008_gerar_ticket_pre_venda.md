# UC_POS_008 - Gerar Ticket de Pré-Venda (POS)

## 📋 Informações do Caso de Uso

| Atributo | Detalhe |
| :--- | :--- |
| **Identificador** | `UC_POS_008` |
| **Nome** | Gerar e Imprimir Ticket de Pré-Venda |
| **Módulo** | Ponto de Venda (POS) - Módulo Vendedor |
| **Atores Primários** | Sistema Alpha Engine POS |
| **Atores Secundários** | Vendedor de Balcão, Cliente Presencial (*Customer*), Impressora Térmica ESC/POS |
| **Tipo** | Inclusão de `UC_POS_007` (`<<include>>`) / Impressão Física |
| **Frequência de Uso** | Contínua / Muito Alta |
| **Rastreabilidade** | **RF:** [RF006](/docs/requirements/functional/functional_requirements.yaml) (Inventário), [RF021](/docs/requirements/functional/functional_requirements.yaml) (Modalidades de entrega)<br>**RN:** [RN016](/docs/requirements/business_rules/business_rules.yaml) (Destaque de desconto no PIX)<br>**RNF:** [RNF001](/docs/requirements/non_functional/non_functional_requirements.yaml) (Impressão térmica ESC/POS rápida < 2s) |

---

## 1. 🎯 Descrição Sumária
Invocado após o salvamento da comanda para compor o leiaute do cupom de pré-venda e despachar os comandos ESC/POS para a impressora térmica do balcão (58mm/80mm), contendo o cabeçalho da loja, número da comanda em destaque (`#150`), código de barras Code-128 / QR Code para leitura óptica rápida no caixa, relação resumida de mercadorias, valor total e instruções de pagamento.

---

## 2. ⚡ Pré-Condições
- Pré-venda salva no banco com status `Pendente`.
- Impressora térmica de balcão conectada e com papel.

---

## 3. ✅ Pós-Condições
- Ticket físico impresso e cortado automaticamente pelo guilhotina da impressora, sendo entregue ao cliente para pagamento.

---

## 4. 🚀 Gatilho (Trigger)
Disparado automaticamente ao final do caso de uso `UC_POS_007`.

---

## 5. 🔄 Fluxo Principal (Caminho Feliz)

1. **Sistema:** Monta o payload ESC/POS formatado do cupom:
   - Logotipo em texto e Razão Social da Alpha Materiais de Construção;
   - Número do Pedido em fonte ampliada (ex: `*** PRE-VENDA #150 ***`);
   - Nome do Vendedor e Nome/CPF do Cliente;
   - Modalidade: `[ RETIRADA NO BALCAO ]` ou `[ ENTREGA EM DOMICILIO ]`;
   - Lista resumida dos itens com quantidades e valores;
   - Valor Total e Destaque: *"Valor com desconto no PIX: R$ [Valor]"*;
   - Código de barras impresso em alta densidade (Code-128) e QR Code.
2. **Sistema:** Despacha o buffer de impressão via Spooler/Websocket para o dispositivo físico.
3. **Impressora:** Imprime e aciona a guilhotina de corte de papel.
4. **Vendedor:** Retira o ticket e entrega ao cliente: *"Por favor, dirija-se ao caixa com este ticket para efetuar o pagamento."*.
5. **Cliente:** Recebe o ticket e caminha para os caixas (`UC_POS_009`).

---

## 6. 🔀 Fluxos Alternativos

- **FA01 - Reimpressão de Ticket de Balcão:**
  1. O papel da impressora atolou ou o cliente perdeu a via.
  2. O vendedor digita o número da pré-venda e clica em `[F11] Reimprimir Ticket`.
  3. O sistema despacha novamente os comandos de impressão sem alterar o estado do pedido.

---

## 7. ⚠️ Fluxos de Exceção

- **FE01 - Impressora Térmica Sem Papel ou Desconectada:**
  1. O sistema não obtém resposta da porta de impressão.
  2. O sistema exibe alerta no terminal: *"Impressora térmica sem papel ou desligada."* e disponibiliza a visualização do ticket em tela com QR Code para leitura direto no monitor.

---

## 8. 📜 Regras de Negócio Aplicadas

- **RN016:** Apresentação clara dos valores com desconto condicional para pagamento à vista.

---

## 9. 🖥️ Interface & Campos de Entrada/Saída

### Entradas:
- Dados estruturados da pré-venda salva.

### Saídas:
- Ticket térmico impresso com código de barras / QR Code para conferência no caixa.
