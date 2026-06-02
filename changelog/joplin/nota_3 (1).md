---
### Alpha Engine: Implementação da Entidade CountryDescription
---
**Data:** [Data Atual]
**O que foi feito:**
- Transformação do esboço da classe `CountryDescription` para o formato restrito do PHP 8.4, alinhado à base de dados legada.
- Adição de relações `#[ManyToOne]` com as classes `Country` e `Language` para permitir a hidratação recursiva pelo EntityMapper.
**Benefícios:** Consistência tipada e viabilização de consultas O(1) quando uma região ou país precisar ser traduzido em listagens de checkout e perfis de clientes.

- Transformação do esboço da classe `CountryDescription` para o formato restrito do PHP 8.4, alinhado à base de dados legada.
- Adição de relações `#[ManyToOne]` com as classes `Country` e `Language` para permitir a hidratação recursiva pelo EntityMapper.
**Benefícios:** Consistência tipada e viabilização de consultas O(1) quando uma região ou país precisar ser traduzido em listagens de checkout e perfis de clientes.
