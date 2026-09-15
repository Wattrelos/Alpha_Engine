# UC_POS_003 - Verificar Disponibilidade de Estoque (POS)

## 📋 Informações do Caso de Uso

| Atributo | Detalhe |
| :--- | :--- |
| **Identificador** | `UC_POS_003` |
| **Nome** | Verificar Disponibilidade de Estoque |
| **Módulo** | Ponto de Venda (POS) - Módulo Vendedor |
| **Atores Primários** | Sistema Alpha Engine POS |
| **Atores Secundários** | Vendedor de Balcão (*Sales Representative*) |
| **Tipo** | Inclusão de `UC_POS_001` (`<<include>>`) / Consulta em Tempo Real |
| **Frequência de Uso** | Contínua / Muito Alta |
| **Rastreabilidade** | **RF:** [RF006](/docs/requirements/functional/functional_requirements.yaml) (Gestão automática e saldo de estoque)<br>**RN:** [RN005](/docs/requirements/business_rules/business_rules.yaml) (Controle rigoroso de estoque em tempo real), [RN006](/docs/requirements/business_rules/business_rules.yaml) (Alerta de baixo estoque)<br>**RNF:** [RNF002](/docs/requirements/non_functional/non_functional_requirements.yaml) (Consulta de saldo < 50ms) |

---

## 1. 🎯 Descrição Sumária
Invocado automaticamente durante a navegação e digitação no POS para consultar o saldo físico real do produto na loja, distinguindo o saldo disponível para pronta entrega no balcão, saldo reservado em outras comandas abertas e saldo armazenado no Centro de Distribuição (CD) com indicação do endereço da prateleira/corredor.

---

## 2. ⚡ Pré-Condições
- Identificador do produto (`product_id` ou SKU) informado na interface do POS.

---

## 3. ✅ Pós-Condições
- Informações de estoque em tempo real disponibilizadas para o vendedor orientar o cliente.

---

## 4. 🚀 Gatilho (Trigger)
O vendedor consulta um produto ou bipa um código no balcão.

---

## 5. 🔄 Fluxo Principal (Caminho Feliz)

1. **Sistema:** Recebe o `product_id` consultado no balcão.
2. **Sistema:** Executa consulta na tabela `tbkk_product` com `SELECT quantity, location, min_stock_threshold FROM tbkk_product WHERE product_id = ?`.
3. **Sistema:** Abate as unidades que já estão comprometidas em pré-vendas pendentes aguardando pagamento no caixa (`status = 'pending'`).
4. **Sistema:** Retorna o saldo líquido disponível e a localização do depósito (ex: *"Local: Corredor C, Prateleira 4 | Saldo Disponível: 85 unidades"*).
5. **Sistema:** Se o saldo for inferior ao limite mínimo de segurança, exibe alerta visual de estoque crítico (RN006).

---

## 6. 🔀 Fluxos Alternativos

- **FA01 - Consulta de Saldo em Outras Filiais / Depósito Central:**
  1. O produto está com estoque zerado na loja física atual.
  2. O sistema exibe o saldo disponível nas filiais mais próximas e o prazo de transferência de estoque (ex: *"Disponível no CD Central: 500 un - Chega em 24h"*).

---

## 7. ⚠️ Fluxos de Exceção

- **FE01 - Estoque Zerado / Indisponível:**
  1. O produto possui saldo líquido zero.
  2. O sistema bloqueia a adição do item à pré-venda e sugere produtos substitutos de especificação equivalente (ex: cimento de outra marca com mesma resistência).

---

## 8. 📜 Regras de Negócio Aplicadas

- **RN005 (Estoque Rigoroso):** Impossibilidade de venda de mercadorias sem lastro físico em estoque.
- **RN006 (Alerta de Baixo Estoque):** Notificação visual no terminal quando o saldo atinge o ponto de pedido.

---

## 9. 🖥️ Interface & Campos de Entrada/Saída

### Entradas:
- `product_id`.

### Saídas:
- `available_quantity`, `reserved_quantity`, `shelf_location`, `warning_level`.
