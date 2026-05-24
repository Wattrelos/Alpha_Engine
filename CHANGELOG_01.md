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
---

### Alpha Engine: Relacionamento Bidirecional em Country e CountryDescription
**Data:** [Data Atual]
**O que foi feito:**
- Adição do atributo `#[OneToMany]` na entidade `Country.php` apontando para `CountryDescription`, estabelecendo a árvore de traduções.
- Restauração das propriedades primitivas e chaves estrangeiras `$countryId` e `$languageId` na entidade `CountryDescription.php` com seus respectivos mapeamentos condicionais nos setters e getters.
**Benefícios:** Consistência arquitetural. Ao manter a bidirecionalidade, garantimos que o DAO saiba exatamente como resolver a chave estrangeira na hora da hidratação e evitamos o risco do banco de dados ser atualizado com IDs nulos durante rotinas em cascata (`UnitOfWork`).

---

### Alpha Engine: Criação e Relacionamento de ZoneDescription
**Data:** [Data Atual]
**O que foi feito:**
- Criação da entidade `ZoneDescription.php` tipada para o PHP 8.4, com os devidos mapeamentos `#[ManyToOne]` para `Zone` e `Language`.
- Orientação estrutural para injetar a coleção `descriptions` utilizando `#[OneToMany]` na entidade legada `Zone.php`.
**Benefícios:** Expansão da capacidade de localização e tradução do sistema. Modelar as zonas (Estados/Departamentos) com entidades ricas de tradução garante precisão máxima de idioma na emissão de notas fiscais e relatórios logísticos.

---

### Alpha Engine: Padronização de Compatibilidade Legada (DTO Factory) no Repositório
**Data:** [Data Atual]
**O que foi feito:**
- Refatoração do `CountryRepository` para agir bidirecionalmente. Os métodos da interface Domain (`find`, `findAll`) agora retornam as Entidades `Country` ricas mapeadas pelo ORM.
- Os métodos originais do OpenCart (`getCountry`, `getCountries`) foram adaptados como fábricas de *Legacy DTOs* (`toLegacyDTO`), convertendo as entidades em arrays planos.
- Injeção da lógica de resolução multidioma de `CountryDescription` (via `$this->registry` config) dentro da formatação do Array.
**Benefícios:** Zero refatoração manual exigida nos *Controllers* e *Views* do checkout legado. As telas de carrinho, endereço e cadastro que recebem o Model injetado via `AlphaContainer` continuarão operando com a semântica `foreach ($countries as $country) echo $country['name'];` perfeitamente, unindo a força do DDD à retrocompatibilidade da IU.

---

### Alpha Engine: Strict Type Casting no DTO de Country
**Data:** [Data Atual]
**O que foi feito:**
- Verificação da entidade `Country` e aplicação de *type casting* explícito `(int)` para os booleanos `postcode_required` e `status` no método `toLegacyDTO` do `CountryRepository`.
**Benefícios:** Garante que o array exportado para as *Views* legadas tenha o formato exato `0` ou `1`, prevenindo falhas silenciosas no Twig caso ele tente comparar um booleano nativo com uma string estrita do banco de dados (ex: `'1'`).

---

### Alpha Engine: Criação do ZoneRepository com DTO Factory
**Data:** [Data Atual]
**O que foi feito:**
- Criação do `ZoneRepository.php` aplicando a mesma arquitetura de Compatibilidade Legada (DTO Factory) do `CountryRepository`.
- Implementação dos métodos legados `getZone`, `getZonesByCountryId` e `getZones` convertendo a entidade `Zone` para arrays associativos puros.
- Mapeamento de `'localisation/zone'` no interceptador `AlphaContainer`.
**Benefícios:** A tela de checkout depende fortemente de requisições AJAX para `index.php?route=localisation/country.country` para atualizar os estados (zonas) ao alterar o país. Com o `ZoneRepository` retornando o DTO legível pelo JSON do OpenCart, o checkout moderno da Alpha Engine não sofre crash na renderização das opções (Dropdowns) dos formulários.

---

