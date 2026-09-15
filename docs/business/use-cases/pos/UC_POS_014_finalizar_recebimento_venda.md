# UC_POS_014 - Finalizar Pagamento e Venda (POS Caixa)

## 📋 Informações do Caso de Uso

| Atributo | Detalhe |
| :--- | :--- |
| **Identificador** | `UC_POS_014` |
| **Nome** | Finalizar Pagamento e Venda |
| **Módulo** | Ponto de Venda (POS) - Módulo Caixa |
| **Atores Primários** | Operador de Caixa (*Cashier*), Sistema Alpha Engine POS |
| **Atores Secundários** | Servidor da SEFAZ (Emissão NFC-e), Estoque Físico |
| **Tipo** | Condução / Fechamento Fiscal & Estoque |
| **Frequência de Uso** | Contínua / Muito Alta |
| **Rastreabilidade** | **RF:** [RF006](/docs/requirements/functional/functional_requirements.yaml) (Baixa definitiva de inventário), [RF020](/docs/requirements/functional/functional_requirements.yaml) (Faturamento/NFC-e)<br>**RN:** [RN005](/docs/requirements/business_rules/business_rules.yaml) (Baixa definitiva de estoque), [RN012](/docs/requirements/business_rules/business_rules.yaml) (Emissão de documento fiscal obrigatório)<br>**RNF:** [RNF007](/docs/requirements/non_functional/non_functional_requirements.yaml) (Consistência transacional ACID) |

---

## 1. 🎯 Descrição Sumária
Conclui o ciclo da venda presencial no caixa após a liquidação financeira total, executando a baixa atômica definitiva do saldo físico de estoque no banco de dados (`StockDeductionObserver`), alterando o status da comanda para `Pago / Finalizado`, transmitindo os dados fiscais para a Secretaria da Fazenda e invocando a impressão do cupom fiscal NFC-e (`<<include>> UC_POS_015`).

---

## 2. ⚡ Pré-Condições
- Todos os valores da venda quitados com sucesso via `UC_POS_010`.

---

## 3. ✅ Pós-Condições
- Pedido com status `Pago` na base de dados.
- Estoque físico decrementado de forma irreversível.
- Cupom fiscal NFC-e emitido com chave de acesso e QR Code SEFAZ impresso.
- Mercadorias liberadas para retirada imediata no balcão ou despacho logístico.

---

## 4. 🚀 Gatilho (Trigger)
Ocorre automaticamente assim que o saldo devedor da venda é zerado no caixa.

---

## 5. 🔄 Fluxo Principal (Caminho Feliz)

1. **Sistema:** Abre transação ACID no banco de dados MySQL.
2. **Sistema:** Atualiza o status da comanda para `Pago / Finalizado` com timestamp e identificador do operador do caixa.
3. **Sistema:** Executa a baixa definitiva das quantidades físicas dos produtos na tabela `tbkk_product` (RN005).
4. **Sistema:** Registra a movimentação no log de auditoria de estoque (`tbkk_product_inventory_log`).
5. **Sistema:** Transmite o arquivo XML da Nota Fiscal de Consumidor Eletrônica (NFC-e Modelo 65) para o webservice da SEFAZ Estadual.
6. **SEFAZ:** Retorna o protocolo de autorização de uso (ex: `Autorizado o uso da NFC-e - Protocolo: 1352600091823`).
7. **Sistema:** Executa o `COMMIT` da transação no banco de dados.
8. **Sistema:** Invoca `<<include>> UC_POS_015 (Imprimir Recibo e Cupom Fiscal NFC-e)`.
9. **Ator:** Entrega o cupom impresso e libera a sacola de mercadorias ao cliente, finalizando a venda com cordialidade: *"Alpha Materiais agradece a sua preferência!"*.
10. **Sistema:** Prepara o terminal do caixa para a próxima leitura de ticket.

---

## 6. 🔀 Fluxos Alternativos

- **FA01 - Venda com Entrega em Domicílio:**
  1. A pré-venda possui a modalidade de entrega agendada por caminhão.
  2. O sistema emite a NFC-e/NF-e e gera a Ordem de Carregamento e Expedição para a equipe de depósito e logística.

---

## 7. ⚠️ Fluxos de Exceção

- **FE01 - Indisponibilidade dos Servidores da SEFAZ (Emissão em Contingência Offline):**
  1. O webservice da SEFAZ está fora do ar ou inoperante.
  2. O sistema gera automaticamente a NFC-e em modo **Contingência Offline (TP_EMIS = 9)**, assina com o certificado digital A1 local, imprime o cupom com a tarja *"EMITIDA EM CONTINGÊNCIA"* e enfileira o XML no RabbitMQ para transmissão assíncrona assim que a SEFAZ restabelecer a conexão.

---

## 8. 📜 Regras de Negócio Aplicadas

- **RN005 (Estoque Rigoroso):** Baixa definitiva em tempo real.
- **RN012 (Documentação Obrigatória):** Emissão compulsória de documento fiscal ao consumidor.

---

## 9. 🖥️ Interface & Campos de Entrada/Saída

### Entradas:
- Confirmação de recebimento completo.

### Saídas:
- Protocolo de autorização fiscal da SEFAZ, chave de 44 dígitos e liberação para impressão.
