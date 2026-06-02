---
### Refatoração de Resiliência: Páginas Institucionais (Information e Contact)
---
**Implementação:**
- Substituição de `$this->load->view()` por `$this->viewRenderer->render()` no método `info()` de `information.php`. Correção da assinatura de retorno (`return type`) do método `index()` no `contact.php` de `string` para `?\Opencart\System\Engine\Action`.
**Motivo:**
- 1. A rota `information/information.info` (frequentemente usada para carregar termos de aceite via AJAX em popups de checkout) ainda utilizava o motor nativo, correndo risco de WSOD.
- 2. O controlador de contato tentava retornar o resultado de `$this->render()` como `string`, porém, o método da `BaseController` é tipado estritamente como `void`. Isso geraria um `TypeError` (Erro Fatal) no PHP 8.4 ao renderizar a página de contato.
**Benefício:**
- Consistência de tipagem restabelecida, garantindo que a página de contato carregue perfeitamente. O carregamento de páginas institucionais em modais de aceite do checkout agora está 100% blindado contra falhas de template.# Registro de Modificações IA
