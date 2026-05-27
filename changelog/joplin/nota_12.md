---
### Refatoração de Performance: Otimização do Cabeçalho Global (Header)
---
**Implementação:**
- Aplicação do método `renderFragment()` no controlador de cabeçalho (`catalog/controller/common/header.php`) para envelopar e realizar o cache PSR-16 do sub-componente `Menu`. O retorno da classe também foi migrado do antigo `$this->load->view()` para a engine segura `$this->viewRenderer->render()`.
**Motivo:**
- O menu de departamentos do OpenCart exige intenso processamento recursivo no banco de dados (Category Repository) para montar a árvore multinível em todas as requisições de página, causando latência no TTFB. Além disso, o cabeçalho precisava adotar a proteção contra *White Screen of Death* implementada hoje.
**Benefício:**
- Adoção dupla de segurança e velocidade. A árvore de categorias agora é lida do banco de dados e processada no Twig apenas 1 vez por hora, sendo o HTML final entregue instantaneamente a partir da memória nas demais milhares de visitas. O uso do `ViewRenderer` garante que eventuais quebras visuais no cabeçalho não travem o carregamento do restante do *body* da loja.

