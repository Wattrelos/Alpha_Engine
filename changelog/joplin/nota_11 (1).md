---
### Alpha Engine: Criação do ZoneRepository com DTO Factory
---
**Data:** [Data Atual]
**O que foi feito:**
- Criação do `ZoneRepository.php` aplicando a mesma arquitetura de Compatibilidade Legada (DTO Factory) do `CountryRepository`.
- Implementação dos métodos legados `getZone`, `getZonesByCountryId` e `getZones` convertendo a entidade `Zone` para arrays associativos puros.
- Mapeamento de `'localisation/zone'` no interceptador `AlphaContainer`.
**Benefícios:** A tela de checkout depende fortemente de requisições AJAX para `index.php?route=localisation/country.country` para atualizar os estados (zonas) ao alterar o país. Com o `ZoneRepository` retornando o DTO legível pelo JSON do OpenCart, o checkout moderno da Alpha Engine não sofre crash na renderização das opções (Dropdowns) dos formulários.
