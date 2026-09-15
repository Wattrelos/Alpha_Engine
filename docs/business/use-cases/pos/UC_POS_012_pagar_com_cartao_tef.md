# UC_POS_012 - Pagar com Cartão via TEF (POS)

## 📋 Informações do Caso de Uso

| Atributo | Detalhe |
| :--- | :--- |
| **Identificador** | `UC_POS_012` |
| **Nome** | Pagar com Cartão via TEF (Crédito/Débito) |
| **Módulo** | Ponto de Venda (POS) - Módulo Caixa |
| **Atores Primários** | Cliente Presencial (*Customer*), Operador de Caixa (*Cashier*) |
| **Atores Secundários** | PinPad Físico / Gerenciador TEF (SiTef/Stone), Adquirente |
| **Tipo** | Especialização de `UC_POS_010` (Generalização de Pagamento) |
| **Frequência de Uso** | Muito Alta |
| **Rastreabilidade** | **RF:** [RF018](/docs/requirements/functional/functional_requirements.yaml) (Multi-meios de pagamento), [RF019](/docs/requirements/functional/functional_requirements.yaml) (Integração de pagamento seguro)<br>**RNF:** [RNF003](/docs/requirements/non_functional/non_functional_requirements.yaml) (Criptografia de ponta a ponta e normas PCI-DSS) |

---

## 1. 🎯 Descrição Sumária
Especializa a liquidação financeira no caixa quando o cliente opta por pagar com Cartão de Crédito ou Cartão de Débito, comunicando-se com o PinPad através do protocolo TEF (*Transferência Eletrônica de Fundos*), gerenciando a inserção/aproximação do cartão (NFC), digitação de senha, escolha de parcelas e captura do comprovante transacional de autorização (NSU).

---

## 2. ⚡ Pré-Condições
- Terminal TEF ativo e PinPad conectado.

---

## 3. ✅ Pós-Condições
- Transação com cartão autorizada pela adquirente com código de autorização e NSU gravados.

---

## 4. 🚀 Gatilho (Trigger)
O operador pressiona `[F2] Cartão Débito` ou `[F3] Cartão Crédito` no terminal.

---

## 5. 🔄 Fluxo Principal (Caminho Feliz)

1. **Ator:** Pressiona `[F3] Cartão Crédito` e seleciona o número de parcelas desejado pelo cliente (ex: *3x sem juros*).
2. **Sistema:** Envia o comando de transação para o módulo TEF com o valor exato a ser cobrado.
3. **PinPad:** Exibe no visor *"INSIRA, PASSE OU APROXIME O CARTÃO"*.
4. **Cliente:** Insere ou aproxima seu cartão físico e digita a senha pessoal de segurança no teclado do PinPad.
5. **PinPad / TEF:** Criptografa os dados e envia para a adquirente via canal seguro.
6. **Sistema:** Recebe a resposta de aprovação com o NSU (ex: `NSU: 482910`) e comprovante de autorização.
7. **Sistema:** Registra a quitação no terminal e avança no fluxo do caixa.

---

## 6. 🔀 Fluxos Alternativos

- **FA01 - Pagamento por Aproximação (Contactless / NFC):**
  1. O cliente aproxima o smartphone ou smartwatch no PinPad.
  2. A transação é autorizada imediatamente sem necessidade de inserção física.

---

## 7. ⚠️ Fluxos de Exceção

- **FE01 - Transação Negada / Saldo Insuficiente (Retentativa no TEF):**
  1. A operadora retorna *"Transação Não Autorizada - Saldo Insuficiente"*.
  2. O sistema exibe o motivo no monitor do caixa e permite até **3 retentativas** utilizando outro cartão ou alterando a modalidade para PIX/Dinheiro.
- **FE02 - PinPad Desconectado / Falha de Comunicação:**
  1. O cabo USB do PinPad soltou.
  2. O sistema alerta *"PinPad não responde. Verifique a conexão do cabo"* e oferece botão para reestabelecer o canal TEF.

---

## 8. 📜 Regras de Negócio Aplicadas

- **RNF003:** Isolamento completo de dados de cartões conforme certificação PCI-DSS.

---

## 9. 🖥️ Interface & Campos de Entrada/Saída

### Entradas:
- Modalidade (Débito/Crédito), número de parcelas e senha no PinPad.

### Saídas:
- `tef_nsu`, `authorization_code`, `card_brand` e comprovante impresso.