### Alpha Engine: Injeção OneToMany em Zone.php (Zonas e Descrições)
**Data:** [Data Atual]
**O que foi feito:**
- Verificação da entidade `Zone` confirmando a existência correta e tipada dos métodos `getCode()`, `getName()` e `getStatus()`.
- Adição do atributo relacional `#[OneToMany(targetEntity: ZoneDescription::class, mappedBy: "zone", foreignKey: "zoneId")]` na propriedade `$descriptions` da entidade `Zone`.
**Benefícios:** Sem essa declaração, o ORM (DataAccessObject) não seria capaz de hidratar automaticamente as traduções (`ZoneDescription`) quando um estado fosse carregado. Agora, o DTO Factory do repositório pode extrair o nome traduzido nativamente e de forma limpa, garantindo a internacionalização dos estados na tela de checkout e painel de administração.

---

### Alpha Engine: Refatoração Skinny Controller (Localisation/Country)
**Data:** [Data Atual]
**O que foi feito:**
- Refatoração completa do controller `catalog/controller/localisation/country.php`.
- Remoção da instanciação estática de `CountryMapper` (que ignorava injeções de dependência) e substituição pela chamada formal via `RepositoryFactory`.
- Delegação de formatações e malabarismos de chaves de array (remoção de conversão genérica) para os métodos *DTO-factory* (`getCountry` e `getZonesByCountryId`) dos repositórios.
**Benefícios:** Consistência com o padrão Skinny Controller. O JSON entregue ao Javascript do checkout passa a herdar diretamente o cache O(1) do Repositório e respeitará a tradução do idioma ativo do usuário. Além disso, previne quebra de layout na hora de injetar as Tags HTML das zonas.

---

### Alpha Engine: Auditoria de Conformidade ORM em CountryMapper e ZoneMapper
**Data:** [Data Atual]
**O que foi feito:**
- Inspeção do `CountryMapper.php` e `ZoneMapper.php` confirmando a eliminação total de `JOINs` manuais e hidratações N+1.
- Refatoração do método `getTotalZonesByCountryId` no `ZoneMapper` para substituir strings chumbadas (`DB_PREFIX . 'zone'`) pelo uso correto e encapsulado de `$this->tableName`.
**Benefícios:** Os Mappers geográficos agora atestam o sucesso da refatoração relacional da Alpha Engine. A ausência de queries manuais de relacionamento assegura que qualquer alteração futura nas entidades geográficas será resolvida apenas pelo motor ORM abstrato, sem necessidade de tocar nos Mappers.

---

### Alpha Engine: Limpeza Absoluta do LanguageMapper e CurrencyMapper
**Data:** [Data Atual]
**O que foi feito:**
- `LanguageMapper`: Remoção completa de `QueryBuilder` manuais e execuções diretas de array/SQL. Os métodos `getLanguage`, `getLanguageByCode` e `getLanguages` agora delegam 100% da carga para a herança do `BaseMapper` (`findById`, `findOneBy`, `search`), garantindo uso estrito da hidratação ORM.
- `CurrencyMapper`: Adição da definição obrigatória `$entityClass` para suportar buscas de Entidades futuras, e remoção de uma lógica legada de "Static Cache" (`static $cache = null;`). 
**Benefícios:** Mappers devem ser "estúpidos" e transparentes, limitando-se a traduzir entidades para o banco. O cacheamento passa a ser responsabilidade exclusiva do Repository (`LanguageRepository` e `CurrencyRepository`), isolando corretamente a camada de persistência e a lógica de domínio.

---

### Alpha Engine: Implementação dos Mappers ProductOption e ProductOptionValue
**Data:** [Data Atual]
**O que foi feito:**
- Criação das classes `ProductOptionMapper` e `ProductOptionValueMapper` no namespace `Alpha\Mappers\EntityMappers`.
- Extensão da classe `BaseMapper` para herdar o comportamento CRUD padronizado e a integração com o `DataAccessObject` (DAO).
- Configuração das propriedades estritas `$table` e `$entityClass` apontando para as respectivas tabelas e entidades de domínio.
**Benefícios:** Integração total da gestão de variações de produtos com o motor ORM da Alpha Engine. A herança do `BaseMapper` garante que a extração e a hidratação das opções de produto e seus valores (ex: tamanhos, cores, e seus acréscimos de preço) ocorram de forma padronizada e previsível, alimentando corretamente os Repositórios sem a necessidade de reescrever consultas SQL manuais.

