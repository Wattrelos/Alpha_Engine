---
### Bugfix e Refatoração de Performance: Renderização de Posições e Fragment Caching (Layouts)
---
**Implementação:**
- O método `renderPosition(string $position)` em `Alpha\Controller\BaseController` foi inteiramente reescrito. Agora ele itera sobre os arrays de configuração dos módulos (oriundos do banco de dados), carrega os respectivos *settings* via `model_setting_module` e invoca o controlador secundário responsável pela renderização (`$this->load->controller()`). Adicionalmente, todo o escopo de execução foi blindado com o novo método `$this->remember()`, aplicando cache PSR-16 inteligente.
**Motivo:**
- O método anterior repassava o array de configuração cru para as views (`content_top`, `column_left`, etc.), o que provocava o erro `PHP Warning: Array to string conversion` em `Template.php` e a exibição indesejada da palavra "Array" na tela sempre que o Twig tentava imprimir a variável `{{ module }}`.
**Benefício:**
- Restaura a exibição de todos os módulos de layout (como Banners, Produtos Recentes na lateral e afins) convertendo as configurações em HTML autêntico. A injeção do cache salva dezenas de instâncias de `load->controller()` em cada carregamento, armazenando o HTML de todos os módulos centrais da página na memória (separados rigorosamente por Rota, Moeda e Grupo de Cliente). O resultado é um ganho massivo de performance estrutural para toda a loja.

