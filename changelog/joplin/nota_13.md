---
### Bugfix e Refatoração Core: ViewRenderer e Cache de Fragmento
---
**Implementação:**
- No `Alpha\System\ViewRenderer`, substituiu-se a instanciação obsoleta `new \Template()` pelo uso do serviço já inicializado `$this->registry->get('template')`, compatibilizando o despachador com a assinatura de renderização do OpenCart 4 (`render($route, $data, $code)`).
**Motivo:**
- O *WSOD-Catcher* do ViewRenderer brilhou e exibiu seu primeiro log em ambiente de desenvolvimento. O OpenCart 4 descontinuou a classe global `Template` em favor do namespace e removeu o método legados `$template->set()`, causando exceção *Class Not Found* na chamada manual.
**Benefício:**
- A engine de apresentação volta a compilar o HTML adequadamente preservando todo o setup de diretórios injetado durante o boot do framework.
---
**Implementação:**
- Modificação da lógica condicional no método `renderFragment` em `Alpha\Controller\BaseController` para aceitar estritamente retornos onde `is_string($output)` seja verdadeiro.
**Motivo:**
- Disparo do erro de PHP *Warning: Array to string conversion*. Chaves residuais no sistema de cache em arquivo do OpenCart (ou lixo em drivers não PSR-16) retornavam arrays. Ao forçar o typecast `(string)$output`, o compilador gerava ruído e quebrava o JSON e Layouts.
**Benefício:**
- Estabilidade aumentada. A partir de agora, mesmo que o Cache esteja sujo com objetos ou arrays legados, a Alpha Engine descartará essa resposta e gerará um novo HTML limpo e seguro para a interface.