---

### Alpha Engine: Criação dos Repositórios de Opções de Produto
**Data:** [Data Atual]
**O que foi feito:**
- Criação de `ProductOptionRepository` e `ProductOptionValueRepository` na camada de Domínio (`Alpha\Model\Domain\Repositories`).
- Implementação do método `getByProductId` para buscar as variações principais (ex: "Cor", "Tamanho") associadas ao produto.
- Implementação estratégica do método `getOptionValuesByIds(array $ids)` retornando instâncias estritas de `ProductOptionValue` **indexadas por seus próprios IDs**.
**Benefícios:** Desacoplamento inteligente. Como as entidades já trafegam via `Identity Map` através do `findById`, processar as opções que o cliente escolheu no carrinho (usando o `getOptionValuesByIds`) garante complexidade $O(1)$. Isso remove a responsabilidade de "array_search" ou múltiplos foreachs dentro do `CartRepository`, tornando os cálculos de imposto, acréscimo de peso e descontos automáticos e infalíveis matematicamente.

---

### Alpha Engine: Implementação de Mappers e Repositórios para ProductDiscount e ProductImage
**Data:** [Data Atual]
**O que foi feito:**
- Criação das classes `ProductDiscountMapper` e `ProductImageMapper` abstraindo persistência através da classe `BaseMapper`.
- Criação de `ProductDiscountRepository` e `ProductImageRepository` com métodos para recuperar dados vinculados ao `productId`.
- Implementação do método especializado `getActiveDiscounts` focado em cruzar o ID do Produto com o Grupo do Cliente.
**Benefícios:** Consistência e previsibilidade no motor de catálogo e precificação. Com a lógica de descontos por grupo orquestrada pelo repositório, libertamos o Controller de buscas complexas e garantimos que os cálculos no carrinho operem sempre através de entidades estritamente tipadas do domínio.

---

### Alpha Engine: Refatoração O(1) do CartRepository (Fim da Regra Legada)
**Data:** [Data Atual]
**O que foi feito:**
- Injeção direta de `ProductOptionValueRepository` e `ProductDiscountRepository` dentro do `CartRepository::getProducts()`.
- O bloco estático de "Fallback Legado" (que executava queries dentro de loop `foreach`) foi desidratado e inteiramente substituído pela busca inteligente `getOptionValuesByIds` com complexidade $O(1)$.
- Delegação estrita do recálculo de preço progressivo para a memória: cruzamento atômico da propriedade `$item['quantity']` com a coleção de `ProductDiscount` extraída do banco.
**Benefícios:** Performance incomparável e precisão financeira extrema. O OpenCart legado frequentemente falhava ao tentar aplicar descontos progressivos (desconto ativado ao colocar "x" unidades no carrinho), pois o modelo atômico antigo muitas vezes baseava-se em quantidade "1" por ser uma query pré-compilada. Agora, as entidades do Domínio avaliam em tempo real o que o usuário escolheu e aplicam modificadores de imposto, desconto e peso através de Objetos seguros (`ProductOptionValue` e `ProductDiscount`).

---

### Alpha Engine: Criação do PriceRepository e Desacoplamento do ProductMapper
**Data:** [Data Atual]
**O que foi feito:**
- Extração da lógica estrita de "Subqueries de Preço" (`getPriceStatements`) de dentro do `ProductMapper` para o recém-criado `PriceRepository`.
- Atualização das chamadas do `ProductMapper` (`getProduct`, `getProducts`, `getProductsByIds`, `getRelated`) para injetarem dinamicamente as queries vindas do repositório através do parâmetro opcional `$priceStatements`.
- Correção de um bug crítico no `CartRepository`, que estava enviando erroneamente o ID do cliente (`$this->getCustomerId()`) no lugar do ID do Grupo de Clientes (`$customerGroupId`) para a função `getProductsByIds`, impedindo que os descontos B2B/B2C fossem aplicados na base da listagem.
**Benefícios:** Consistência no Domain-Driven Design (DDD). O DataMapper volta a focar estritamente na persistência e extração de tabelas, enquanto toda a lógica de precificação — e como o motor de descontos deve ser montado no SQL — passa a morar em um "Domain Service" (`PriceRepository`). Isso garante que futuras mecânicas financeiras da Alpha Engine sejam adicionadas de forma plug-and-play sem sujar o Model.

