# Registro de Modificações IA (Sessão 33)

---

### Limpeza de Mapeamento Órfão: GeoZone, Language, Location e Store
**Data:** [Data Atual]
**O que foi feito:**
- Entidade `GeoZone`: Removidos `dateAdded` e `dateModified`.
- Entidade `Language`: Removidos `image` e `directory` (essas colunas eram do OpenCart 2.x/3.x e já foram removidas do schema nativo).
- Entidades `Location` e `Store`: Removidas as propriedades `fax` e `ssl`, respectivamente, que não existem mais na estrutura do banco.
**Benefícios:**
- Impede que o DataMapper tente executar operações de `INSERT` ou `UPDATE` com colunas inexistentes na tabela, eliminando a ocorrência de "Unknown column" em relatórios e logs de erro do banco de dados e concluindo a hidratação fluida.