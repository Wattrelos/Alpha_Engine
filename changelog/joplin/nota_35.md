---
### Isolamento de Classes Utilitárias (Log Personalizado)
---
**Data:** [Data Atual]
**O que foi feito:**
- A classe `Log.php` foi fisicamente movida da pasta `/core/Model/Domain/Entities/` para `/LogsPersonalizados/`.
- O namespace foi alterado para `LogsPersonalizados` e a herança da classe abstrata `BaseEntity` foi removida, já que essa classe não representa uma tabela real no OpenCart 4.
**Benefícios:**
- Garante o padrão DDD (*Domain-Driven Design*) no core da Alpha Engine. Classes puramente de serviço, utilitárias ou criadas fora da malha do OpenCart não poluem os diretórios restritos do motor ORM, prevenindo a quebra de mapeamento e falhas de leitura do *DataAccessObject*.
