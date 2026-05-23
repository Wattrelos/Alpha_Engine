---

### Alpha Engine: Relacionamento Bidirecional em Country e CountryDescription
**Data:** [Data Atual]
**O que foi feito:**
- Adição do atributo `#[OneToMany]` na entidade `Country.php` apontando para `CountryDescription`, estabelecendo a árvore de traduções.
- Restauração das propriedades primitivas e chaves estrangeiras `$countryId` e `$languageId` na entidade `CountryDescription.php` com seus respectivos mapeamentos condicionais nos setters e getters.
**Benefícios:** Consistência arquitetural. Ao manter a bidirecionalidade, garantimos que o DAO saiba exatamente como resolver a chave estrangeira na hora da hidratação e evitamos o risco do banco de dados ser atualizado com IDs nulos durante rotinas em cascata (`UnitOfWork`).

---

### Alpha Engine: Criação e Relacionamento de ZoneDescription
**Data:** [Data Atual]
**O que foi feito:**
- Criação da entidade `ZoneDescription.php` tipada para o PHP 8.4, com os devidos mapeamentos `#[ManyToOne]` para `Zone` e `Language`.
- Orientação estrutural para injetar a coleção `descriptions` utilizando `#[OneToMany]` na entidade legada `Zone.php`.
**Benefícios:** Expansão da capacidade de localização e tradução do sistema. Modelar as zonas (Estados/Departamentos) com entidades ricas de tradução garante precisão máxima de idioma na emissão de notas fiscais e relatórios logísticos.

---

### Alpha Engine: Padronização de Compatibilidade Legada (DTO Factory) no Repositório
**Data:** [Data Atual]
**O que foi feito:**
- Refatoração do `CountryRepository` para agir bidirecionalmente. Os métodos da interface Domain (`find`, `findAll`) agora retornam as Entidades `Country` ricas mapeadas pelo ORM.
- Os métodos originais do OpenCart (`getCountry`, `getCountries`) foram adaptados como fábricas de *Legacy DTOs* (`toLegacyDTO`), convertendo as entidades em arrays planos.
- Injeção da lógica de resolução multidioma de `CountryDescription` (via `$this->registry` config) dentro da formatação do Array.
**Benefícios:** Zero refatoração manual exigida nos *Controllers* e *Views* do checkout legado. As telas de carrinho, endereço e cadastro que recebem o Model injetado via `AlphaContainer` continuarão operando com a semântica `foreach ($countries as $country) echo $country['name'];` perfeitamente, unindo a força do DDD à retrocompatibilidade da IU.

---

### Alpha Engine: Strict Type Casting no DTO de Country
**Data:** [Data Atual]
**O que foi feito:**
- Verificação da entidade `Country` e aplicação de *type casting* explícito `(int)` para os booleanos `postcode_required` e `status` no método `toLegacyDTO` do `CountryRepository`.
**Benefícios:** Garante que o array exportado para as *Views* legadas tenha o formato exato `0` ou `1`, prevenindo falhas silenciosas no Twig caso ele tente comparar um booleano nativo com uma string estrita do banco de dados (ex: `'1'`).

---

### Alpha Engine: Criação do ZoneRepository com DTO Factory
**Data:** [Data Atual]
**O que foi feito:**
- Criação do `ZoneRepository.php` aplicando a mesma arquitetura de Compatibilidade Legada (DTO Factory) do `CountryRepository`.
- Implementação dos métodos legados `getZone`, `getZonesByCountryId` e `getZones` convertendo a entidade `Zone` para arrays associativos puros.
- Mapeamento de `'localisation/zone'` no interceptador `AlphaContainer`.
**Benefícios:** A tela de checkout depende fortemente de requisições AJAX para `index.php?route=localisation/country.country` para atualizar os estados (zonas) ao alterar o país. Com o `ZoneRepository` retornando o DTO legível pelo JSON do OpenCart, o checkout moderno da Alpha Engine não sofre crash na renderização das opções (Dropdowns) dos formulários.

---

### Alpha Engine: Injeção OneToMany em Zone.php (Zonas e Descrições)
**Data:** [Data Atual]
**O que foi feito:**
- Verificação da entidade `Zone` confirmando a existência correta e tipada dos métodos `getCode()`, `getName()` e `getStatus()`.
- Adição do atributo relacional `#[OneToMany(targetEntity: ZoneDescription::class, mappedBy: "zone", foreignKey: "zoneId")]` na propriedade `$descriptions` da entidade `Zone`.
**Benefícios:** Sem essa declaração, o ORM (DataAccessObject) não seria capaz de hidratar automaticamente as traduções (`ZoneDescription`) quando um estado fosse carregado. Agora, o DTO Factory do repositório pode extrair o nome traduzido nativamente e de forma limpa, garantindo a internacionalização dos estados na tela de checkout e painel de administração.

---

### Alpha Engine: Refatoração Skinny Controller (Localisation/Country)
**Data:** [Data Atual]
**O que foi feito:**
- Refatoração completa do controller `catalog/controller/localisation/country.php`.
- Remoção da instanciação estática de `CountryMapper` (que ignorava injeções de dependência) e substituição pela chamada formal via `RepositoryFactory`.
- Delegação de formatações e malabarismos de chaves de array (remoção de conversão genérica) para os métodos *DTO-factory* (`getCountry` e `getZonesByCountryId`) dos repositórios.
**Benefícios:** Consistência com o padrão Skinny Controller. O JSON entregue ao Javascript do checkout passa a herdar diretamente o cache O(1) do Repositório e respeitará a tradução do idioma ativo do usuário. Além disso, previne quebra de layout na hora de injetar as Tags HTML das zonas.

---

### Alpha Engine: Auditoria de Conformidade ORM em CountryMapper e ZoneMapper
**Data:** [Data Atual]
**O que foi feito:**
- Inspeção do `CountryMapper.php` e `ZoneMapper.php` confirmando a eliminação total de `JOINs` manuais e hidratações N+1.
- Refatoração do método `getTotalZonesByCountryId` no `ZoneMapper` para substituir strings chumbadas (`DB_PREFIX . 'zone'`) pelo uso correto e encapsulado de `$this->tableName`.
**Benefícios:** Os Mappers geográficos agora atestam o sucesso da refatoração relacional da Alpha Engine. A ausência de queries manuais de relacionamento assegura que qualquer alteração futura nas entidades geográficas será resolvida apenas pelo motor ORM abstrato, sem necessidade de tocar nos Mappers.

---

### Alpha Engine: Limpeza Absoluta do LanguageMapper e CurrencyMapper
**Data:** [Data Atual]
**O que foi feito:**
- `LanguageMapper`: Remoção completa de `QueryBuilder` manuais e execuções diretas de array/SQL. Os métodos `getLanguage`, `getLanguageByCode` e `getLanguages` agora delegam 100% da carga para a herança do `BaseMapper` (`findById`, `findOneBy`, `search`), garantindo uso estrito da hidratação ORM.
- `CurrencyMapper`: Adição da definição obrigatória `$entityClass` para suportar buscas de Entidades futuras, e remoção de uma lógica legada de "Static Cache" (`static $cache = null;`). 
**Benefícios:** Mappers devem ser "estúpidos" e transparentes, limitando-se a traduzir entidades para o banco. O cacheamento passa a ser responsabilidade exclusiva do Repository (`LanguageRepository` e `CurrencyRepository`), isolando corretamente a camada de persistência e a lógica de domínio.