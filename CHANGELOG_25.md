# Registro de Modificações IA

---

### Refatoração: Controladores de Cupom e Recompensa na Alpha Engine

- **Implementação:** Refatoração dos módulos de finalização de compra `coupon.php` e `reward.php` (da extensão OpenCart Checkout) para herdar a classe `Alpha\Controller\BaseController`. As chamadas redundantes de formatação JSON em `save()` e `remove()` foram trocadas por `$this->jsonResponse()`. As views agora contam com a injeção automática de textos através do `$this->loadLanguageData()`.
- **Motivo:** O carrinho de compras precisa de coesão em todas as suas etapas (frete, cupom, vale-presentes). As abordagens nativas exigiam injeção de variável a variável ou perdiam escopo, deixando os templates (`.twig`) sem botões traduzidos ou labels em branco.
- **Benefício:** Reduz repetições de código no controlador, padroniza totalmente as respostas em AJAX (prevenindo quebras com a interface assíncrona do minicart) e garante a exibição correta de todos os elementos HTML traduzidos nos painéis modais (accordion) do carrinho.