---

### Alpha Engine: Skinny Controller no Fluxo do Carrinho (Cart)
**Data:** [Data Atual]
**O que foi feito:**
- Desidratação severa do controlador de carrinho (`catalog/controller/checkout/cart.php`), reduzindo seu tamanho e complexidade ciclomática.
- Criação do método `getCartPageData` no `CartRepository` para orquestrar a carga de títulos e Breadcrumbs globais da visão do carrinho.
- Refatoração do método `$cartRepository->getCartListDisplayData()` para incluir a extração do Mapper de Extensões do tipo "Total" diretamente no ViewResponse, eliminando chamadas repetitivas de banco no Controller.
- Criação do `validateAddition` no Repository, extraindo quase 60 linhas de regras de negócio estritas de produto (validação de variantes, campos de texto Regex, opções obrigatórias e assinaturas) para o domínio da Alpha Engine.
**Benefícios:** Limpeza absoluta e máxima testabilidade. O Controller agora atua de forma pura: apenas capta as intenções POST do usuário e as redireciona para a Alpha Engine. A validação de itens no carrinho não está mais algemada ao contexto web, permitindo que a mesma lógica `validateAddition` seja reaproveitada futuramente num endpoint de API Mobile (App) sem reescrever uma linha sequer.

---

### Alpha Engine: Criação de Entidades para Autorização e Tokens de Usuário e Cliente
**Data:** [Data Atual]
**O que foi feito:**
- Foram criadas as classes de Domínio para representar os mecanismos de persistência e segurança de sessão: `CustomerAuthorize`, `CustomerToken`, `UserAuthorize` e `UserToken`.
- Mapeamento dos relacionamentos bidirecionais (atributo `#[ManyToOne]`) garantindo que cada token ou autorização mantenha o contexto da entidade pai associada (`Customer` ou `User`).
- Inicialização de propriedades primitivas com valores estritos (`int = 0`, `string = ''`, `bool = false`) prevenindo `Fatal Error: Uninitialized Property` na hidratação pela Engine.
**Benefícios:** Consistência com o Data Access Object (DAO) e a camada de segurança. Agora os métodos de recuperação de senha e autorização persistente (manter conectado) poderão ser manuseados pelo Doctrine/UnitOfWork e Repository Patterns garantindo a integridade dos dados de IPs, User Agents e Datas de Expiração sem quebrar nas buscas de ORM.

---

### Alpha Engine: Repositórios e Mappers de Segurança (Auth/Tokens)
**Data:** [Data Atual]
**O que foi feito:**
- Criação dos Mappers: `CustomerAuthorizeMapper`, `CustomerTokenMapper`, `UserAuthorizeMapper` e `UserTokenMapper` abstraindo diretamente as tabelas do schema nativo para as novas classes de entidade da Alpha Engine.
- Criação dos Repositórios correspondentes herdando `AbstractRepository`.
- Implementação de métodos utilitários de Domínio voltados à segurança: `findByToken($token)`, `findByCode($code)` para validações, e `clearTokensForCustomer()` / `clearTokensForUser()` para reset atômico após troca de senha bem-sucedida.
**Benefícios:** Desacoplamento absoluto da camada de autenticação. Agora, *Controllers* relacionados a Login, Registro ou Redefinição de Senha não interagem com query builders do OpenCart. Basta injetar as intenções através dos métodos concisos de persistência e validação da Alpha Engine, fechando brechas de retenção de tokens zumbis através do `clearTokens`.# Registro de Modificações IA (Sessão 4)

---

