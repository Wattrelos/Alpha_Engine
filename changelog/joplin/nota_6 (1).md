---
### Alpha Engine: Implementação de Relacionamentos e Suporte de Produtos (Domínio)
---
**Data:** [Data Atual]
**O que foi feito:**
- Identificação e mapeamento das entidades auxiliares do ecossistema de Produtos: `ProductToCategory`, `ProductToLayout`, `ProductToStore`, `ProductRelated` e `ProductReward`.
- A entidade `ProductRelated` foi instruída com o mapeamento duplo para `Product` (atuando como entidade do produto dono e entidade do produto relacionado), operando o auto-relacionamento de forma segura pelo `DataAccessObject`.
- Configuração da entidade de premiações de produtos (`ProductReward`) vinculando apropriadamente `CustomerGroup` e isolando atributos primários como pontuação (`int $points = 0`).
**Benefícios:** Formalização total da área de catálogo. Produtos agora têm suas estruturas de associação geográfica (Lojas) e organizacionais (Categorias, Layouts, Relacionados) disponíveis para queries através do ORM e hidratações transparentes.
