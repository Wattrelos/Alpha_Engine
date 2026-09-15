# UC_ADM_003 - Gerenciar Pedidos & Faturamento

## 📋 Informações do Caso de Uso

| Atributo | Detalhe |
| :--- | :--- |
| **Identificador** | `UC_ADM_003` |
| **Nome** | Gerenciar Pedidos, Expedição & Faturamento |
| **Módulo** | Painel Administrativo - Operações de Negócio |
| **Atores Primários** | Operador do Painel (*Operator*), Administrador Geral (*Admin*) |
| **Atores Secundários** | Servidor da SEFAZ, Transportadoras de Carga Pesada, Sistema Alpha Engine |
| **Tipo** | Condução / Gestão Operacional de Vendas |
| **Frequência de Uso** | Contínua / Muito Alta |
| **Rastreabilidade** | **RF:** [RF016](/docs/requirements/functional/functional_requirements.yaml) (Histórico), [RF020](/docs/requirements/functional/functional_requirements.yaml) (Faturamento/NF-e), [RF022](/docs/requirements/functional/functional_requirements.yaml) (Last-mile tracking)<br>**RN:** [RN005](/docs/requirements/business_rules/business_rules.yaml) (Estoque), [RN012](/docs/requirements/business_rules/business_rules.yaml) (Documentação fiscal NF-e), [RN013](/docs/requirements/business_rules/business_rules.yaml) (Notificação de atrasos e rastreamento)<br>**RNF:** [RNF001](/docs/requirements/non_functional/non_functional_requirements.yaml) (Visualização em Kanban/Tabela de pedidos) |

---

## 1. 🎯 Descrição Sumária
Permite aos operadores acompanhar a esteira completa de processamento de pedidos originados do e-commerce e do PDV, atualizar status de separação e expedição, emitir notas fiscais eletrônicas (NF-e Modelo 55), gerar guias de transporte de carga pesada, inserir códigos de rastreamento logístico e despachar notificações automáticas de acompanhamento aos clientes.

---

## 2. ⚡ Pré-Condições
- Operador autenticado com permissão no módulo `sale/order`.

---

## 3. ✅ Pós-Condições
- Status do pedido atualizado no banco de dados com histórico gravado em `tbkk_order_history`.
- NF-e autorizada pela SEFAZ e e-mail com rastreio enviado ao comprador.

---

## 4. 🚀 Gatilho (Trigger)
O operador acessa "Vendas > Pedidos" no painel administrativo.

---

## 5. 🔄 Fluxo Principal (Faturar e Despachar Pedido)

1. **Ator:** Acessa a lista de pedidos e filtra por status `Pago / Pronto para Faturamento`.
2. **Ator:** Clica em "Visualizar / Faturar Pedido" no pedido `#10542`.
3. **Sistema:** Carrega o detalhamento da compra: comprador, endereço de entrega de obra, itens, peso total e forma de envio.
4. **Ator:** Clica no botão "Emitir NF-e".
5. **Sistema:** Monta o XML da NF-e com os impostos calculados (ICMS/PIS/COFINS), assina digitalmente e transmite para a SEFAZ (RF020).
6. **SEFAZ:** Retorna a autorização e chave de acesso de 44 dígitos.
7. **Sistema:** Anexa o DANFE em PDF e o XML ao pedido e altera o status para `Faturado / Em Separação no Depósito`.
8. **Ator:** Após o carregamento do caminhão, insere o código de rastreamento da transportadora e altera o status para `Em Transporte`.
9. **Sistema:** Dispara e-mail e SMS ao cliente com o link de rastreamento em tempo real (RF022 / RN013).

---

## 6. 🔀 Fluxos Alternativos

- **FA01 - Cancelamento de Pedido com Estorno de Estoque:**
  1. O operador comanda o cancelamento de um pedido não pago ou a pedido do cliente.
  2. O sistema estorna as quantidades reservadas de volta ao estoque físico ativo (RN005).

---

## 7. ⚠️ Fluxos de Exceção

- **FE01 - Rejeição de NF-e pela SEFAZ (Erro de NCM ou Inscrição Estadual):**
  1. A SEFAZ rejeita a nota fiscal por inconsistência tributária.
  2. O sistema exibe o código do erro (ex: *"Rejeição 539: Duplicidade de NF-e"*), mantém o pedido no status de pendência e permite a correção dos campos fiscais.

---

## 8. 📜 Regras de Negócio Aplicadas

- **RN005 & RN012 & RN013:** Rigor na baixa de estoque, emissão fiscal e transparência no rastreio da entrega.

---

## 9. 🖥️ Interface & Campos de Entrada/Saída

### Entradas:
- Ações de alteração de status, dados de nota fiscal e código de rastreamento.

### Saídas:
- DANFE gerado, XML armazenado e timeline do pedido atualizada.