### Alpha Engine: Auditoria do Schema e Implementação de Entidades Ausentes (Pedidos e CMS)
**Data:** [Data Atual]
**O que foi feito:**
- Criação das entidades `OrderOption`, `OrderStatus`, `OrderSubscription` e `OrderReturn` (sendo mapeada para `return`, evitando conflito de nome reservado) para completar as hierarquias de Pedidos e Pós-vendas.
- Criação das entidades `Module`, `Event` e `Startup` para completar a modelagem de configuração do sistema (Hooks, injeção de Middlewares e armazenamento JSON de módulos de extensões).
- Mapeamento de instâncias `#[ManyToOne]` nas sub-entidades de Order (`OrderOption` e `OrderSubscription`) e OrderReturn garantindo hidratação autônoma pelo DAO.
- Tipagem de dados e conversão flutuante para preços nas assinaturas, compatibilidade estrita do PHP 8.4.
**Benefícios:** Esta iteração fecha os "Buracos Negros" do banco de dados na nova arquitetura. O motor logístico da Alpha Engine agora enxerga a totalidade do ciclo de um pedido — desde as opções e assinaturas escolhidas até uma eventual devolução (RMA). Na infraestrutura, a disponibilidade de `Module` e `Event` viabiliza a refatoração completa do motor de extensão, abandonando arrays brutas a favor de objetos manipuláveis via Repositório.

---

### Alpha Engine: Repositórios e Mappers da Malha de Devolução (RMA)
**Data:** [Data Atual]
**O que foi feito:**
- Foram implementados os Mappers para abstração do DAO: `OrderReturnMapper`, `ReturnActionMapper`, `ReturnHistoryMapper`, `ReturnReasonMapper` e `ReturnStatusMapper`.
- Criação dos repositórios correspondentes com foco em buscar as devoluções por Cliente e por Pedido (`OrderReturnRepository`).
- Criação do `ReturnDictionaryRepository` estruturado como *Facade* (Fachada) para puxar facilmente listas de status, motivos e ações com base no idioma (`languageId`) do cliente ativo, eliminando as dezenas de Models isolados do OpenCart legado.
**Benefícios:** A gestão de logística reversa e SAC (Devoluções) agora estão totalmente independentes da arquitetura defasada e dos *queries* complexos. Os *Controllers* da interface não precisam mais mesclar bancos de dados de idiomas, bastando chamar os métodos concisos como `getReasonsByLanguage()`.

---

### Alpha Engine: Correção de Schema e Refatoração de Dicionário de RMA
**Data:** [Data Atual]
**O que foi feito:**
- Correção estrutural na modelagem das entidades `ReturnAction` e `ReturnReason`. Foi detectado que o OpenCart não utiliza o padrão `_description` nestas tabelas (`db_schema.php`), mantendo os atributos `language_id` e `name` enraizados na tabela principal.
- Refatoração do `ReturnDictionaryRepository` para consumir os Mappers primários (Flat Tables) em vez de relacionamentos *OneToMany*.
**Benefícios:** Prevenção de exceções severas no momento em que o DAO fosse montar as queries dinâmicas, garantindo precisão total na extração das traduções de devolução de forma limpa.

---

### Alpha Engine: Malha de Segurança e Painel Administrativo (Users)
**Data:** [Data Atual]
**O que foi feito:**
- Criação das Entidades `User`, `UserGroup` e `UserLogin` tipadas estritamente com PHP 8.4, fechando o escopo administrativo pendente em relação às tabelas nativas de permissão.
- Mapeamento de instâncias `#[ManyToOne]` garantindo que cada *User* possua um *UserGroup* extraído diretamente pelo ORM no processo de hidratação e que os logs de *UserLogin* se relacionem com o *User* correspondente.
- Criação de Mappers dedicados e Repositórios de Domínio contendo utilitários cruciais para segurança como `findByUsername()`, `findByEmail()` e `countRecentLogins()`.
**Benefícios:** Desacoplamento do sistema de autenticação e proteção Anti-Bruteforce. O Backoffice agora passa a usufruir da segurança de Domain Objects e não depende mais das Strings puras em Models legados. A gestão de permissões de módulos via `UserGroup` passa a ser entregue como um Array Limpo vindo do `$userGroup->getPermissionArray()`.# Registro de Modificações IA (Sessão 5)

