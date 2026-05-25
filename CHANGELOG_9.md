# Registro de Modificações IA

---

### Modernização de Infraestrutura: Startups e Events (Alpha Engine)

- **Implementação:** Criação das entidades `Startup` e `Event`, mapeadores `StartupMapper` e `EventMapper`, e repositórios `StartupRepository` e `EventRepository`. Orientações repassadas para refatoração dos controladores de pre-action (`startup/startup.php` e `startup/event.php`).
- **Motivo:** O log do sistema (capturador) alertou sobre o carregamento de modelos legados (`setting/startup` e `setting/event`) rodando em *loop* ou no carregamento de rotas simples como `account/login`. Como esses componentes são *pre-actions* nativas do OpenCart que disparam em absolutamente todas as rotas (carregando extensões e registrando eventos), seu uso pelo model legado poluía o log de depreciação e dependia do motor SQL antigo.
- **Benefício:** A adoção da arquitetura Alpha para os Startups e Events permite que o cacheamento seja feito através do *Identity Map* e elimina completamente os "Warnings" de depreciação de *Legacy Models* do log. Isso agiliza o bootstrap da aplicação e a injeção inicial de dependências do OpenCart.