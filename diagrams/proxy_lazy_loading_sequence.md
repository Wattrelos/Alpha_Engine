# Proxy Pattern: Lazy Loading e Performance

O Proxy Pattern é a técnica que permite à Alpha Engine manter a alta performance, evitando o carregamento de grafos de objetos gigantescos quando apenas os dados da entidade principal são necessários.

Em uma relação ManyToOne (como um Produto que pertence a um Fabricante), o Mapper não busca os dados do fabricante imediatamente se a estratégia for LAZY. Em vez disso, ele injeta um objeto "fantasma" (o Proxy) que contém apenas o ID. A consulta ao banco de dados só é disparada se, e somente se, o código tentar acessar uma propriedade desse fabricante.

## O que este padrão revela sobre a arquitetura:

*   **Interceptação de Chamada:** O Proxy sobrescreve os métodos da entidade original para verificar o estado de carregamento antes de retornar o valor.
*   **Eficiência de Memória:** Durante a fase inicial, o objeto Proxy ocupa pouquíssimo espaço, pois não carrega strings longas ou blobs de imagem do banco.
*   **Transparência:** Para o Client (Service ou Controller), não há diferença perceptível entre uma entidade real e um Proxy. Ele simplesmente chama os métodos e recebe o dado, independentemente de quando a query ocorreu.
*   **Integração com DAO:** O Proxy utiliza a infraestrutura de consulta da Alpha Engine, garantindo que mesmo o carregamento tardio passe pelas camadas de segurança e logs do sistema.

---
*Documentação técnica da Alpha Engine.*

> **Dica:** O carregamento LAZY é o padrão recomendado para associações que nem sempre são exibidas em listagens, evitando o "problema do N+1" quando bem configurado.