---

### Alpha Engine: Auditoria de Domínio e Exclusão de Código Zumbi (ApiSession)
**Data:** [Data Atual]
**O que foi feito:**
- Identificada a ausência da tabela `api_session` no `db_schema.php` (característica do OpenCart 4 que consolidou o gerenciamento de sessões de API).
- Exclusão dos arquivos residuais `ApiSession.php` e `ApiSessionMapper.php` (este último também encontrava-se em diretório errado) da arquitetura Alpha Engine.
**Benefícios:** Prevenção de exceções severas no motor ORM (`DataAccessObject`) que poderia tentar realizar consultas em tabelas inexistentes. O domínio permanece enxuto, estritamente sincronizado com o *schema* e livre de débitos técnicos (código morto).

---

### Alpha Engine: Auditoria de Segurança da API e Whitelist
**Data:** [Data Atual]
**O que foi feito:**
- Criação das entidades `ApiIp` e `ApiHistory` com mapeamento reverso `#[ManyToOne]` para a entidade primária `Api`.
- Criação dos repositórios correspondentes, incorporando métodos limpos de negócio: `isIpAllowed(int $apiId, string $ip)` para atuar como middleware de checagem da Whitelist, e `getRecentHistory(int $apiId)` para auditoria.
- Criação dos Mappers para abstrair as queries destas tabelas da camada do ORM.
**Benefícios:** A arquitetura de segurança da API da loja não depende mais de strings SQL vulneráveis dentro do core (`startup/api.php` ou `model/setting/api.php`). Os middlewares agora apenas invocam repositórios, resultando num fluxo de autenticação limpo, OOD (Object-Oriented Design) e preparado para checagem em cache O(1) de permissões.

---

### Alpha Engine: Auditoria de Domínio e Implementação de Catálogos e Endereços
**Data:** [Data Atual]
**O que foi feito:**
- Criação das Entidades Vitais `Address`, `Attribute` e `AttributeGroup`.
- Mapeamento estruturado das chaves estrangeiras com injeção de classes ricas (`Country`, `Zone`, `Customer`, `AttributeGroup`) evitando SQLs JOIN manuais. As conversões de PascalCase para SnakeCase nativas da Engine lidam fluidamente com nomes de colunas numéricas (como `address1` para `address_1`).
- Criação do `AddressRepository` com regras de negócios cruciais implementadas como: `getDefaultAddress(int $customerId)` permitindo autocompletamento fácil no processo de Checkout.
**Benefícios:** Desacoplamento estrutural em painéis complexos. Ao separar Mappers e Repositórios para a malha de Livro de Endereços (Address Book), a tela de Checkout deixará de invocar Modelos que misturam interface com banco. O catálogo também ganha previsibilidade com `AttributeRepository` organizando suas listagens por `sortOrder` em consultas limpas.

---

### Alpha Engine: Auditoria de Domínio e Implementação de Carteira e Fidelidade (Customer)
**Data:** [Data Atual]
**O que foi feito:**
- Criação das entidades vitais financeiras do cliente: `CustomerTransaction` (Carteira/Saldo de Loja) e `CustomerReward` (Pontos de Fidelidade).
- Implementação dos Repositórios associados, com a extração da regra de negócio de totalização (`getBalance()` e `getTotalPoints()`), que computam ativamente a soma ou dedução em PHP puro e limpo percorrendo as coleções obtidas.
**Benefícios:** Desacoplamento do motor financeiro de retenção de clientes. Controllers das contas dos clientes ou até mesmo o motor de descontos do carrinho (`CartRepository`) não precisam escrever SQL ou Models legados para deduzir o saldo da carteira, basta injetar a chamada para `getBalance()` e permitir que as entidades orientadas a objetos guiem as validações.# Registro de Modificações IA (Sessão 6)

---

