# Grafos e Mapeamento de Entidades

Aqui, demonstra-se como o uso de **PHP 8.4 Attributes** nos diagramas de classe ajuda a desmistificar como o *DataAccessObject* (DAO) consegue a "mágica" de persistir e recuperar dados sem que o desenvolvedor precise escrever uma única linha de SQL manual dentro das entidades.

Explicações de como atributos como #[Table], #[Column], #[ManyToOne] e #[OneToMany] servem de metadados para que o motor Alpha Engine automatize a tradução entre o mundo de objetos (camelCase) e o banco de dados (snake_case).

### Por que essas adições são importantes:

1.  **Explicação do Mapeamento**: A nota no `catalog_tree.mmd` explica a "tradução" automática. O desenvolvedor define `private string $model` na entidade e o atributo `#[Column]` diz ao DAO que isso corresponde a `model` no banco, cuidando de tipos e segurança.
2.  **Inteligência Relacional**: No `customer_tree.mmd`, fica claro que o DAO não precisa de JOINs manuais para carregar o grupo de um cliente; ele lê o atributo de relacionamento e executa a busca recursiva de forma transparente.
3.  **Segurança e Padronização**: No `sales_tree.mmd`, reforçamos que o uso de *Attributes* blinda o sistema contra *SQL Injection*, pois o DAO utiliza esses metadados para construir *Prepared Statements* automaticamente.

---
*Esses detalhes ajudam a conectar a visualização das classes com o comportamento real do motor de persistência.*