---
### Alpha Engine: Criação dos Repositórios de Opções de Produto
---
**Data:** [Data Atual]
**O que foi feito:**
- Criação de `ProductOptionRepository` e `ProductOptionValueRepository` na camada de Domínio (`Alpha\Model\Domain\Repositories`).
- Implementação do método `getByProductId` para buscar as variações principais (ex: "Cor", "Tamanho") associadas ao produto.
- Implementação estratégica do método `getOptionValuesByIds(array $ids)` retornando instâncias estritas de `ProductOptionValue` **indexadas por seus próprios IDs**.
**Benefícios:** Desacoplamento inteligente. Como as entidades já trafegam via `Identity Map` através do `findById`, processar as opções que o cliente escolheu no carrinho (usando o `getOptionValuesByIds`) garante complexidade $O(1)$. Isso remove a responsabilidade de "array_search" ou múltiplos foreachs dentro do `CartRepository`, tornando os cálculos de imposto, acréscimo de peso e descontos automáticos e infalíveis matematicamente.
