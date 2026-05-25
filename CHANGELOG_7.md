# Registro de Modificações IA

---

### Correção de Erro Fatal: Undefined Method em Mappers e Repositórios

- **Implementação:** Substituição em massa das chamadas de métodos inexistentes `findBy()` e `findOneBy()` por `search()` no `AddressRepository`, `CustomerGroupRepository`, `CustomFieldRepository`, `CountryRepository`, `CurrencyRepository`, `CustomerTokenRepository`, `CustomerAffiliateRepository`, `CustomerAuthorizeRepository` e `ApiIpRepository`.
- **Implementação:** Ajuste de nomenclatura de propriedade (`$table` para `$tableName`) nos Mappers herdados de `BaseMapper` (`AddressMapper`, `ApiIpMapper`, `ApiHistoryMapper`, `CustomerRewardMapper`, `AttributeGroupMapper`, `AttributeMapper`, `CustomerAuthorizeMapper`).
- **Motivo:** A abstração do ORM (`BaseMapper`) fornece a função `search()` para extração de entidades em vez dos legados `findBy` / `findOneBy`. A invocação incorreta causou o Fatal Error relatado no carrinho de compras (originado ao tentar injetar as zonas de impostos da Entidade de Endereço). Além disso, várias classes mantinham a propriedade desatualizada `$table` impedindo que a reflexão de queries pelo DataAccessObject funcionasse.
- **Benefício:** Restaura a visibilidade da página inicial, checkout e carrinho, estabiliza todo o ecossistema de Repositórios injetados e impede que exceções idênticas derrubem a aplicação em outras páginas (como o painel da conta do cliente).

---

### Correção de Erro de Sintaxe SQL: Escapamento de Palavras Reservadas no ORM (`BaseMapper`)

- **Implementação:** Adicionado o caractere de acento grave (backtick `` ` ``) ao redor dos nomes das colunas gerados dinamicamente nas cláusulas `WHERE` e `ORDER BY` nos métodos `search` e `paginate` da classe `Alpha\Mappers\BaseMapper`.
- **Motivo:** Ao buscar o endereço padrão no Carrinho (`default` => true), o método convertia a chave para a coluna `default`. Como `default` é uma palavra restrita (reservada) no MariaDB/MySQL, a omissão das crases disparava uma `PDOException` (`Syntax error or access violation: 1064`), quebrando a renderização do header.
- **Benefício:** Restaura o cálculo visual de impostos atrelado ao endereço do usuário e garante resiliência estrutural ao DataAccessObject, permitindo a extração dinâmica via QueryBuilder contra qualquer tabela que utilize colunas com nomes restritos na engine SQL.