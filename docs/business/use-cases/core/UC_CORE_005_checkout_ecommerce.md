# UC_CORE_005 - Checkout E-Commerce

## 📋 Informações do Caso de Uso

| Atributo | Detalhe |
| :--- | :--- |
| **Identificador** | `UC_CORE_005` |
| **Nome** | Realizar Checkout E-Commerce & Faturamento |
| **Módulo** | Núcleo Central (Core) - Compras & Fechamento Financeiro |
| **Atores Primários** | Cliente Logado (*Customer*), Gateways de Pagamento |
| **Atores Secundários** | Sistema Alpha Engine, Servidor SEFAZ (Emissão NF-e) |
| **Tipo** | Condução / Transacional Crítico (ACID) |
| **Frequência de Uso** | Muito Alta |
| **Rastreabilidade** | **RF:** [RF010](/docs/requirements/functional/functional_requirements.yaml) (Frete dinâmico/cubagem), [RF017](/docs/requirements/functional/functional_requirements.yaml) (Múltiplos shiptos/obras), [RF018](/docs/requirements/functional/functional_requirements.yaml) (Multi-meios de pagamento), [RF019](/docs/requirements/functional/functional_requirements.yaml) (Integração gateway), [RF020](/docs/requirements/functional/functional_requirements.yaml) (Faturamento/NF-e)<br>**RN:** [RN002](/docs/requirements/business_rules/business_rules.yaml) (Cubagem), [RN007](/docs/requirements/business_rules/business_rules.yaml) (Modalidades frete), [RN008](/docs/requirements/business_rules/business_rules.yaml) (BOPIS/Frete grátis), [RN016](/docs/requirements/business_rules/business_rules.yaml) (Desconto PIX)<br>**RNF:** [RNF003](/docs/requirements/non_functional/non_functional_requirements.yaml) (Criptografia PCI-DSS / TLS 1.3), [RNF007](/docs/requirements/non_functional/non_functional_requirements.yaml) (Idempotência / Lock transacional) |

---

## 1. 🎯 Descrição Sumária
Coordena o fluxo de fechamento de compras online em tela única (*One-Page Transparent Checkout*), integrando a escolha de múltiplos endereços de entrega de obra (*shiptos*), o cálculo logístico por peso/cubagem, o cálculo tributário interestadual (ICMS-ST / DIFAL), o processamento multi-meios de pagamento (PIX com QR Code dinâmico imediato, Cartão de Crédito com tokenização ou Boleto Bancário) e a reserva definitiva de inventário com geração do pedido oficial.

---

## 2. ⚡ Pré-Condições
1. O cliente deve estar autenticado com sessão válida (`UC_CORE_003`).
2. O carrinho deve possuir pelo menos 1 produto e todos os itens devem ter saldo em estoque confirmado.

---

## 3. ✅ Pós-Condições
- Pedido gravado nas tabelas `tbkk_order`, `tbkk_order_product` e `tbkk_order_total`.
- Estoque deduzido no banco de dados.
- Carrinho de compras esvaziado.
- Webhook do gateway registrado para atualização do status de faturamento.

---

## 4. 🚀 Gatilho (Trigger)
O cliente clica em "Finalizar Pedido" ou "Avançar para o Checkout".

---

## 5. 🔄 Fluxo Principal (Compra com Pagamento via PIX Dinâmico)

1. **Ator:** Clica em "Finalizar Compra" no carrinho.
2. **Sistema:** Carrega o One-Page Checkout e cria chave de idempotência exclusiva para evitar duplicidade de cobrança (`X-Idempotency-Key`).
3. **Ator:** Seleciona o endereço de entrega da obra desejado (ex: "Obra Residencial Alphaville - Lote 14") cadastrado em seu livro de shiptos (RF017).
4. **Sistema:** Recupera a cubagem total dos produtos e peso acumulado (RN002) e consulta as tabelas de frete para o CEP da obra:
   - Opção 1: Frete Dedicado Caminhão com Munck (R$ 180,00 - 3 dias úteis - RN007);
   - Opção 2: Retirada Presencial Gratuita na Loja (BOPIS - RN008).
