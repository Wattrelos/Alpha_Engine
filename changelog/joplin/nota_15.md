---
### Correção de Duplicidade de Código: Controlador Home
---
**Implementação:**
- Limpeza de blocos de código duplicados no controlador `catalog/controller/common/home.php`, removendo a declaração repetida das variáveis de contexto de cache (`$currencyCode`, `$customerGroupId`, `$cacheContext`) e do bloco de produtos em destaque (`featured_products`).
**Motivo:**
- Durante a inserção manual (copy-paste) da implementação do método `$this->remember()`, trechos do código foram colados duas vezes.
**Benefício:**
- Mantém o arquivo limpo, enxuto e evita que a mesma query de Destaques seja processada de forma redundante caso o cache estivesse vazio, prevenindo bugs lógicos e economizando processamento.

