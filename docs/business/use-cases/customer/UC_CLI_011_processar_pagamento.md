# UC_CLI_011 - Processar Pagamento Online

## 📋 Informações do Caso de Uso

| Atributo | Detalhe |
| :--- | :--- |
| **Identificador** | `UC_CLI_011` |
| **Nome** | Processar Pagamento Online |
| **Módulo** | Loja Virtual - Carrinho & Compras |
| **Atores Primários** | Sistema / Gateway (Alpha Engine) |
| **Atores Secundários** | Cliente Logado (*Customer*), Gateways Externos (Mercado Pago, Cielo, Banco Central) |
| **Tipo** | Inclusão de `UC_CLI_009` (`<<include>>`) / Integração Financeira |
| **Frequência de Uso** | Muito Alta |
| **Rastreabilidade** | **RF:** [RF018](file:///var/www/html/agsonhos/docs/requirements/functional/functional_requirements.yaml) (Multi-meios de pagamento), [RF019](file:///var/www/html/agsonhos/docs/requirements/functional/functional_requirements.yaml) (Integração Gateway seguro), [RF020](file:///var/www/html/agsonhos/docs/requirements/functional/functional_requirements.yaml) (Faturamento)<br>**RN:** [RN005](file:///var/www/html/agsonhos/docs/requirements/business_rules/business_rules.yaml) (Baixa de estoque pós-pagamento), [RN016](file:///var/www/html/agsonhos/docs/requirements/business_rules/business_rules.yaml) (Desconto PIX)<br>**RNF:** [RNF003](file:///var/www/html/agsonhos/docs/requirements/non_functional/non_functional_requirements.yaml) (Criptografia PCI-DSS / Tokenização), [RNF007](file:///var/www/html/agsonhos/docs/requirements/non_functional/non_functional_requirements.yaml) (Idempotência) |

---

## 1. 🎯 Descrição Sumária
Invocado obrigatoriamente durante o checkout para processar a cobrança financeira através das modalidades configuradas: PIX com QR Code dinâmico e webhook instantâneo, Cartão de Crédito com tokenização no frontend e análise antifraude, ou Boleto Bancário com código de barras, atualizando o status do pedido e disparando a emissão de nota fiscal após liquidação.

---

## 2. ⚡ Pré-Condições
- Pedido instanciado na tabela `tbkk_order` com valor total consolidado.
- Meio de pagamento selecionado e credenciais de API do gateway ativas.

---

## 3. ✅ Pós-Condições
- Transação autorizada ou payload de pagamento gerado (QR Code PIX / Linha digitável de Boleto).
- Transação gravada na tabela `tbkk_order_transaction` e webhooks aguardados assincronamente.

---

## 4. 🚀 Gatilho (Trigger)
O usuário confirma os dados de pagamento na etapa final do checkout.

---

## 5. 🔄 Fluxo Principal (Modalidade PIX Dinâmico)

1. **Sistema:** Recebe a intenção de pagamento com modalidade `PIX`.
2. **Sistema:** Aplica o desconto de pagamento à vista (RN016) e calcula o valor líquido a cobrar.
3. **Sistema:** Aciona o `PixGatewayAdapter`, transmitindo o valor, identificador do pedido e chave de expiração de 15 minutos via API segura.
4. **Gateway Externo:** Retorna o payload contendo o código Pix Copia e Cola (EMV) e a imagem do QR Code em Base64.
5. **Sistema:** Grava a transação como `Pendente` e renderiza o QR Code dinâmico na tela do cliente.
6. **Cliente:** Efetua a leitura e pagamento no app de seu banco.
7. **Gateway Externo:** Envia notificação HTTP assíncrona para o endpoint de webhook (`POST /webhook/payment/pix`).
8. **Sistema:** Valida a assinatura criptográfica do webhook, atualiza o status do pedido para `Pago / Em Processamento`, efetua a baixa definitiva do estoque físico (RN005) e dispara evento de faturamento para emissão de NF-e (RF020).

---

## 6. 🔀 Fluxos Alternativos

- **FA01 - Pagamento via Cartão de Crédito com Tokenização:**
  1. O cliente preenche número, validade, CVV e parcelas no formulário transparente.
  2. O SDK do gateway tokeniza os dados no navegador (sem trafegar número de cartão no servidor - RNF003).
  3. O sistema submete o token à autorização e motor antifraude.
  4. Transação aprovada imediatamente: status do pedido vai para `Pago` em tempo real.
- **FA02 - Pagamento via Boleto Bancário:**
  1. O sistema gera a linha digitável e o PDF do boleto com vencimento em 3 dias úteis.
  2. O status do pedido fica como `Aguardando Pagamento` com estoque pré-reservado.

---

## 7. ⚠️ Fluxos de Exceção

- **FE01 - Cartão de Crédito Recusado:**
  1. A operadora recusa a transação (saldo insuficiente, bloqueio preventivo do emissor).
  2. O sistema exibe mensagem amigável *"Transação não autorizada pela operadora do cartão"* e permite ao cliente trocar de cartão ou alternar para PIX instantâneo.
- **FE02 - Expiração de QR Code PIX:**
  1. O cliente não paga o PIX dentro da janela de 15 minutos.
  2. O sistema expira o payload e libera um botão *"Gerar Novo QR Code PIX"*.

---

## 8. 📜 Regras de Negócio Aplicadas

- **RN005 (Controle de Estoque):** Baixa atômica de estoque efetuada imediatamente após a confirmação da quitação financeira.
- **RN016 (Descontos por Modalidade):** Concessão de abatimento percentual exclusivo para PIX e Dinheiro.

---

## 9. 🖥️ Interface & Campos de Entrada/Saída

### Entradas:
- Token do cartão de crédito, parcelas OU solicitação de chave PIX / Boleto.

### Saídas:
- Objeto de status transacional (`success`, `transaction_id`, `qr_code_image`, `pix_code`, `boleto_url`).
