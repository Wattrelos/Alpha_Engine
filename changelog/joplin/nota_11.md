---
### Refatoração: Implementação do Cache de Fragmento (PSR-16) no BaseController
---
**Implementação:**
- Inclusão do método `renderFragment($cacheKey, $generator, $ttl)` em `Alpha\Controller\BaseController`. Este método utiliza a estratégia de cache disponível para interceptar e envelopar a execução de HTML pesado. Aplicação imediata no auto-carregamento do `footer` dentro do método `render()`, configurado com TTL de 24 horas.
**Motivo:**
- A renderização de certos blocos visuais constantes (como rodapés, menus em árvore de departamentos) requer que a aplicação passe por validação de Controladores, carregamento de Modelos/Repositórios, leitura de páginas institucionais no banco e renderização através do motor Twig em *todas* as requisições, gerando alto custo de CPU e latência (Time to First Byte - TTFB).
**Benefício:**
- Permite que desenvolvedores "congelem" blocos HTML da interface. Ao envelopar lógicas custosas (ex: `return $this->viewRenderer->render('meu/menu_complexo')`) dentro do `renderFragment`, o resultado processado da View é mantido no driver de Cache e devolvido diretamente em $O(1)$ na próxima visita. A aplicação automática no `footer` já resulta em redução imediata de *queries* SQL relacionadas às páginas de informação exibidas no rodapé.

