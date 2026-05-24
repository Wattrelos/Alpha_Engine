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
**Benefícios:** Desacoplamento absoluto da camada de autenticação. Agora, *Controllers* relacionados a Login, Registro ou Redefinição de Senha não interagem com query builders do OpenCart. Basta injetar as intenções através dos métodos concisos de persistência e validação da Alpha Engine, fechando brechas de retenção de tokens zumbis através do `clearTokens`.