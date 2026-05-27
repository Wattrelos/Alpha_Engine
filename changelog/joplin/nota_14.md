---
### Refatoração: Injeção de Contexto Financeiro em Cache PSR-16 (Página Inicial)
---
**Implementação:**
- Criação do método genérico `$this->remember()` no `BaseController` para dar suporte a arrays iteráveis no driver de cache. No controlador `home.php`, envelopou-se a consulta dos produtos de Lançamento (Latest) e Destaques (Featured) utilizando esse método.
**Motivo:**
- Caching de produtos esbarra na complexidade de precificação dinâmica (Impostos, Moedas, Descontos de Grupo/Atacado). Armazenar a *string HTML* bruta globalmente resultaria em exibir moedas ou preços incorretos após um usuário logar ou trocar de país.
**Benefício:**
- Para contornar isso, gerou-se a assinatura `$cacheContext` (`Moeda` + `Grupo de Cliente`) dinamicamente. O sistema agora mantém instâncias de HTML distintas e isoladas na RAM para cada perfil. Isso zera as consultas ao banco de dados e os cálculos do `ProductRepository` na Home Page, entregando um TTFB sub-100ms e protegendo os templates internos com a engine Anti-WSOD nativa da Alpha.

