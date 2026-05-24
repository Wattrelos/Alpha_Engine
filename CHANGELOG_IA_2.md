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

- Transformação do esboço da classe `CountryDescription` para o formato restrito do PHP 8.4, alinhado à base de dados legada.
- Adição de relações `#[ManyToOne]` com as classes `Country` e `Language` para permitir a hidratação recursiva pelo EntityMapper.
**Benefícios:** Consistência tipada e viabilização de consultas O(1) quando uma região ou país precisar ser traduzido em listagens de checkout e perfis de clientes.

---

### Alpha Engine: Implementação de Entidades de Domínio Faltantes (CMS & Infra)
**Data:** [Data Atual]
**O que foi feito:**
- Criação em massa das Entidades (`Entities`) que constavam no `db_schema.php` mas estavam ausentes na nova arquitetura PHP 8.4 estrita.
- Entidades criadas: `Antispam`, `ApiHistory`, `ArticleComment`, `ArticleRating`, `ArticleToLayout` e `ArticleToStore`.
- Injeção de relacionamentos `#[ManyToOne]` permitindo auto-hidratação via Reflection sem quebrar pelo uso de falsos nulos em chaves estrangeiras.
- Tipagem de atributos (`int`, `bool`, `string`) com inicialização de valor padrão (`= 0`, `= false`, `= ''`) visando previnir `Fatal Error: Uninitialized Property`.
**Benefícios:** Consolidada a base para modernização completa da área do Blog/Conteúdo e proteção ativa contra falhas de Type Hinting no DAO, garantindo previsibilidade durante o mapeamento ORM.

- Injeção de relacionamentos `#[ManyToOne]` permitindo auto-hidratação via Reflection sem quebrar pelo uso de falsos nulos em chaves estrangeiras.
- Tipagem de atributos (`int`, `bool`, `string`) com inicialização de valor padrão (`= 0`, `= false`, `= ''`) visando previnir `Fatal Error: Uninitialized Property`.
**Benefícios:** Consolidada a base para modernização completa da área do Blog/Conteúdo e proteção ativa contra falhas de Type Hinting no DAO, garantindo previsibilidade durante o mapeamento ORM.

---

### Alpha Engine: Implementação de Entidades de Configuração, Design e Catálogo (Pivot)
**Data:** [Data Atual]
**O que foi feito:**
- Criação das entidades `BannerImage`, `CategoryFilter`, `CategoryPath` e `CustomFieldCustomerGroup`.
- Entidades de tabelas "pivot" (tabelas de cruzamento sem chave serial primária `id`) foram adaptadas para estender de `BaseEntity` utilizando adequadamente os atributos de relacionamento `#[ManyToOne]`.
- Tipagem estrita de propriedades não relacionais (`sortOrder`, `level`, `required`) com valores *default* seguros para compatibilidade direta com hidratação de formulários da interface e extrações do banco de dados (DAO).
**Benefícios:** Mapeamento consistente de relacionamentos "Muitos Para Muitos" e caminhos hierárquicos (Category Path), mitigando falhas estruturais causadas por chaves e instâncias indefinidas e reforçando a fundação para a listagem e organização do catálogo de produtos e exibição de banners no front-end.

- Entidades de tabelas "pivot" (tabelas de cruzamento sem chave serial primária `id`) foram adaptadas para estender de `BaseEntity` utilizando adequadamente os atributos de relacionamento `#[ManyToOne]`.
- Tipagem estrita de propriedades não relacionais (`sortOrder`, `level`, `required`) com valores *default* seguros para compatibilidade direta com hidratação de formulários da interface e extrações do banco de dados (DAO).
**Benefícios:** Mapeamento consistente de relacionamentos "Muitos Para Muitos" e caminhos hierárquicos (Category Path), mitigando falhas estruturais causadas por chaves e instâncias indefinidas e reforçando a fundação para a listagem e organização do catálogo de produtos e exibição de banners no front-end.

---

### Alpha Engine: Implementação de Relacionamentos e Suporte de Produtos (Domínio)
**Data:** [Data Atual]
**O que foi feito:**
- Identificação e mapeamento das entidades auxiliares do ecossistema de Produtos: `ProductToCategory`, `ProductToLayout`, `ProductToStore`, `ProductRelated` e `ProductReward`.
- A entidade `ProductRelated` foi instruída com o mapeamento duplo para `Product` (atuando como entidade do produto dono e entidade do produto relacionado), operando o auto-relacionamento de forma segura pelo `DataAccessObject`.
- Configuração da entidade de premiações de produtos (`ProductReward`) vinculando apropriadamente `CustomerGroup` e isolando atributos primários como pontuação (`int $points = 0`).
**Benefícios:** Formalização total da área de catálogo. Produtos agora têm suas estruturas de associação geográfica (Lojas) e organizacionais (Categorias, Layouts, Relacionados) disponíveis para queries através do ORM e hidratações transparentes.

---

### Alpha Engine: Implementação de Entidades de Variação e Precificação de Produtos
**Data:** [Data Atual]
**O que foi feito:**
- Criação das entidades `ProductDiscount`, `ProductImage`, `ProductOption`, `ProductOptionValue` e `ProductSubscription`.
- Tipagem de atributos monetários e ponderais (`price`, `weight`, `trialPrice`) como `float` garantindo alta precisão computacional, e `string` para operadores analíticos (`pricePrefix`, `weightPrefix`).
- O Atributo relacional `#[ManyToOne]` foi interconectado recursivamente nas entidades secundárias de opções, habilitando os *Option Values* a serem resolvidos como objetos no momento de cálculo de carrinhos pelo DAO.
**Benefícios:** Expansão vital para a camada de Vendas. O carrinho agora tem acesso arquitetural nativo a variações complexas de preços, descontos por grupos de clientes e dimensões unitárias baseadas em opções escolhidas pelo consumidor.
