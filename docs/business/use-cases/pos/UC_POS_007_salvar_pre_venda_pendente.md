# UC_POS_007 - Salvar Pré-Venda Pendente (POS)

## 📋 Informações do Caso de Uso

| Atributo | Detalhe |
| :--- | :--- |
| **Identificador** | `UC_POS_007` |
| **Nome** | Salvar Pré-Venda Pendente |
| **Módulo** | Ponto de Venda (POS) - Módulo Vendedor |
| **Atores Primários** | Sistema Alpha Engine POS |
| **Atores Secundários** | Vendedor de Balcão (*Sales Representative*) |
| **Tipo** | Inclusão de `UC_POS_005` (`<<include>>`) / Persistência e Reserva |
| **Frequência de Uso** | Contínua / Muito Alta |
| **Rastreabilidade** | **RF:** [RF006](/docs/requirements/functional/functional_requirements.yaml) (Gestão automática de inventário), [RF020](/docs/requirements/functional/functional_requirements.yaml) (Faturamento)<br>**RN:** [RN005](/docs/requirements/business_rules/business_rules.yaml) (Controle de estoque em tempo real), [RN016](/docs/requirements/business_rules/business_rules.yaml) (Cálculo prévio de desconto à vista)<br>**RNF:** [RNF007](/docs/requirements/non_functional/non_functional_requirements.yaml) (Idempotência no fechamento da comanda) |

---

## 1. 🎯 Descrição Sumária
Invocado ao concluir o atendimento de balcão para validar a chave de idempotência no Redis, abrir uma transação atômica no banco de dados MySQL, persistir a comanda na tabela `tbkk_pos_order` com status `Pendente`, registrar o vendedor responsável, vincular o cliente e disparar a geração física do ticket impresso (`<<include>> UC_POS_008`).

---

## 2. ⚡ Pré-Condições
- Pré-venda contendo ao menos 1 item e saldo verificado.

---

## 3. ✅ Pós-Condições
- Pré-venda gravada com ID sequencial único (ex: `#150`), status `Pendente` e código de barras/QR Code de ticket gerado.
- Estoque colocado em estado de reserva temporária de balcão.

---

## 4. 🚀 Gatilho (Trigger)
O vendedor comanda a gravação da pré-venda pressionando `[F10]`.

---

## 5. 🔄 Fluxo Principal (Caminho Feliz)

1. **Sistema:** Verifica a chave de idempotência (`X-Idempotency-Key`) no Redis para impedir gravação duplicada.
2. **Sistema:** Inicia uma transação no MySQL (`START TRANSACTION`).
3. **Sistema:** Insere o cabeçalho da pré-venda na tabela `tbkk_pos_order` com:
   - `order_id` gerado (ex: `#150`);
   - `seller_id` do vendedor logado;
   - `customer_id` do cliente identificado;
   - `total_amount` e `status = 'pending'`;
   - `delivery_type` ('retirada_balcao' ou 'entrega_obra').
4. **Sistema:** Insere os itens na tabela `tbkk_pos_order_item`.
5. **Sistema:** Executa o `COMMIT` da transação.
6. **Sistema:** Invoca `<<include>> UC_POS_008 (Gerar e Imprimir Ticket de Pré-Venda)`.
7. **Sistema:** Limpa a tela do terminal do vendedor, disponibilizando-o para o próximo cliente.

---

## 6. 🔀 Fluxos Alternativos

- N/A.

---

## 7. ⚠️ Fluxos de Exceção

- **FE01 - Falha de Conexão com o Banco de Dados:**
  1. Ocorre uma falha momentânea de rede no terminal.
  2. O sistema executa o `ROLLBACK` automático, preserva os dados digitados na memória local do navegador/aplicativo e exibe: *"Erro de rede ao salvar pré-venda. Pressione [F10] para tentar novamente."*

---

## 8. 📜 Regras de Negócio Aplicadas

- **RN005 (Estoque):** Marcação de reserva temporária de 30 minutos na comanda para impedir que outro canal venda o mesmo saldo físico enquanto o cliente caminha até o caixa.

---

## 9. 🖥️ Interface & Campos de Entrada/Saída

### Entradas:
- Estrutura de dados da comanda preenchida no terminal.

### Saídas:
- `pos_order_id`, total gravado e acionamento da impressão do ticket.
