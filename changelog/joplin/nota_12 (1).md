---
### Alpha Engine: Injeção OneToMany em Zone.php (Zonas e Descrições)
---
**Data:** [Data Atual]
**O que foi feito:**
- Verificação da entidade `Zone` confirmando a existência correta e tipada dos métodos `getCode()`, `getName()` e `getStatus()`.
- Adição do atributo relacional `#[OneToMany(targetEntity: ZoneDescription::class, mappedBy: "zone", foreignKey: "zoneId")]` na propriedade `$descriptions` da entidade `Zone`.
**Benefícios:** Sem essa declaração, o ORM (DataAccessObject) não seria capaz de hidratar automaticamente as traduções (`ZoneDescription`) quando um estado fosse carregado. Agora, o DTO Factory do repositório pode extrair o nome traduzido nativamente e de forma limpa, garantindo a internacionalização dos estados na tela de checkout e painel de administração.
