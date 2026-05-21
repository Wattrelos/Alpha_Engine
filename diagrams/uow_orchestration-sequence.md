# Orquestração Unit of Work (UoW)

O diagrama de orquestração do Unit of Work (UoW) visualiza como a Alpha Engine resolve o problema clássico de inconsistência entre vendas e estoque.

Neste diagrama, destaco como o UoW serve como um "guarda-chuva" transacional. Ele garante que o OrderMapper e o StockMapper (representado pela lógica de inventário no sistema) trabalhem em harmonia: se a reserva de estoque falhar, o pedido não é persistido; se o pedido apresentar erro, o estoque não é alterado.

## Por que este diagrama é vital para o projeto:

*   **Visualização da Atomicidade:** Ele mostra claramente que o commit real só acontece no final do processo, protegendo o banco de dados contra estados parciais (ex: pedido salvo sem baixa de estoque).
*   **Transações Aninhadas:** O uso das notas sobre o `transactionCounter` do DAO explica visualmente como o UoW evita que múltiplas chamadas de `beginTransaction` quebrem a conexão PDO.
*   **Segurança de Negócio:** A seção `alt/else` detalha o comportamento em caso de falha, o que é fundamental para depuração e para garantir que o setor financeiro e o logístico estejam sempre em sincronia.

---
*Documentação técnica da Alpha Engine.*

> **Lembrar:** Criar um diagrama similar para o fluxo de Cupom de Desconto, que também exige orquestração entre o histórico de uso e os totais do pedido!