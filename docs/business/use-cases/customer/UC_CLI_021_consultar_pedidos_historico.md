# UC_CLI_021 - Consultar Pedidos & Histórico

## 📋 Informações do Caso de Uso

| Atributo | Detalhe |
| :--- | :--- |
| **Identificador** | `UC_CLI_021` |
| **Nome** | Consultar Pedidos & Histórico |
| **Módulo** | Loja Virtual - Área "Minha Conta" |
| **Atores Primários** | Cliente Logado (*Customer*) |
| **Atores Secundários** | Sistema Alpha Engine |
| **Tipo** | Condução / Consulta |
| **Frequência de Uso** | Alta |
| **Rastreabilidade** | **RF:** [RF016](/docs/requirements/functional/functional_requirements.yaml) (Histórico de pedidos), [RF020](/docs/requirements/functional/functional_requirements.yaml) (Faturamento/NF-e), [RF022](/docs/requirements/functional/functional_requirements.yaml) (Last-mile tracking)<br>**RN:** [RN012](/docs/requirements/business_rules/business_rules.yaml) (Documentação fiscal e DANFE), [RN013](/docs/requirements/business_rules/business_rules.yaml) (Rastreamento e comunicação)<br>**RNF:** [RNF001](/docs/requirements/non_functional/non_functional_requirements.yaml) (Clareza visual) |

---

## 1. 🎯 Descrição Sumária
Permite ao cliente autenticado consultar a listagem histórica e os detalhes completos de todos os seus pedidos realizados (`/account/orders` e `/account/orders/{id}`), visualizando o status atual da compra através de uma linha do tempo gráfica (*timeline*), código de rastreamento da transportadora em tempo real, download de documentos fiscais (DANFE/XML da NF-e) e botão de recompra rápida.

---

## 2. ⚡ Pré-Condições
- Cliente autenticado na sessão.

---

## 3. ✅ Pós-Condições
- Visualização detalhada do pedido, seus itens, valores discriminados e dados de entrega.

---

## 4. 🚀 Gatilho (Trigger)
O cliente clica em "Meus Pedidos" ou "Histórico de Compras" no painel da conta.

---

## 5. 🔄 Fluxo Principal (Caminho Feliz)

1. **Ator:** Acessa `/account/orders`.
2. **Sistema:** Consulta os pedidos associados ao `customer_id` na tabela `tbkk_order` com paginação.
3. **Sistema:** Renderiza a lista de pedidos contendo: Número do Pedido (`#10542`), Data da Compra, Quantidade de Produtos, Total em Reais e Badge de Status (ex: `Aguardando Pagamento`, `Em Separação`, `Em Transporte`, `Entregue`).
4. **Ator:** Clica no botão "Ver Detalhes" do pedido desejado.
5. **Sistema:** Renderiza a página `/account/orders/info?order_id=10542` contendo:
   - Linha do tempo visual do status do pedido;
   - Código de rastreamento com link direto para o site da transportadora/Correios (RF022);
   - Endereço de entrega da obra e modalidade de frete selecionada;
   - Tabela de itens com miniatura, descrição, unidade m²/cx, quantidade e valor;
   - Botões para Download do **DANFE (PDF)** e **XML da Nota Fiscal** (RF020);
   - Botão para Solicitar Devolução / RMA (`UC_CLI_023`).
6. **Ator:** Faz o download do DANFE ou acompanha a entrega.

---

## 6. 🔀 Fluxos Alternativos

- **FA01 - Recomprar Pedido Anterior (1-Clique):**
  1. O cliente clica no botão "Comprar Novamente".
  2. O sistema adiciona todos os itens ativos daquele pedido anterior diretamente ao carrinho de compras vigente.

---

## 7. ⚠️ Fluxos de Exceção

- **FE01 - Pedido Não Pertence ao Cliente Logado (Tentativa de IDOR):**
  1. O usuário tenta acessar a URL de um pedido alterando o parâmetro `order_id` para o de outro cliente.
  2. O sistema valida que `order.customer_id != session.customer_id`, bloqueia o acesso com HTTP 403 e registra o evento no log de auditoria de segurança.

---

## 8. 📜 Regras de Negócio Aplicadas

- **RN012 (Documentação Obrigatória):** Disponibilização de arquivos da Nota Fiscal Eletrônica.
- **RN013 (Comunicação de Status):** Atualização transparente de cada etapa logística.

---

## 9. 🖥️ Interface & Campos de Entrada/Saída

### Entradas:
- `order_id`.

### Saídas:
- Timeline gráfica de status, listagem discriminada de produtos, link de rastreio e botões de download fiscal.
