# Alpha Engine - Catálogo, Conteúdo e SEO
**Módulo de Taxonomia e Exibição**

Este log documenta o saneamento das entidades que compõem a vitrine e a estrutura de dados do catálogo.

## 🚀 [CATALOG] Otimização e Normalização

1.  **Batch Loading em ProductMapper**: Recuperação massiva de produtos e descrições em uma única operação SQL, eliminando o problema de *N+1 queries*.
2.  **Taxonomia Completa**: Árvore lógica conectando Categorias, Produtos, Fabricantes, Opções e Atributos de forma tipada (PHP 8.4).
3.  **Pricing Logic Consolidation**: Unificação de cálculos dinâmicos de descontos (Percentual vs Fixo) diretamente no Mapper.
4.  **Traffic Auditing**: Migração do registro de visualizações para a entidade `ProductReport` via DAO.
5.  **Advertising Engine**: Implementação de `Banner` e `BannerImage` com suporte multi-idioma e cache de identidade.
6.  **Stock Status**: Exibição localizada de estados de inventário sincronizada com o fluxo de pedidos.

## 🔍 [SEO] Inteligência de Busca e Metadados

1.  **Schema.org JSON-LD**: Geração automática de dados estruturados para Breadcrumbs e Listagens no `CategoryRepository`.
2.  **Paginação SEO**: Implementação de links `rel="canonical"`, `rel="next"` e `rel="prev"` centralizados no repositório.
3.  **SeoUrl Mapper**: Suporte a Cache Estático de Lookup para resolver slugs em lote, reduzindo latência em listagens.
4.  **Breadcrumb Integration**: Resolução recursiva de paths integrada ao CategoryMapper para links amigáveis lineares.

## 📝 [CMS] Gestão de Conteúdo e Blog

1.  **Articles & Topics**: Normalização das entidades de blog com `ArticleDescription` e `TopicDescription` para suporte multi-idioma nativo.
2.  **Information Pages**: Modernização de páginas institucionais com vínculos diretos a layouts e lojas específicas.

## 🛠️ Vitórias Técnicas de Catálogo
1.  **CategoryMapper Modernizado**: Transição para `c.id` como referência principal e lógica de filtros otimizada via subqueries.
2.  **Product Entity (PHP 8.4)**: Tipagem estrita de preços e estoques para evitar falhas matemáticas em cálculos fiscais.
3.  **Recursividade de Opções**: DAO configurado para carregar `Option -> OptionValue -> Description` em uma única árvore de objetos ricos.
4.  **SEO Automation**: `SeoUrlMapper` com suporte a Cache de Lookup, resolvendo centenas de slugs em listagens sem sobrecarga de banco.
5.  **Review System**: Refatoração completa com moderação nativa via objetos de domínio.
6.  **Atributos Técnicos**: Hierarquia de `AttributeGroup` mapeada para hidratação automática de fichas técnicas.
7.  **ManufacturerMapper**: Centralização da lógica de marcas com suporte a SEO URLs nativas.
8.  **Articles & Topics**: Suporte multi-idioma para Blog/CMS com gerenciamento de metatags isolado.
9.  **Refatoração do InformationRepository**: Remoção de métodos de busca redundantes (find/findAll), herdando comportamento da base.
10. **Refatoração do ManufacturerRepository**: Consolidação da persistência via `AbstractRepository` e remoção de código repetitivo.
11. **Refatoração do CategoryRepository**: Eliminação de redundâncias de busca e centralização da lógica de taxonomia recursiva e SEO estruturado.
12. **Lazy Loading em Categorias**: Refatoração do `getTree` para utilizar `LazyCollection`, otimizando a memória ao carregar menus aninhados.
13. **Master Pattern em Controladores**: Migração completa dos controladores de `Categoria` e `Busca` para atuar via `BaseController`, consumindo ViewResponses padronizadas e delegando a extração de dados aos Repositórios.
14. **ViewResponse**: Padronização da resposta DTO para os controladores de catálogo, com suporte fluente e alias `getData()` para facilitar a interoperabilidade com templates.


### 💡 Insights de Persistência
*   A inclusão explícita de campos de descrição no `ProductMapper` resolve o erro histórico de vitrines vazias.
*   O uso de `float` no preço não é estético, é uma trava de segurança para o motor de impostos e frete.
*   O carregamento de categorias agora utiliza o método `readByIds` para evitar o problema de *N+1 queries* em menus complexos.

---
*Trabalho em constante evolução para elevar o padrão de engenharia do catálogo.*