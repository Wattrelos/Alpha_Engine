# UC_CLI_009 - Realizar Checkout

## 📋 Informações do Caso de Uso

| Atributo | Detalhe |
| :--- | :--- |
| **Identificador** | `UC_CLI_009` |
| **Nome** | Realizar Checkout |
| **Módulo** | Loja Virtual - Carrinho & Compras |
| **Atores Primários** | Cliente Logado (*Customer*), Visitante (*Guest*) |
| **Atores Secundários** | Sistema Alpha Engine |
| **Tipo** | Condução / Transacional |
| **Frequência de Uso** | Muito Alta |
| **Rastreabilidade** | **RF:** [RF010](/docs/requirements/functional/functional_requirements.yaml) (Frete), [RF017](/docs/requirements/functional/functional_requirements.yaml) (Shiptos), [RF018](/docs/requirements/functional/functional_requirements.yaml) (Multi-meios de pagamento), [RF020](/docs/requirements/functional/functional_requirements.yaml) (Faturamento)<br>**RN:** [RN005](/docs/requirements/business_rules/business_rules.yaml) (Estoque), [RN007](/docs/requirements/business_rules/business_rules.yaml) (Frete cubagem), [RN016](/docs/requirements/business_rules/business_rules.yaml) (Desconto PIX)<br>**RNF:** [RNF003](/docs/requirements/non_functional/non_functional_requirements.yaml) (Segurança/LGPD), [RNF007](/docs/requirements/non_functional/non_functional_requirements.yaml) (Idempotência) |

---

## 1. 🎯 Descrição Sumária
Orquestra o fluxo de fechamento de pedido da loja virtual, reunindo em uma experiência intuitiva (*One-Page Checkout* transparente) as etapas de identificação do cliente, seleção do endereço de entrega da obra (*Shiptos*), escolha da modalidade de frete, inserção de observações de entrega e acionamento da etapa de processamento de pagamento (`<<include>> UC_CLI_011`).

---

## 2. ⚡ Pré-Condições
1. Carrinho de compras contendo ao menos um item válido.
2. Saldo de estoque físico disponível para todos os produtos da comanda.

---

## 3. ✅ Pós-Condições
- Pedido gravado no banco de dados (`tbkk_order`, `tbkk_order_product`, `tbkk_order_total`) com status inicial `Pendente`.
- Estoque reservado/baixado atomicamente.
- Redirecionamento para a tela de confirmação e despacho de e-mail transacional.

---

## 4. 🚀 Gatilho (Trigger)
O usuário clica no botão "Finalizar Compra" ou "Avançar para o Checkout" no carrinho.

---

## 5. 🔄 Fluxo Principal (Caminho Feliz)

1. **Ator:** Clica em "Finalizar Compra".
2. **Sistema:** Carrega a tela unificada de checkout (`/checkout`) e gera uma chave única de idempotência (`X-Idempotency-Key`) no Redis.
3. **Ator:** Seleciona o endereço de entrega desejado a partir do seu livro de endereços ou cadastra um novo endereço de entrega de obra (`UC_CLI_019`).
4. **Sistema:** Recalcula as opções de frete disponíveis para o CEP do endereço selecionado (Correios, Transportadora com Munck ou Retirada na Loja - `UC_CLI_007`).
5. **Ator:** Seleciona a opção de frete preferida.
6. **Ator:** Pode aplicar cupom de desconto promocional (`<<extend>> UC_CLI_008`).
7. **Ator:** Escolhe o meio de pagamento (PIX, Cartão de Crédito ou Boleto Bancário) e clica em "Confirmar e Pagar".
8. **Sistema:** Invoca `<<include>> UC_CLI_011 (Processar Pagamento)`.
9. **Sistema:** Registra a ordem de compra com ID único (ex: `#10542`), limpa o carrinho da sessão e exibe a página de confirmação de sucesso (`/checkout/success`).

---

## 6. 🔀 Fluxos Alternativos

- **FA01 - Checkout como Visitante (Guest Checkout):**
  1. O usuário não possui login.
  2. O sistema executa o caso de uso estendido `<<extend>> UC_CLI_010 (Comprar como Visitante)` solicitando apenas dados essenciais sem forçar criação de senha.

---

## 7. ⚠️ Fluxos de Exceção

- **FE01 - Falha de Estoque Concorrente (Lock Otimista):**
  1. Outro comprador esgota o item durante o tempo em que o cliente preenchia o checkout.
  2. O sistema detecta a ruptura no momento do commit da transação, cancela a finalização e exibe alerta: *"O produto [Nome] acabou de se esgotar. Por favor, atualize seu carrinho."*
- **FE02 - Duplo Clique / Repetição de Requisição:**
  1. O cliente clica duas vezes no botão de confirmação.
  2. O middleware de idempotência do Redis intercepta a segunda requisição idêntica e retorna o resultado da primeira transação sem duplicar o pedido.

---

## 8. 📜 Regras de Negócio Aplicadas

- **RN005 (Controle de Estoque):** Garantia de consistência do saldo físico pós-finalização.
- **RN016 (Desconto por Pagamento à Vista):** Aplicação automática do desconto configurado quando selecionado PIX.

---

## 9. 🖥️ Interface & Campos de Entrada/Saída

### Entradas:
- `address_id` (endereço selecionado).
- `shipping_method` (código do frete).
- `payment_method` (pix, credit_card, boleto).
- `comment` (instruções de entrega).

### Saídas:
- Número do pedido gerado (`order_id`), resumo detalhado da compra e comprovante/instruções de quitação.
