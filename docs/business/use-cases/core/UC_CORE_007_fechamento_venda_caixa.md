# UC_CORE_007 - Fechamento de Venda no Caixa

## 📋 Informações do Caso de Uso

| Atributo | Detalhe |
| :--- | :--- |
| **Identificador** | `UC_CORE_007` |
| **Nome** | Fechamento de Venda no Caixa com Baixa de Estoque e NFC-e |
| **Módulo** | Núcleo Central (Core) - Ponto de Venda (POS) & Frente de Caixa |
| **Atores Primários** | Operador de Caixa (*Cashier*), Cliente Presencial (*Customer*) |
| **Atores Secundários** | Servidor da SEFAZ (Autorização NFC-e), Terminal TEF / Adquirente, Gaveta de Dinheiro |
| **Tipo** | Condução / Transacional Fiscal & Baixa Definitiva |
| **Frequência de Uso** | Contínua / Muito Alta |
| **Rastreabilidade** | **RF:** [RF006](/docs/requirements/functional/functional_requirements.yaml) (Gestão automática de inventário), [RF018](/docs/requirements/functional/functional_requirements.yaml) (Multi-meios de pagamento), [RF020](/docs/requirements/functional/functional_requirements.yaml) (Faturamento/NF-e e NFC-e)<br>**RN:** [RN005](/docs/requirements/business_rules/business_rules.yaml) (Baixa definitiva de inventário), [RN012](/docs/requirements/business_rules/business_rules.yaml) (Documentação fiscal obrigatória), [RN016](/docs/requirements/business_rules/business_rules.yaml) (Desconto em dinheiro/PIX)<br>**RNF:** [RNF003](/docs/requirements/non_functional/non_functional_requirements.yaml) (Segurança TEF/PCI), [RNF007](/docs/requirements/non_functional/non_functional_requirements.yaml) (Consistência ACID e contingência offline) |

---

## 1. 🎯 Descrição Sumária
Executa a liquidação financeira das compras no caixa da loja física através da bipagem do código de barras do ticket de pré-venda emitido no balcão (`UC_CORE_006`) ou passagem direta de itens avulsos. Suporta pagamentos fracionados multi-meios (ex: parte em dinheiro com cálculo de troco e parte no cartão via TEF integrado ou PIX dinâmico), executa a baixa definitiva e atômica do estoque físico no banco de dados e transmite os dados fiscais à SEFAZ Estadual para emissão e impressão imediata da Nota Fiscal de Consumidor Eletrônica (NFC-e Modelo 65).

---

## 2. ⚡ Pré-Condições
1. Operador de caixa autenticado no terminal com turno de caixa aberto e fundo de troco registrado.
2. Certificado Digital ICP-Brasil (A1/A3) instalado e comunicável com o webservice da SEFAZ.

---

## 3. ✅ Pós-Condições
- Pedido baixado com status `Concluído / Pago`.
- Saldo físico de estoque debitado de forma irreversível nas tabelas de inventário.
- XML da NFC-e assinado, autorizado pela SEFAZ e impresso com QR Code fiscal para o consumidor.
- Gaveta de dinheiro acionada para abertura (quando houver liquidação em espécie).

---

## 4. 🚀 Gatilho (Trigger)
O cliente apresenta o ticket impresso de pré-venda no caixa e o operador faz a leitura do código de barras com o scanner óptico.

---

## 5. 🔄 Fluxo Principal (Caminho Feliz - Pagamento em Cartão via TEF)

