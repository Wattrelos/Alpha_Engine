---
### 🛠️ Vitórias Técnicas de Catálogo
---
1.  **CategoryMapper Modernizado:**
-  Transição para `c.id` como referência principal e lógica de filtros otimizada via subqueries.
2.  **Product Entity (PHP 8.4):**
-  Tipagem estrita de preços e estoques para evitar falhas matemáticas em cálculos fiscais.
3.  **Recursividade de Opções:**
-  DAO configurado para carregar `Option -> OptionValue -> Description` em uma única árvore de objetos ricos.
4.  **SEO Automation:**
-  `SeoUrlMapper` com suporte a Cache de Lookup, resolvendo centenas de slugs em listagens sem sobrecarga de banco.
5.  **Review System:**
-  Refatoração completa com moderação nativa via objetos de domínio.
6.  **Atributos Técnicos:**
-  Hierarquia de `AttributeGroup` mapeada para hidratação automática de fichas técnicas.
7.  **ManufacturerMapper:**
-  Centralização da lógica de marcas com suporte a SEO URLs nativas.
8.  **Articles & Topics:**
-  Suporte multi-idioma para Blog/CMS com gerenciamento de metatags isolado.
9.  **Refatoração do InformationRepository:**
-  Remoção de métodos de busca redundantes (find/findAll), herdando comportamento da base.
10. **Refatoração do ManufacturerRepository:**
-  Consolidação da persistência via `AbstractRepository` e remoção de código repetitivo.
11. **Refatoração do CategoryRepository:**
-  Eliminação de redundâncias de busca e centralização da lógica de taxonomia recursiva e SEO estruturado.
12. **Lazy Loading em Categorias:**
-  Refatoração do `getTree` para utilizar `LazyCollection`, otimizando a memória ao carregar menus aninhados.
13. **Master Pattern em Controladores:**
-  Migração completa dos controladores de `Categoria` e `Busca` para atuar via `BaseController`, consumindo ViewResponses padronizadas e delegando a extração de dados aos Repositórios.
14. **ViewResponse:**
-  Padronização da resposta DTO para os controladores de catálogo, com suporte fluente e alias `getData()` para facilitar a interoperabilidade com templates.
