---
### Alpha Engine: Skinny Controller no Fluxo do Carrinho (Cart)
---
**Data:** [Data Atual]
**O que foi feito:**
- Desidratação severa do controlador de carrinho (`catalog/controller/checkout/cart.php`), reduzindo seu tamanho e complexidade ciclomática.
- Criação do método `getCartPageData` no `CartRepository` para orquestrar a carga de títulos e Breadcrumbs globais da visão do carrinho.
- Refatoração do método `$cartRepository->getCartListDisplayData()` para incluir a extração do Mapper de Extensões do tipo "Total" diretamente no ViewResponse, eliminando chamadas repetitivas de banco no Controller.
- Criação do `validateAddition` no Repository, extraindo quase 60 linhas de regras de negócio estritas de produto (validação de variantes, campos de texto Regex, opções obrigatórias e assinaturas) para o domínio da Alpha Engine.
**Benefícios:** Limpeza absoluta e máxima testabilidade. O Controller agora atua de forma pura: apenas capta as intenções POST do usuário e as redireciona para a Alpha Engine. A validação de itens no carrinho não está mais algemada ao contexto web, permitindo que a mesma lógica `validateAddition` seja reaproveitada futuramente num endpoint de API Mobile (App) sem reescrever uma linha sequer.
