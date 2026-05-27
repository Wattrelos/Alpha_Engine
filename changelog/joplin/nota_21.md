---
### Refatoração Arquitetural: Delegação de Módulos (Skinny Controller na Home)
---
**Implementação:**
- Remoção total da injeção manual (hardcoded) de *Latest Products*, *Featured Products* e *Banners* do controlador `catalog/controller/common/home.php`. O controlador retorna ao estado de manipulação exclusiva de SEO, Identidade e chamadas de rotas.
**Motivo:**
- Verificou-se que a loja já possuía uma estrutura modular ativa via Painel Administrativo, com blocos alocados na posição `Content Top`. Com a recente implementação do *Fragment Caching* generalizado no método `renderPosition()` do `BaseController`, manter a injeção estrita no controlador causaria sobreposição (duplicação visual de módulos) e engessaria a usabilidade do lojista no painel.
**Benefício:**
- Restabelece o fluxo natural de montagem de layouts do OpenCart permitindo edição livre via drag-and-drop no Admin. O ganho de performance original (Zero Queries e Load Instantâneo) é integralmente mantido, pois a `Alpha Engine` intercepta todas as posições renderizadas antes de enviar ao template e as armazena no Cache PSR-16.

