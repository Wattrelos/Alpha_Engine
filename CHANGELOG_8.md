# Registro de Modificações IA

---

### Compatibilidade de SQL Legado: Correção de Primary Keys em `account/order`

- **Implementação:** Refatoração de queries manuais SQL no model legado `catalog/model/account/order.php`, substituindo as menções à antiga coluna `order_id` pela chave primária normalizada `id` (tanto em cláusulas `WHERE` quanto em `ORDER BY` e `JOIN`). Adição de alias `AS order_id` nos `SELECT`s para manter os controladores compatíveis sem quebra de contrato.
- **Motivo:** O banco de dados da Alpha Engine padroniza todas as chaves primárias das tabelas como `id` em vez do formato redudante `nome_da_tabela_id`. Como a página "Histórico de Pedidos" da conta do cliente e o resumo do pedido (info) ainda utilizavam o Model legado, o motor do MySQL disparava o erro fatal `Unknown column 'order_id' in 'ORDER BY'` ao tentar listar os pedidos.
- **Benefício:** Restaura instantaneamente o acesso à aba "Meus Pedidos" e o detalhamento de faturas no painel do cliente, garantindo que o OpenCart nativo consiga conviver em harmonia com as tabelas normalizadas do novo ecossistema ORM enquanto não recebe sua própria refatoração de Controller e Repositório.

---

### Compatibilidade de SQL Legado: Correção de Primary Keys em `account/returns`

- **Implementação:** Refatoração de queries SQL no model legado `catalog/model/account/returns.php`. Substituição da coluna `return_id` por `id` nas cláusulas `WHERE`, `ORDER BY` e projeção `SELECT`. Os joins de tabelas associadas (`return_reason`, `return_action`, `return_status`) também tiveram suas chaves de comparação adaptadas de `nome_tabela_id` para `id`. O alias `AS return_id` foi preservado para o frontend.
- **Motivo:** Garantir interoperabilidade com o novo modelo normalizado de banco de dados da Alpha Engine. A ausência de conversão para a chave genérica `id` disparava erros de "Unknown column" quando o usuário tentava acessar seu histórico de devoluções e garantias.
- **Benefício:** Restaura plenamente o acesso e visualização à seção "Minhas Devoluções" do cliente.

---

### Compatibilidade de SQL Legado: Proteção de Projeção em `account/transaction`

- **Implementação:** Adição do alias `id AS customer_transaction_id` na projeção (`SELECT *`) do método `getTransactions` do model `catalog/model/account/transaction.php`.
- **Motivo:** A tabela `customer_transaction` opera predominantemente baseada em chaves estrangeiras (`customer_id`, `order_id`), que não tiveram seus nomes alterados na Alpha Engine, tornando as cláusulas `WHERE` naturalmente imunes a quebras. No entanto, para evitar que controladores legados falhem silenciosamente ao procurar pela chave primária original em listagens ou links, o alias foi injetado de forma preventiva.
- **Benefício:** Torna o modelo de transações financeiras 100% imune a problemas de compatibilidade reversa com o frontend legado, estabilizando o painel do usuário.