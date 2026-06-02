---
### Alpha Engine: Padronização de Compatibilidade Legada (DTO Factory) no Repositório
**Data:** [Data Atual]
**O que foi feito:**
- Refatoração do `CountryRepository` para agir bidirecionalmente. Os métodos da interface Domain (`find`, `findAll`) agora retornam as Entidades `Country` ricas mapeadas pelo ORM.
- Os métodos originais do OpenCart (`getCountry`, `getCountries`) foram adaptados como fábricas de *Legacy DTOs* (`toLegacyDTO`), convertendo as entidades em arrays planos.
- Injeção da lógica de resolução multidioma de `CountryDescription` (via `$this->registry` config) dentro da formatação do Array.
**Benefícios:** Zero refatoração manual exigida nos *Controllers* e *Views* do checkout legado. As telas de carrinho, endereço e cadastro que recebem o Model injetado via `AlphaContainer` continuarão operando com a semântica `foreach ($countries as $country) echo $country['name'];` perfeitamente, unindo a força do DDD à retrocompatibilidade da IU.
