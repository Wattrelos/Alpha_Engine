
---
### Alpha Engine: Refatoração de Endpoints Dinâmicos (Cart Controller)
---
**Data:** [Data Atual]
**O que foi feito:**
- Refatoração dos métodos `add`, `edit` e `remove` do controller `checkout/cart.php`.
- Substituição das exclusões manuais de sessão (`unset`) pela chamada unificada aos métodos de orquestração do `CartRepository` (`addAndClearCheckout`, `updateAndClearCheckout` e `removeAndClearCheckout`).
- Remoção da redundância e formatações inseguras, adotando estritamente `$this->jsonResponse()` para os retornos AJAX em conjunto com a injeção nativa `$this->loadLanguage()`.
**Benefícios:** Limpeza profunda de "Spaghetti Code" no controller; Garantia de que ao manipular itens no carrinho via API ou View, as sessões voláteis do checkout (fretes e pagamentos escolhidos) são rigorosamente resetadas pela camada de Domínio, evitando inconsistência de valores antigos e brechas lógicas.
