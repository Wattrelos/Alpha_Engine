---
### Alpha Engine: Implementação de Entidades de Domínio Faltantes (CMS & Infra)
---
**Data:** [Data Atual]
**O que foi feito:**
- Criação em massa das Entidades (`Entities`) que constavam no `db_schema.php` mas estavam ausentes na nova arquitetura PHP 8.4 estrita.
- Entidades criadas: `Antispam`, `ApiHistory`, `ArticleComment`, `ArticleRating`, `ArticleToLayout` e `ArticleToStore`.
- Injeção de relacionamentos `#[ManyToOne]` permitindo auto-hidratação via Reflection sem quebrar pelo uso de falsos nulos em chaves estrangeiras.
- Tipagem de atributos (`int`, `bool`, `string`) com inicialização de valor padrão (`= 0`, `= false`, `= ''`) visando previnir `Fatal Error: Uninitialized Property`.
**Benefícios:** Consolidada a base para modernização completa da área do Blog/Conteúdo e proteção ativa contra falhas de Type Hinting no DAO, garantindo previsibilidade durante o mapeamento ORM.

- Injeção de relacionamentos `#[ManyToOne]` permitindo auto-hidratação via Reflection sem quebrar pelo uso de falsos nulos em chaves estrangeiras.
- Tipagem de atributos (`int`, `bool`, `string`) com inicialização de valor padrão (`= 0`, `= false`, `= ''`) visando previnir `Fatal Error: Uninitialized Property`.
**Benefícios:** Consolidada a base para modernização completa da área do Blog/Conteúdo e proteção ativa contra falhas de Type Hinting no DAO, garantindo previsibilidade durante o mapeamento ORM.
