# Registro de Modificações IA

---

### Correção de Nomenclatura no Registro (Registry): system/library/cart/cart.php

- **Implementação:** Alteração da chave de busca de `$registry->get('repository')` para `$registry->get('alpha_repository_factory')` dentro do construtor da biblioteca legada de Cart. 
- **Motivo:** O arquivo já não possuía dependências do antigo `$this->load`, porém referenciava a injeção da fábrica de repositórios com uma chave desatualizada/incompatível com o Bootstrap (`cron.php` / `startup.php`) da Alpha Engine.
- **Benefício:** Evita *Fatal Errors* causados por `null` durante a resolução do `CartRepository` no construtor. O arquivo agora é 100% interoperável e atua com segurança como ponte proxy entre a arquitetura Alpha e extensões de terceiros que ainda esperam que `$this->cart` exista no OpenCart.