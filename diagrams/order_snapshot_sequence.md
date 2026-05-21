# Snapshot Pattern: Integridade Histórica de Pedidos

O Snapshot Pattern é um dos pilares de um e-commerce confiável. Sem ele, se um lojista alterasse o preço de um produto hoje, todos os pedidos feitos no passado teriam seus totais alterados retroativamente no banco de dados, o que seria um desastre jurídico e contábil.

Aqui está o novo diagrama detalhando esse processo:

> **INTEGRIDADE:** O pedido preserva o valor original (Snapshot), garantindo a segurança contábil da transação, independentemente de alterações futuras no catálogo.

## Por que esse diagrama é importante:

*   **Diferenciação de Responsabilidade:** Mostra que o `Product` (Catálogo) é mutável, enquanto o `OrderProduct` (Venda) é imutável após a criação.
*   **Segurança de Negócio:** Explica por que duplicamos dados (preço e nome) em vez de apenas linkar o ID do produto. Se linkássemos apenas o ID, o nome do produto no pedido mudaria se o lojista corrigisse um erro de digitação no catálogo, por exemplo.
*   **Auditoria:** Facilita o entendimento de como o sistema reconstrói um pedido de dois anos atrás exatamente como ele foi fechado.

---
*Documentação técnica da Alpha Engine.*