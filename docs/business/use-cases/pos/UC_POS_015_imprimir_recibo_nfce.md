# UC_POS_015 - Imprimir Recibo e Cupom Fiscal NFC-e (POS)

## 📋 Informações do Caso de Uso

| Atributo | Detalhe |
| :--- | :--- |
| **Identificador** | `UC_POS_015` |
| **Nome** | Imprimir Recibo e Cupom Fiscal NFC-e |
| **Módulo** | Ponto de Venda (POS) - Módulo Caixa |
| **Atores Primários** | Sistema Alpha Engine POS |
| **Atores Secundários** | Impressora Fiscal/Térmica Não Fiscal, Cliente Presencial (*Customer*) |
| **Tipo** | Inclusão de `UC_POS_014` (`<<include>>`) / Impressão Fiscal |
| **Frequência de Uso** | Contínua / Muito Alta |
| **Rastreabilidade** | **RF:** [RF020](file:///var/www/html/agsonhos/docs/requirements/functional/functional_requirements.yaml) (Faturamento/NF-e e NFC-e)<br>**RN:** [RN012](file:///var/www/html/agsonhos/docs/requirements/business_rules/business_rules.yaml) (Documentação fiscal obrigatória para eventuais devoluções)<br>**RNF:** [RNF001](file:///var/www/html/agsonhos/docs/requirements/non_functional/non_functional_requirements.yaml) (Impressão térmica em bobina de 80mm com QR Code legível) |

---

## 1. 🎯 Descrição Sumária
Invocado automaticamente ao finalizar a venda para compor o Documento Auxiliar da Nota Fiscal de Consumidor Eletrônica (DANFE NFC-e) no padrão oficial exigido pela legislação tributária, enviando os comandos para a impressora térmica do caixa para impressão do cabeçalho fiscal, discriminação dos itens e impostos (ICMS/PIS/COFINS), chave de acesso de 44 dígitos e o QR Code oficial de consulta pública do consumidor.

---

## 2. ⚡ Pré-Condições
- Venda aprovada e autorizada pela SEFAZ ou gerada em contingência offline (`UC_POS_014`).
- Impressora de cupom do caixa pronta para impressão.

---

## 3. ✅ Pós-Condições
- Cupom fiscal NFC-e físico emitido e entregue ao cliente junto com as mercadorias.

---

## 4. 🚀 Gatilho (Trigger)
Disparado imediatamente após a confirmação da transação fiscal no caso de uso `UC_POS_014`.

---

## 5. 🔄 Fluxo Principal (Caminho Feliz)

1. **Sistema:** Formata o leiaute do DANFE NFC-e com:
   - Dados do Emitente: Razão Social, CNPJ, Inscrição Estadual, Endereço da Loja Física;
   - Relação de Itens: Código, Descrição, NCM/SH, CST, Quantidade, Unidade, Valor Unitário, Valor Total do Item;
   - Detalhamento de Tributos Incidentes (Lei da Transparência Fiscal 12.741/2012);
   - Formas de Pagamento e Troco Devolvido;
   - Chave de Acesso de 44 dígitos e Protocolo de Autorização da SEFAZ;
   - QR Code bidimensional de autenticidade para consulta rápida pelo celular do cliente.
2. **Sistema:** Envia os dados de impressão para a impressora térmica de 80mm.
3. **Impressora:** Imprime o documento e corta o papel com a guilhotina.
4. **Sistema:** Se o cliente possuir e-mail cadastrado, envia simultaneamente o XML e o PDF da nota fiscal em cópia digital.
5. **Operador do Caixa:** Entrega o cupom impresso ao cliente.

---

## 6. 🔀 Fluxos Alternativos

- **FA01 - Reimpressão de Segunda Via do Cupom Fiscal:**
  1. O cliente solicita uma segunda via ou a primeira via saiu com falha de impressão.
  2. O operador clica em `[F11] Reemitir DANFE NFC-e`.
  3. O sistema despacha a segunda via com a marca d'água *"2ª VIA"*.

---

## 7. ⚠️ Fluxos de Exceção

- **FE01 - Papel Preso ou Esgotado na Impressora:**
  1. A impressora reporta status de erro de alimentação.
  2. O sistema emite alerta visual no caixa, permite a troca da bobina e disponibiliza o botão *"Repetir Impressão"*.

---

## 8. 📜 Regras de Negócio Aplicadas

- **RN012 (Documentação Obrigatória):** A guarda do cupom fiscal emitido é a garantia do cliente para futuros acionamentos de garantia, troca ou devolução (RMA).

---

## 9. 🖥️ Interface & Campos de Entrada/Saída

### Entradas:
- Payload XML da NFC-e autorizada.

### Saídas:
- Cupom fiscal NFC-e impresso fisicamente com QR Code oficial de conferência.
