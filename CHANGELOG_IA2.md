## 🌍 Orquestração Automática de Associações (Países, Zonas e Descrições)
**Data:** [Data Atual]

**O que foi feito:**
- Criação do `CountryRepository` na camada de Domínio, eliminando de vez a necessidade do modelo legado `localisation/country`.
- Mapeamento no `AlphaContainer` para que todos os acessos legados sejam injetados via Repository Pattern.
- Verificação da integridade do ORM (`DataAccessObject`): Como a Entidade `Country` agora possui o atributo `#[OneToMany]` para Zonas e Descrições, o simples ato do Mapper instanciar um `Country` engatilha o `processAssociations` no ORM, buscando automaticamente os arrays dependentes em um fluxo otimizado.

**Benefícios Técnicos:**
1. **Desacoplamento Rigoroso**: O repositório e o mapper não contêm nenhuma linha de `JOIN` manual para descrições. As estruturas de relacionamento são completamente invisíveis, definidas apenas por regras estruturais da Entidade PHP 8.4.
2. **Identity Map e O(1) Performance**: Com a camada de cache persistente implantada no Repositório, as requisições constantes de listagem de países e resolução de checkout/frete ocorrem com taxa zero de queries SQL após a primeira carga.
3. **Segurança de Tipos Estrita**: Com coleções padronizadas, métodos como `getName()` do país ou as repetições sobre as zonas vão beneficiar-se dos analisadores estáticos da Alpha Engine, reduzindo crashes não mapeados.

---

## 📄 Criação da Entidade CountryDescription
**Data:** [Data Atual]

**O que foi feito:**
- Implementação da entidade `CountryDescription`, garantindo que as propriedades `$countryId`, `$languageId` e `$name` estejam corretamente tipadas.
- Mapeamento exato do método `setCountryId()` para casar perfeitamente com a configuração `foreignKey: "countryId"` declarada no atributo `#[OneToMany]` da Entidade `Country`. Isso assegura a hidratação bidirecional (via ORM DataAccessObject) sem "mágica" oculta.