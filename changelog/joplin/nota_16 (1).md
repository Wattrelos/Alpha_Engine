---
### Alpha Engine: Implementação dos Mappers ProductOption e ProductOptionValue
---
**Data:** [Data Atual]
**O que foi feito:**
- Criação das classes `ProductOptionMapper` e `ProductOptionValueMapper` no namespace `Alpha\Mappers\EntityMappers`.
- Extensão da classe `BaseMapper` para herdar o comportamento CRUD padronizado e a integração com o `DataAccessObject` (DAO).
- Configuração das propriedades estritas `$table` e `$entityClass` apontando para as respectivas tabelas e entidades de domínio.
**Benefícios:** Integração total da gestão de variações de produtos com o motor ORM da Alpha Engine. A herança do `BaseMapper` garante que a extração e a hidratação das opções de produto e seus valores (ex: tamanhos, cores, e seus acréscimos de preço) ocorram de forma padronizada e previsível, alimentando corretamente os Repositórios sem a necessidade de reescrever consultas SQL manuais.
