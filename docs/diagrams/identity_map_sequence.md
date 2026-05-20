# Identity Map: Cache de Primeiro Nível e Consistência

O padrão Identity Map é essencial para a performance da Alpha Engine, pois atua como um cache de primeiro nível durante o ciclo de vida de uma requisição. Ele garante que, se você solicitar o mesmo Produto ou Cliente várias vezes em lugares diferentes do código, o sistema não "onere" o banco de dados repetidamente, retornando a instância que já reside na memória.

Isso também resolve o problema de consistência: se você alterar o nome de um objeto em uma parte do sistema, essa alteração é refletida em todas as outras referências, pois todas apontam para o mesmo endereço de memória.

> **CONSISTÊNCIA:** Ambas as chamadas retornam a mesma referência de memória. A segunda consulta ao banco foi totalmente evitada.

## Por que este padrão é fundamental na Alpha Engine:

*   **Redução de I/O:** Em listagens complexas onde o mesmo objeto (ex: um Fabricante ou um Status de Pedido) aparece várias vezes, o Identity Map reduz drasticamente o número de queries SQL.
*   **Integridade de Referência:** Evita que existam duas versões diferentes do "mesmo" objeto na memória, o que poderia causar bugs onde você atualiza uma instância, mas a outra continua com dados antigos.
*   **Suporte a Lazy Loading:** Facilita a resolução de dependências circulares, permitindo que o DAO saiba que já está processando um objeto antes de entrar em um loop infinito de hidratação.

## Funcionamento

O `DataAccessObject` protege o MySQL de trabalho repetitivo ao verificar se o ID do objeto solicitado já consta no mapa de identidades antes de realizar o `SELECT`.

---
*Documentação técnica da Alpha Engine.*

> **Dica:** Este padrão trabalha em conjunto com o Unit of Work para garantir que as alterações em memória sejam persistidas corretamente ao final da transação.