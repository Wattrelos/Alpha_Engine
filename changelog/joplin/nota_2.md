---
### Alpha Engine: Refatoração da View do Carrinho (Cart List)
---
**Data:** [Data Atual]
**O que foi feito:**
- Refatoração dos métodos `list()` e `getList()` no controller `checkout/cart.php`.
- Extracão em massa da lógica condicional pesada (verificações de estoque, cálculos de totais, restrições de preço para visitantes e alertas de sessão volátil) para o método `getCartListDisplayData()` do `CartRepository`.
- Remoção de Models estáticos (`tool/upload` e `tool/image`) da UI, delegando o processamento de imagens ao `ImagePresenter` nativo da Alpha Engine.
**Benefícios:** Transformação do controlador num verdadeiro *Skinny Controller*, aliviando-o de mais de 100 linhas de HTML/Business Logic misturados. O DTO de resposta agora está padronizado via `ViewResponse`, isolando eventuais bugs ou inconsistências matemáticas diretamente na camada de Domínio e habilitando testabilidade unitária dos cálculos do carrinho.