### Alpha Engine: Auditoria de Domínio e Implementação de Entidades Faltantes do Schema
**Data:** [Data Atual]
**O que foi feito:**
- Criação das entidades `Identifier`, `ProductCode`, `SubscriptionLog`, `SubscriptionOption` e `SubscriptionProduct`.
- Criação das entidades de ligação para o CMS (Blog): `TopicToLayout` e `TopicToStore`.
- Mapeamento adequado dos relacionamentos `#[ManyToOne]` para instâncias como `Product`, `Subscription`, `Topic`, `Store` e `Layout`, garantindo auto-hidratação limpa do EntityMapper sem a necessidade de usar joins manuais ou lógicas engessadas no código legado.
- Tipagem restrita em todas as classes embasada no PHP 8.4, com os devidos valores "defaults" seguros e imutáveis instanciados (ex: `$price = 0.0`, `$quantity = 0`) para prevenção ostensiva contra o erro de "Type Hinting" ao renderizar a extração direta do DAO em bancos ainda não viciados com todos os dados.

**Benefícios:** Mapeamento integral e segurança fortificada contra brechas estruturais nas tabelas apontadas em `db_schema.php`. Com isso, as transações de Assinaturas (Subscriptions), a manipulação avançada de códigos de integração e SKUs por produto, e o rastreamento das ligações CMS para Tópicos ficam blindadas, viabilizando operações seguras de inserção, deleção e resgate guiadas puramente à objetos na nova arquitetura Alpha.# Registro de Modificações IA (Sessão 7)

---

### Alpha Engine: Limpeza de Entidades Zumbis (Sem Tabela no OpenCart 4)
**Data:** [Data Atual]
**O que foi feito:**
- Identificação e remoção de classes de Domínio (`Entities`) que não possuem tabelas correspondentes no banco de dados do OpenCart 4 (verificado via `db_schema.php`).
- Foram deletadas as entidades: `ReturnReasonDescription.php`, `SubscriptionTransaction.php`, `VoucherHistory.php`, `VoucherTheme.php` e `VoucherThemeDescription.php`.
- Identificado e sugerida a deleção manual de: `CustomerPayment.php`, `Voucher.php` e `ReturnActionDescription.php`.
**Benefícios:** Redução do débito técnico e prevenção de erros fatais no mapeamento ORM. Funcionalidades como "Vouchers" não fazem mais parte do core estrutural do OpenCart 4, e tabelas de descrição como `return_reason` e `return_action` tiveram suas colunas mescladas, deixando de usar o formato paralelo relacional de antes.# Registro de Modificações IA (Sessão 8)

---

### Alpha Engine: Auditoria Final de Entidades Zumbis
**Data:** [Data Atual]
**O que foi feito:**
- Análise cruzada da listagem de entidades no `README.md` contra a base de dados real definida no `db_schema.php`.
- Identificação de mais quatro entidades zumbis (sem tabelas correspondentes): `Vendor`, `OrderVoucher`, `Log` e `TaxClassDescription`.
- Atualização do `README.md` para remover as menções a estas entidades e também às entidades removidas em sessões anteriores (`CustomerPayment`, `SubscriptionTransaction`, `Voucher`, `VoucherTheme`, `VoucherHistory`, `ApiSession`).
**Benefícios:** Garantia de que a documentação técnica da Alpha Engine reflete estritamente a realidade estrutural do banco de dados do OpenCart 4, guiando a equipe de desenvolvimento com precisão e evitando débitos técnicos ou *Fatal Errors* causados por mapeamento de tabelas inexistentes.# Registro de Modificações IA (Sessão 9)

---

### Isolamento de Classes Utilitárias (Log Personalizado)
**Data:** [Data Atual]
**O que foi feito:**
- A classe `Log.php` foi fisicamente movida da pasta `/core/Model/Domain/Entities/` para `/LogsPersonalizados/`.
- O namespace foi alterado para `LogsPersonalizados` e a herança da classe abstrata `BaseEntity` foi removida, já que essa classe não representa uma tabela real no OpenCart 4.
**Benefícios:**
- Garante o padrão DDD (*Domain-Driven Design*) no core da Alpha Engine. Classes puramente de serviço, utilitárias ou criadas fora da malha do OpenCart não poluem os diretórios restritos do motor ORM, prevenindo a quebra de mapeamento e falhas de leitura do *DataAccessObject*.