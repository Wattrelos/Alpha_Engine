---
### Alpha Engine: Refatoração O(1) do CartRepository (Fim da Regra Legada)
---
**Data:** [Data Atual]
**O que foi feito:**
- Injeção direta de `ProductOptionValueRepository` e `ProductDiscountRepository` dentro do `CartRepository::getProducts()`.
- O bloco estático de "Fallback Legado" (que executava queries dentro de loop `foreach`) foi desidratado e inteiramente substituído pela busca inteligente `getOptionValuesByIds` com complexidade $O(1)$.
- Delegação estrita do recálculo de preço progressivo para a memória: cruzamento atômico da propriedade `$item['quantity']` com a coleção de `ProductDiscount` extraída do banco.
**Benefícios:** Performance incomparável e precisão financeira extrema. O OpenCart legado frequentemente falhava ao tentar aplicar descontos progressivos (desconto ativado ao colocar "x" unidades no carrinho), pois o modelo atômico antigo muitas vezes baseava-se em quantidade "1" por ser uma query pré-compilada. Agora, as entidades do Domínio avaliam em tempo real o que o usuário escolheu e aplicam modificadores de imposto, desconto e peso através de Objetos seguros (`ProductOptionValue` e `ProductDiscount`).
