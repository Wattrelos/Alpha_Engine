# Registro de Modificações IA

---

### Correção de Boot: Método findAll no Controlador de Startup de Extensões

- **Implementação:** Substituição da chamada legada `$extensionRepo->getExtensions()` por `$extensionRepo->findAll()` no controlador `catalog/controller/startup/extension.php`.
- **Motivo:** O erro *Call to undefined method* ocorreu porque, na refatoração anterior do `ExtensionRepository` (CHANGELOG 22), padronizamos os métodos de busca para aderir à interface `BaseRepositoryInterface` da Alpha Engine, removendo o método customizado e fora do padrão `getExtensions()`. O *startup action* do OpenCart foi esquecido e ainda tentava invocar a nomenclatura antiga.
- **Benefício:** Restaura a sequência de inicialização (Pre-Actions). O controlador de *startup* passa a extrair todas as extensões ativas corretamente da memória/banco de dados através da abstração correta, carregando os autoloaders de módulos sem interromper o fluxo da aplicação.