5. **Ator:** Seleciona "Frete Dedicado Caminhão com Munck".
6. **Ator:** Seleciona a opção de pagamento "PIX (com 5% de desconto à vista - RN016)".
7. **Sistema:** Aplica a linha de desconto correspondente na tabela de totais do pedido e exibe o resumo financeiro atualizado.
8. **Ator:** Revisa os dados e clica em "Pagar com PIX".
9. **Sistema:** Abre transação segura no banco de dados, gera o registro do pedido (`order_id = 10842`) com status `Aguardando Pagamento`.
10. **Sistema:** Dispara requisição autenticada à API do Gateway de Pagamento, recebendo a string "Copia e Cola" e a imagem do QR Code dinâmico com expiração de 30 minutos.
11. **Sistema:** Exibe a tela de confirmação com o QR Code na tela, dispara e-mail com instruções e inicia monitoramento via WebSocket/SSE para confirmação instantânea.
12. O cliente realiza a transferência em seu app bancário; o gateway notifica o Alpha Engine via Webhook e o pedido migra automaticamente para `Pago / Em Separação`.

---

## 6. 🔀 Fluxos Alternativos

- **FA01 - Pagamento com Cartão de Crédito:**
  1. No passo 6, o ator escolhe "Cartão de Crédito".
  2. Informa os dados do cartão (número, titular, validade, CVV) e número de parcelas (até 10x sem juros).
  3. O sistema envia os dados tokenizados diretamente ao gateway via JavaScript seguro (sem transitar dados sensíveis de cartão pelo servidor - RNF003).
  4. O gateway autoriza a transação em segundos e o pedido é gerado já com status `Aprovado`.
- **FA02 - Cadastro de Novo Shipto de Obra Durante o Checkout:**
  1. No passo 3, o cliente clica em "Entregar em outro endereço de obra".
  2. Preenche o CEP, logradouro, número, responsável no canteiro e instruções de descarga.
  3. O endereço é salvo no livro de endereços do cliente e selecionado para o pedido atual.

---

## 7. ⚠️ Fluxos de Exceção

- **FE01 - Rejeição da Transação de Cartão de Crédito:**
  1. No FA01, a operadora recusa a cobrança (saldo insuficiente, antifraude ou dados incorretos).
  2. O sistema exibe o motivo retornado pelo gateway (ex: *"Transação não autorizada pelo emissor do cartão"*) e permite que o cliente tente outro cartão ou mude para PIX sem perder os dados da tela.
- **FE02 - Conflito de Ruptura de Estoque Durante o Checkout:**
  1. Entre a entrada no checkout e o clique em "Pagar", outro cliente esgotou o saldo do SKU no balcão físico.
  2. O sistema detecta o lock de estoque, cancela a transação e alerta o cliente: *"Um dos itens do seu carrinho esgotou enquanto você finalizava a compra. Seu pedido não foi cobrado."*.

---

## 8. 📜 Regras de Negócio Aplicadas

- **RN002 e RN007 (Cubagem e Modalidades de Frete):** Separação obrigatória de frete rodoviário de grande porte vs. pequenos pacotes.
- **RN008 (Retirada em Loja):** Disponibilização de frete zero na retirada física.
- **RN016 (Descontos por Modalidade):** Concessão de abatimento proporcional no pagamento via PIX.

---

## 9. 🖥️ Interface & Campos de Entrada/Saída

### Entradas:
- `address_id` (shipto), `shipping_method_code`, `payment_method_code`.
- Dados tokenizados do cartão ou seleção de PIX.
- `comments_delivery` (instruções de canteiro de obras).

### Saídas:
- `order_id`, número de protocolo, payload do QR Code PIX ou comprovante de captura do cartão.
- Redirecionamento para a página `/checkout/success`.
