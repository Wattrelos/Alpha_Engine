---
### Alpha Engine: Limpeza Absoluta do LanguageMapper e CurrencyMapper
---
**Data:** [Data Atual]
**O que foi feito:**
- `LanguageMapper`: Remoção completa de `QueryBuilder` manuais e execuções diretas de array/SQL. Os métodos `getLanguage`, `getLanguageByCode` e `getLanguages` agora delegam 100% da carga para a herança do `BaseMapper` (`findById`, `findOneBy`, `search`), garantindo uso estrito da hidratação ORM.
- `CurrencyMapper`: Adição da definição obrigatória `$entityClass` para suportar buscas de Entidades futuras, e remoção de uma lógica legada de "Static Cache" (`static $cache = null;`). 
**Benefícios:** Mappers devem ser "estúpidos" e transparentes, limitando-se a traduzir entidades para o banco. O cacheamento passa a ser responsabilidade exclusiva do Repository (`LanguageRepository` e `CurrencyRepository`), isolando corretamente a camada de persistência e a lógica de domínio.
