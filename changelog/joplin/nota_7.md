---
### Refatoração: Integração do ViewRenderer no BaseController
---
**Implementação:**
- Injeção e instanciação da classe `Alpha\System\ViewRenderer` dentro do construtor de `Alpha\Controller\BaseController`. O método final de renderização `render()` foi atualizado para delegar o processamento de HTML ao novo motor, aposentando definitivamente a chamada legada `$this->load->view()`.
**Motivo:**
- O controlador mestre dita o comportamento de todos os outros controladores do sistema. Era fundamental que o método facilitador `render()` deixasse de utilizar a via propensa a falhas silenciosas (*White Screen of Death*), centralizando a segurança em um único ponto da arquitetura.
**Benefício:**
- A partir de agora, qualquer tela renderizada na loja (que já estenda o `BaseController`) passa a estar automaticamente protegida contra crashes de buffer do Twig. Erros serão interceptados, gravados em log físico e a propagação de tela branca em produção é drasticamente mitigada sem a necessidade de reescrever controladores filhos individuais.

