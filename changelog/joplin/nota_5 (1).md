---
### Alpha Engine: Implementação de Entidades de Configuração, Design e Catálogo (Pivot)
---
**Data:** [Data Atual]
**O que foi feito:**
- Criação das entidades `BannerImage`, `CategoryFilter`, `CategoryPath` e `CustomFieldCustomerGroup`.
- Entidades de tabelas "pivot" (tabelas de cruzamento sem chave serial primária `id`) foram adaptadas para estender de `BaseEntity` utilizando adequadamente os atributos de relacionamento `#[ManyToOne]`.
- Tipagem estrita de propriedades não relacionais (`sortOrder`, `level`, `required`) com valores *default* seguros para compatibilidade direta com hidratação de formulários da interface e extrações do banco de dados (DAO).
**Benefícios:** Mapeamento consistente de relacionamentos "Muitos Para Muitos" e caminhos hierárquicos (Category Path), mitigando falhas estruturais causadas por chaves e instâncias indefinidas e reforçando a fundação para a listagem e organização do catálogo de produtos e exibição de banners no front-end.

- Entidades de tabelas "pivot" (tabelas de cruzamento sem chave serial primária `id`) foram adaptadas para estender de `BaseEntity` utilizando adequadamente os atributos de relacionamento `#[ManyToOne]`.
- Tipagem estrita de propriedades não relacionais (`sortOrder`, `level`, `required`) com valores *default* seguros para compatibilidade direta com hidratação de formulários da interface e extrações do banco de dados (DAO).
**Benefícios:** Mapeamento consistente de relacionamentos "Muitos Para Muitos" e caminhos hierárquicos (Category Path), mitigando falhas estruturais causadas por chaves e instâncias indefinidas e reforçando a fundação para a listagem e organização do catálogo de produtos e exibição de banners no front-end.
