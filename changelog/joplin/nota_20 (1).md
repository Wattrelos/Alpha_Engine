---
### Alpha Engine: Criação do PriceRepository e Desacoplamento do ProductMapper
---
**Data:** [Data Atual]
**O que foi feito:**
- Extração da lógica estrita de "Subqueries de Preço" (`getPriceStatements`) de dentro do `ProductMapper` para o recém-criado `PriceRepository`.
- Atualização das chamadas do `ProductMapper` (`getProduct`, `getProducts`, `getProductsByIds`, `getRelated`) para injetarem dinamicamente as queries vindas do repositório através do parâmetro opcional `$priceStatements`.
- Correção de um bug crítico no `CartRepository`, que estava enviando erroneamente o ID do cliente (`$this->getCustomerId()`) no lugar do ID do Grupo de Clientes (`$customerGroupId`) para a função `getProductsByIds`, impedindo que os descontos B2B/B2C fossem aplicados na base da listagem.
**Benefícios:** Consistência no Domain-Driven Design (DDD). O DataMapper volta a focar estritamente na persistência e extração de tabelas, enquanto toda a lógica de precificação — e como o motor de descontos deve ser montado no SQL — passa a morar em um "Domain Service" (`PriceRepository`). Isso garante que futuras mecânicas financeiras da Alpha Engine sejam adicionadas de forma plug-and-play sem sujar o Model.
