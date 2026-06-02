---
### Alpha Engine: Refatoração Skinny Controller (Localisation/Country)
---
**Data:** [Data Atual]
**O que foi feito:**
- Refatoração completa do controller `catalog/controller/localisation/country.php`.
- Remoção da instanciação estática de `CountryMapper` (que ignorava injeções de dependência) e substituição pela chamada formal via `RepositoryFactory`.
- Delegação de formatações e malabarismos de chaves de array (remoção de conversão genérica) para os métodos *DTO-factory* (`getCountry` e `getZonesByCountryId`) dos repositórios.
**Benefícios:** Consistência com o padrão Skinny Controller. O JSON entregue ao Javascript do checkout passa a herdar diretamente o cache O(1) do Repositório e respeitará a tradução do idioma ativo do usuário. Além disso, previne quebra de layout na hora de injetar as Tags HTML das zonas.
