# Registro de Modificações IA (Sessão 11)

---

### Refatoração de DTOs: HomeData Desacoplada do DAO
**Data:** [Data Atual]
**O que foi feito:**
- A classe `HomeData.php` (movida para `core/Model/Domain/`) teve sua dependência da classe abstrata `BaseEntity` removida, juntamente com a sua declaração `use` redundante.
- A classe foi recriada como um Data Transfer Object (DTO) puro, implementando nativamente a interface `\JsonSerializable` e o método `toArray()`.
**Benefícios:**
- Previne falhas de persistência. Como `HomeData` não mapeia uma tabela no banco, herdar de `BaseEntity` a expunha ao risco de ser processada pelo Motor ORM (`DataAccessObject`), o que geraria um erro fatal. O código agora fica semanticamente correto (DDD) e mais leve em memória (sem os tratamentos dinâmicos de métodos mágicos ou `$id` de entidades).