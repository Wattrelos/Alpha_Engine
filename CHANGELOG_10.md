# Registro de Modificações IA

---

### Modernização de Infraestrutura: Extensões no Startup (Alpha Engine)

- **Implementação:** Criação das classes `ExtensionRepository` e `ExtensionMapper`. Refatoração completa da pre-action `catalog/controller/startup/extension.php` para herdar de `BaseController` e utilizar o motor Alpha. Auditoria no `startup/session.php` confirmando ausência de gargalos.
- **Motivo:** O controlador encarregado de registrar o *autoloader* das extensões da loja ainda engatilhava o modelo legado (`setting/extension`), disparando conexões desnecessárias ao banco de dados e os alertas de depreciação do OpenCart. Além disso, consumia memória ao invés de buscar os dados do *Cache/Identity Map* nas chamadas de sistema global.
- **Benefício:** Adoção unificada da Alpha Engine nas Pre-Actions (`startup`, `event`, `extension`). As informações de carregamento da aplicação agora estão 100% hospedadas em memória/cache acelerado, eliminando completamente a latência de N+1 Queries no momento inicial do bootstrap e silenciando o log de erros e avisos da loja.