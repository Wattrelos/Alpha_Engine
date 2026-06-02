---
### Refatoração: Padronização do Motor de View (Anti-WSOD) nos Componentes Parciais
---
**Implementação:**
- Substituição das chamadas legadas `$this->load->view()` por `$this->viewRenderer->render()` nos controladores de parciais `cart.php`, `footer.php`, `menu.php`, `cookie.php`, `language.php`, `currency.php` e `search.php`.
**Motivo:**
- O método `load->view` original do OpenCart carece de um tratamento robusto de exceções para falhas de sintaxe no Twig, o que pode desencadear uma Tela Branca (WSOD) e matar a execução inteira do *script*.
**Benefício:**
- Centraliza a emissão de HTML no motor proprietário da Alpha Engine (`ViewRenderer`). Se um componente individual falhar na camada da View, o erro será capturado elegantemente e o restante da página continuará responsivo. Padronização total da herança do `BaseController`.# Registro de Modificações IA