1. **Ator (Caixa):** Bipeia o código de barras do ticket de pré-venda.
2. **Sistema:** Recupera a pré-venda do banco (`UC_CORE_006`), converte a reserva temporária em venda ativa e lista todos os itens, quantidades, valores e vendedor comissionado na tela do caixa.
3. **Ator:** Pergunta ao cliente: *"Qual será a forma de pagamento hoje?"*.
4. **Cliente:** Responde: *"Cartão de débito."*.
5. **Ator:** Pressiona o atalho `[F3] Cartão TEF` e digita o valor total da comanda (ex: R$ 850,00).
6. **Sistema:** Dispara a transação para o módulo TEF dedicado e aciona o PinPad no balcão.
7. **Cliente:** Insere/aproxima o cartão no PinPad e digita sua senha pessoal.
8. **Sistema (TEF):** Recebe o código de autorização da adquirente (NSU e Comprovante de Pagamento).
9. **Sistema:** Inicia a transação ACID no banco de dados MySQL:
   - Registra o pagamento na tabela `tbkk_pos_payment`;
   - Executa a baixa definitiva das quantidades físicas dos produtos na tabela `tbkk_product` (RN005);
   - Gera o log de auditoria de movimentação de saída (`tbkk_stock_movement`).
10. **Sistema:** Assina digitalmente o XML da NFC-e e envia o lote ao webservice síncrono da SEFAZ.
11. **SEFAZ:** Processa a requisição e retorna o protocolo de autorização em menos de 1,5 segundo.
12. **Sistema:** Comanda a impressora térmica fiscal para emitir o DANFE simplificado da NFC-e contendo chave de 44 dígitos e QR Code para consulta móvel (RN012).
13. **Ator:** Entrega a via fiscal ao cliente, agradece a compra e libera o romaneio de separação/retirada de mercadorias.

---

## 6. 🔀 Fluxos Alternativos

- **FA01 - Pagamento em Dinheiro com Troco:**
  1. No passo 4, o cliente paga em dinheiro.
  2. O operador digita o valor recebido em cédulas (ex: R$ 900,00 para uma conta de R$ 850,00).
  3. O sistema calcula o troco (R$ 50,00), abre a gaveta de dinheiro eletrônica e emite a NFC-e.
- **FA02 - Pagamento Multi-Meios Fracionado (Split Payment):**
  1. O cliente divide a conta: R$ 500,00 no PIX e R$ 350,00 no Cartão de Crédito.
  2. O sistema abate os valores sequencialmente, mantendo a comanda aberta até a quitação dos R$ 850,00 totais.

---

## 7. ⚠️ Fluxos de Exceção

- **FE01 - Rejeição ou Queda de Conexão com a SEFAZ (Contingência Offline):**
  1. No passo 10, o servidor da SEFAZ encontra-se fora do ar (timeout HTTP).
  2. O sistema entra automaticamente no modo **Contingência Offline NFC-e**:
     - Assina o documento fiscal com a tag `<tpEmis>9</tpEmis>`;
     - Emite o cupom com a indicação legal "Emitido em Contingência";
     - Enfileira o XML em fila RabbitMQ/banco para transmissão automática assim que o link da SEFAZ for restabelecido.
  3. A venda do cliente é finalizada sem filas no caixa.
- **FE02 - Transação Recusada no PinPad (TEF):**
  1. No passo 8, o cliente erra a senha ou o cartão não possui saldo.
  2. O sistema exibe o código do erro na tela do operador, cancela o lote TEF e permite selecionar outro cartão ou meio de pagamento sem perder os itens.

---

## 8. 📜 Regras de Negócio Aplicadas

- **RN005 (Baixa Definitiva de Inventário):** A efetivação do pagamento encerra o ciclo de reserva e decrementa permanentemente o saldo em estoque.
- **RN012 (Documentação Fiscal Obrigatória):** Emissão compulsória de documento fiscal oficial (NFC-e modelo 65 ou NF-e modelo 55) com registro de tributos.
- **RN016 (Desconto por Modalidade):** Recálculo automático caso o operador selecione pagamento em dinheiro ou PIX.

---

## 9. 🖥️ Interface & Campos de Entrada/Saída

### Entradas:
- Código do ticket de balcão lido via scanner óptico (`ticket_barcode`).
- Meio de pagamento (`cash`, `tef_debit`, `tef_credit`, `pix_pos`).
- Valor entregue (`amount_tendered`).

### Saídas:
- DANFE NFC-e impresso na impressora térmica não fiscal de 80mm com QR Code.
- Comprovante de TEF bancário.
- Atualização imediata do painel de movimentação diária do caixa.
