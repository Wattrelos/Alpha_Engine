---
### Alpha Engine: Implementação de Mappers e Repositórios para ProductDiscount e ProductImage
---
**Data:** [Data Atual]
**O que foi feito:**
- Criação das classes `ProductDiscountMapper` e `ProductImageMapper` abstraindo persistência através da classe `BaseMapper`.
- Criação de `ProductDiscountRepository` e `ProductImageRepository` com métodos para recuperar dados vinculados ao `productId`.
- Implementação do método especializado `getActiveDiscounts` focado em cruzar o ID do Produto com o Grupo do Cliente.
**Benefícios:** Consistência e previsibilidade no motor de catálogo e precificação. Com a lógica de descontos por grupo orquestrada pelo repositório, libertamos o Controller de buscas complexas e garantimos que os cálculos no carrinho operem sempre através de entidades estritamente tipadas do domínio.
