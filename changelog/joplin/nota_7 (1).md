---
### Alpha Engine: Implementação de Entidades de Variação e Precificação de Produtos
---
**Data:** [Data Atual]
**O que foi feito:**
- Criação das entidades `ProductDiscount`, `ProductImage`, `ProductOption`, `ProductOptionValue` e `ProductSubscription`.
- Tipagem de atributos monetários e ponderais (`price`, `weight`, `trialPrice`) como `float` garantindo alta precisão computacional, e `string` para operadores analíticos (`pricePrefix`, `weightPrefix`).
- O Atributo relacional `#[ManyToOne]` foi interconectado recursivamente nas entidades secundárias de opções, habilitando os *Option Values* a serem resolvidos como objetos no momento de cálculo de carrinhos pelo DAO.
**Benefícios:** Expansão vital para a camada de Vendas. O carrinho agora tem acesso arquitetural nativo a variações complexas de preços, descontos por grupos de clientes e dimensões unitárias baseadas em opções escolhidas pelo consumidor.
---
### Alpha Engine: Relacionamento Bidirecional em Country e CountryDescription
---
**Data:** [Data Atual]
**O que foi feito:**
- Adição do atributo `#[OneToMany]` na entidade `Country.php` apontando para `CountryDescription`, estabelecendo a árvore de traduções.
- Restauração das propriedades primitivas e chaves estrangeiras `$countryId` e `$languageId` na entidade `CountryDescription.php` com seus respectivos mapeamentos condicionais nos setters e getters.
**Benefícios:** Consistência arquitetural. Ao manter a bidirecionalidade, garantimos que o DAO saiba exatamente como resolver a chave estrangeira na hora da hidratação e evitamos o risco do banco de dados ser atualizado com IDs nulos durante rotinas em cascata (`UnitOfWork`).
