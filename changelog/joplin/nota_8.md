---
### Refatoração Core: Blindagem Defensiva na Classe Template Nativa
---
**Implementação:**
- Inclusão de bloco `try-catch (\Throwable)` envelopando as instruções `extract` e `include` dentro do método `render()` no arquivo `system/library/template/template.php`. Adição de lógica para expurgo seguro do buffer de saída (`ob_end_clean()`) e gravação de log isolado (`alpha_template_engine_trace.log`).
**Motivo:**
- Anteriormente, o mecanismo nativo do OpenCart disparava as views sem proteção de escopo de execução. Se um template PHP ou Twig convertido contivesse um erro fatal ou violação estrita no PHP 8+, a execução global do PHP era abortada, largando o buffer de saída corrompido na memória e resultando no infame *White Screen of Death (WSOD)*.
**Benefício:**
- Garantia absoluta de estabilidade visual e rastreabilidade. Com esta modificação, a proteção "Anti-WSOD" da Alpha Engine passa a cobrir não apenas os Controllers refatorados (via `ViewRenderer`), mas também extensões legadas, módulos de terceiros e qualquer componente do OpenCart que invoque o renderizador de view original, evitando a quebra silenciosa da loja em produção.

