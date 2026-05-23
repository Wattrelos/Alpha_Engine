---

### Alpha Engine: Refatoração de Endpoints Dinâmicos (Cart Controller)
**Data:** [Data Atual]
**O que foi feito:**
- Refatoração dos métodos `add`, `edit` e `remove` do controller `checkout/cart.php`.
- Substituição das exclusões manuais de sessão (`unset`) pela chamada unificada aos métodos de orquestração do `CartRepository` (`addAndClearCheckout`, `updateAndClearCheckout` e `removeAndClearCheckout`).
- Remoção da redundância e formatações inseguras, adotando estritamente `$this->jsonResponse()` para os retornos AJAX em conjunto com a injeção nativa `$this->loadLanguage()`.
**Benefícios:** Limpeza profunda de "Spaghetti Code" no controller; Garantia de que ao manipular itens no carrinho via API ou View, as sessões voláteis do checkout (fretes e pagamentos escolhidos) são rigorosamente resetadas pela camada de Domínio, evitando inconsistência de valores antigos e brechas lógicas.

---

### Alpha Engine: Refatoração da View do Carrinho (Cart List)
**Data:** [Data Atual]
**O que foi feito:**
- Refatoração dos métodos `list()` e `getList()` no controller `checkout/cart.php`.
- Extracão em massa da lógica condicional pesada (verificações de estoque, cálculos de totais, restrições de preço para visitantes e alertas de sessão volátil) para o método `getCartListDisplayData()` do `CartRepository`.
- Remoção de Models estáticos (`tool/upload` e `tool/image`) da UI, delegando o processamento de imagens ao `ImagePresenter` nativo da Alpha Engine.
**Benefícios:** Transformação do controlador num verdadeiro *Skinny Controller*, aliviando-o de mais de 100 linhas de HTML/Business Logic misturados. O DTO de resposta agora está padronizado via `ViewResponse`, isolando eventuais bugs ou inconsistências matemáticas diretamente na camada de Domínio e habilitando testabilidade unitária dos cálculos do carrinho.

---

### Alpha Engine: Implementação da Entidade CountryDescription
**Data:** [Data Atual]
**O que foi feito:**
- Transformação do esboço da classe `CountryDescription` para o formato restrito do PHP 8.4, alinhado à base de dados legada.
- Adição de relações `#[ManyToOne]` com as classes `Country` e `Language` para permitir a hidratação recursiva pelo EntityMapper.
**Benefícios:** Consistência tipada e viabilização de consultas O(1) quando uma região ou país precisar ser traduzido em listagens de checkout e perfis de clientes.