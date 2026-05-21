# Estratégia de Carregamento de Pedidos (Eager vs Lazy)

Esta é uma excelente forma de visualizar a eficiência do motor de dados da Alpha Engine. Em sistemas complexos. O carregamento indiscriminado de dados (Eager) pode causar gargalos de performance, enquanto o carregamento sob demanda (Lazy) economiza recursos ao adiar consultas SQL para coleções que nem sempre são necessárias na visualização atual.

## Detalhamento da Estratégia de Hidratação

### 1. Order (Aggregate Root)
É o ponto de entrada. O `DataAccessObject` utiliza o **Identity Map** aqui para garantir que a instância seja única na memória durante o ciclo de vida da requisição.

### 2. Eager Loading (Carregamento Imediato)
*   **OrderProduct & OrderTotal:** São essenciais para qualquer operação com o pedido. Sem eles, o objeto estaria em um estado "anêmico" e incompleto para a maioria das regras de negócio.
*   **Customer:** Carregado de forma imediata para garantir que o contexto de quem realizou a compra esteja sempre disponível.

### 3. Lazy Loading (Carregamento Tardio)
*   **OrderHistory:** Implementado via `LazyCollection`. A consulta SQL para a tabela `order_history` só é disparada se o código chamar `$order->getHistory()`. Isso torna a listagem de pedidos no administrativo muito mais rápida.
*   **Addresses:** Vinculados ao `Customer`. Um cliente recorrente pode ter um histórico vasto de endereços; carregá-los no momento do login ou da visualização de um pedido simples seria um desperdício de I/O.

## Vantagem Arquitetural

Ao utilizar atributos como `fetch: 'LAZY'` nas Entidades, o seu `DataAccessObject` automatiza a injeção de proxies ou coleções preguiçosas, removendo a necessidade de escrever lógica manual de "se precisar, busque no banco" dentro dos seus Mappers.

---
*Documentação técnica da Alpha Engine.*

> **Dica:** Esse modelo garante que o consumo de memória da Alpha Engine permaneça linear, mesmo com milhares de pedidos processados!
classDiagram
    title Estratégia de Carregamento: Eager vs Lazy Loading (Order)

    class Order {
        <<Aggregate Root>>
        -id: int
        -total: float
        +getProducts() EAGER
        +getTotals() EAGER
        +getCustomer() EAGER
        +getHistory() LAZY
    }
    note for Order "Configuração via Attributes: A estratégia é definida na Entidade usando #[OneToMany(fetch: 'LAZY')], permitindo controle granular da performance."

    class OrderProduct {
        <<Eager Entity>>
        -name: string
        -price: float
    }
    note for OrderProduct "EAGER: Carregado via JOIN ou Batch no momento em que o Pedido é recuperado. Necessário para exibir o carrinho/checkout."

    class OrderTotal {
        <<Eager Entity>>
        -title: string
        -value: float
    }
    note for OrderTotal "EAGER: Parte fundamental da integridade financeira do pedido. Sempre carregado com o pai."

    class Customer {
        <<Eager Entity>>
        -email: string
        +getAddresses() LAZY
    }
    note for Customer "EAGER: Dados de perfil são carregados imediatamente para identificar o comprador."

    class Address {
        <<Lazy Entity>>
        -address1: string
        -city: string
    }
    note for Address "LAZY: O cliente pode ter dezenas de endereços. São carregados apenas se o usuário acessar a gestão de endereços ou checkout."

    class OrderHistory {
        <<Lazy Entity>>
        -comment: string
        -dateAdded: DateTime
    }
    note for OrderHistory "LAZY: O histórico de status pode ser longo. Evita-se o carregamento na listagem de pedidos, disparando a query apenas na tela de detalhes."

    Order *-- OrderProduct : (Eager)
    Order *-- OrderTotal : (Eager)
    Order o-- Customer : (Eager)
    Order *-- OrderHistory : (Lazy)
    Customer *-- Address : (Lazy)