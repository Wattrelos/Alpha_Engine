# UC_CLI_022 - Consultar Extrato & Transações

## 📋 Informações do Caso de Uso

| Atributo | Detalhe |
| :--- | :--- |
| **Identificador** | `UC_CLI_022` |
| **Nome** | Consultar Extrato & Transações |
| **Módulo** | Loja Virtual - Área "Minha Conta" |
| **Atores Primários** | Cliente Logado (*Customer*) |
| **Atores Secundários** | Sistema Alpha Engine |
| **Tipo** | Condução / Financeiro |
| **Frequência de Uso** | Baixa a Média |
| **Rastreabilidade** | **RF:** [RF016](file:///var/www/html/agsonhos/docs/requirements/functional/functional_requirements.yaml) (Histórico de transações), [RF018](file:///var/www/html/agsonhos/docs/requirements/functional/functional_requirements.yaml) (Pagamentos)<br>**RN:** [RN016](file:///var/www/html/agsonhos/docs/requirements/business_rules/business_rules.yaml) (Descontos e liquidação)<br>**RNF:** [RNF003](file:///var/www/html/agsonhos/docs/requirements/non_functional/non_functional_requirements.yaml) (Transparência financeira e segurança) |

---

## 1. 🎯 Descrição Sumária
Permite ao cliente autenticado consultar o histórico financeiro consolidado de sua conta (`/account/transaction`), incluindo créditos de devolução/estorno concedidos pela loja, pagamentos registrados por pedido, abatimentos promocionais e saldo atual disponível para utilização em novas compras.

---

## 2. ⚡ Pré-Condições
- Cliente autenticado na sessão.

---

## 3. ✅ Pós-Condições
- Exibição da tabela cronológica de movimentações financeiras com saldo de crédito acumulado.

---

## 4. 🚀 Gatilho (Trigger)
O cliente clica em "Minhas Transações" ou "Extrato de Créditos" no menu da conta.

---

## 5. 🔄 Fluxo Principal (Caminho Feliz)

1. **Ator:** Acessa `/account/transaction`.
2. **Sistema:** Consulta os lançamentos vinculados ao cliente na tabela `tbkk_customer_transaction`.
3. **Sistema:** Renderiza o extrato contendo:
   - Saldo Total de Créditos Disponíveis em Reais (ex: *"Saldo Atual: R$ 250,00"*);
   - Tabela com Data do Lançamento, Descrição da Operação (ex: *"Crédito por Devolução RMA #42"* ou *"Pagamento Pedido #10540"*), Valor (Positivo/Negativo) e Número do Pedido de referência.
4. **Ator:** Visualiza o detalhamento de suas movimentações financeiras.

---

## 6. 🔀 Fluxos Alternativos

- **FA01 - Utilização de Crédito no Checkout:**
  1. O cliente possui R$ 250,00 de crédito em conta.
  2. Ao finalizar uma nova compra no checkout (`UC_CLI_009`), o sistema oferece a opção de abater o saldo de créditos do valor total a pagar.

---

## 7. ⚠️ Fluxos de Exceção

- **FE01 - Nenhum Lançamento Encontrado:**
  1. O cliente não possui histórico de créditos ou devoluções.
  2. O sistema exibe: *"Você ainda não possui transações ou créditos registrados em sua conta."*

---

## 8. 📜 Regras de Negócio Aplicadas

- **RN009 & RN012:** Registro fiel de todo e qualquer reembolso, estorno ou crédito originado de chamados de devolução homologados.

---

## 9. 🖥️ Interface & Campos de Entrada/Saída

### Entradas:
- Nenhuma (consulta automática por `customer_id`).

### Saídas:
- Card com saldo de créditos e tabela cronológica de débitos e créditos com paginação.
