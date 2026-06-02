---
### Bugfix: Invalidação de Cache Envenenado (Cache Poisoning) na Página Inicial
---
**Implementação:**
- Alteração dos prefixos das chaves de cache no método `renderPosition` (`BaseController`) e nas injeções de produtos no `Home` de `layout_pos` para `layout_pos_v2` e `home_latest_v2`.
**Motivo:**
- Durante os testes da refatoração anterior, a visualização da Home Page causou a persistência de arrays cru de configuração de módulos (o erro de array-to-string) na memória RAM/Disco. Como o método `$this->remember()` tem um TTL de 1 hora, ele ignorava as correções lógicas e continuava a servir os arrays corrompidos exclusivamente para a rota `common/home`, resultando em módulos não renderizados (div vazia).
**Benefício:**
- A mudança na assinatura da chave do cache impõe um expurgo imediato. A Alpha Engine abandona a memória suja e reavalia a estrutura de blocos processando-os como HTML genuíno. A página inicial recupera instantaneamente seus Banners, Destaques e demais blocos configurados no Layout.

