# 🚀 Alpha Engine - Documentação Técnica

Bem-vindo ao repositório central da Alpha Engine. Este projeto implementa um sistema de e-commerce robusto baseado em **Repository Pattern**, **Data Mappers** e **Domain-Driven Design (DDD)**.

## 📑 Índice de Documentação

Para facilitar a navegação, a documentação detalhada foi dividida nos seguintes módulos dentro da pasta `docs/`:

### 1. Arquitetura Geral
Visão geral da estrutura de pastas em `core/`, separação de camadas e princípios de design aplicados.

### 2. Modelos de Domínio e Entidades
Detalhamento das árvores de agregação e grafos de objetos:
*   **Catálogo:** Produtos, Categorias e Fabricantes. ([Ver Diagrama](docs/diagrams/catalog_tree.mmd))
*   **Vendas:** Estrutura de Pedidos e Carrinho. ([Ver Diagrama](docs/diagrams/sales_tree.mmd))
*   **Clientes:** Gestão de perfis e endereçamento. ([Ver Diagrama](docs/diagrams/customer_tree.mmd))
*   **Fluxo de Persistência:** Sequência de salvamento. ([Ver Diagrama](docs/diagrams/order_persistence_sequence.mmd))

### 3. Infraestrutura e Banco de Dados
Explicação técnica sobre o `DataAccessObject` (DAO), abstração de transações aninhadas e segurança com PDO. (Ver Diagrama)

### 4. Persistência (Mappers & Repositories)
Fluxo de salvamento e recuperação de dados, incluindo o ciclo de vida de uma entidade do domínio até o banco de dados.

### 5. Guia de Diagramas
Instruções para visualizar e editar os diagramas PlantUML (`.puml`) localizados na pasta `diagrams/`.

---
*Nota: Este índice foi gerado para organizar o conteúdo distribuído. Os arquivos `.md` mencionados acima devem ser mantidos em sincronia com as evoluções do código em `core/`.*
