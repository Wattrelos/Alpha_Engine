# Changelog

## [Não lançado] - Migração Alpha Engine

### Adicionado (Added)
* **Entidades de Domínio (PHP 8.4)**: Implementação completa de objetos tipados para `Product`, `Category`, `Customer`, `Address`, `Order`, `Voucher`, `Coupon`, `Subscription`, `Marketing`, `TaxClass`, `TaxRate`, `TaxRule`, entre outras.
* **Camada de Mappers**: Introdução do padrão Data Mapper via `ProductMapper`, `CategoryMapper`, `OrderMapper`, `CustomerMapper`, `AddressMapper`, `CartMapper`, `ManufacturerMapper`, `ReviewMapper`, `SeoUrlMapper`, `TaxMapper`, `ExtensionMapper` e `SubscriptionMapper`.
* **Repository Pattern**: Implementação de repositórios de domínio como `CustomerRepository`, `LayoutRepository`, `InformationRepository`, `ManufacturerRepository` e `CategoryRepository` para abstração de casos de uso.
* **Lazy Loading**: Introdução da `LazyCollection` no core do `DataAccessObject` para carregamento sob demanda de coleções pesadas (associações OneToMany).
* **BaseController (Master Pattern)**: Nova classe base em `Alpha\Controller` que automatiza injeção de mappers, repositórios, resolução de idiomas e respostas JSON.
* **Batch Loading no DataAccessObject**: Adição do método `readByIds` para permitir a hidratação massiva de entidades, resolvendo o problema de N+1 queries em listagens de categorias e produtos.
* **Identity Map**: Implementação de cache de instâncias em memória (`static`) no `DataAccessObject` para evitar múltiplas consultas ao banco para o mesmo ID.
* **Order Observer Pattern**: Introdução do suporte a observadores no `OrderMapper` para desacoplamento de side-effects, como o `OrderEmailObserver`.
* **Request Metadata Security**: Implementação do `RequestHelper` no namespace `Alpha\Support` para centralizar a captura de URLs e prevenir ataques de Open Redirect.
* **Módulo de Blog/CMS**: Modernização das entidades `Article` e `Topic` com suporte multi-idioma nativo.
* **Snapshot Pattern**: Implementação de congelamento de dados em `Subscription` e `OrderReturn` para integridade histórica de transações.
* **CouponMapper**: Implementação para suportar persistência de cupons via DataAccessObject, permitindo consultas por código e validação de regras.
* **Sistema de Auditoria**: Entidades `ApiIp`, `Log`, `CustomerActivity` e `ProductReport` integradas ao motor de persistência.
* **Camada de Cache**: Introdução da `CacheStrategyInterface` e `FilesystemCacheStrategy` baseada em disco com suporte a TTL e Lock de escrita.
* **CurrencyMapper**: Criação do Mapper na Alpha Engine para isolar as consultas da tabela de moedas, adicionando regra de negócio de negócio via `findAllActive()` para listar e alocar em memória apenas moedas ativas.
* **StockStatusMapper**: Criação do Mapper para gerenciar as mensagens de status de estoque no Alpha Engine, resolvendo a busca por ID e idioma atual da loja.
* **UploadMapper & UploadRepository**: Mapeador e repositório de uploads de clientes criados para persistência e validação de arquivos enviados.
* **Método executeRawSQL no DAO**: Adição do método de execução de comandos SQL parametrizados brutos no `DataAccessObject` para suporte a instruções do tipo `INSERT ... ON DUPLICATE KEY UPDATE` em persistências de alta frequência como sessões.


### Modificado (Changed)
* **Refatoração de EntityMapper para EntityHydrator**: Renomeação da classe utilitária de preenchimento e validação e sua realocação para `core/Support` (namespace `Alpha\Support`), corrigindo desvios conceituais de DTOs e Mappers de persistência de banco de dados.
* **Normalização de Banco de Dados**: Padronização de chaves primárias para `id` e chaves estrangeiras para `[tabela]_id` com tipo BIGINT para prevenção de overflow.
* **Segurança de Tipos (PHP 8.4)**: Migração total para propriedades tipadas, inicialização segura de valores padrão e uso de `self` para interfaces fluidas nos setters.
* **Isolamento SQL**: Remoção de lógica procedural e queries manuais dos models legados para o motor `QueryBuilder`.
* **CategoryRepository Cache**: Injeção da `CacheStrategyInterface` no repositório de categorias para armazenar a árvore de menus em cache, reduzindo drasticamente queries recursivas de taxonomia no banco.
* **LayoutRepository Cache**: Injeção da `CacheStrategyInterface` no repositório de layouts para armazenar em cache a resolução de rotas, aliviando o banco de dados em todas as visualizações de páginas.
* **OrderRepository UoW**: Consolidação do repositório de pedidos para orquestrar o `UnitOfWork` nas operações de checkout e confirmação, isolando transações complexas. Correção de conflitos de visibilidade de propriedades privadas herdadas, simplificação do construtor, e migração total de consultas SQL legadas para o `OrderMapper` adaptado às PKs normalizadas do banco.
* **CartRepository Context**: Criação do repositório de carrinho para isolar as regras de resolução de contexto (Sessão vs Cliente Logado) e abstrair a persistência direta nos Controladores.
* **CustomerRepository Auth**: Refatoração do repositório de clientes para centralizar regras de autenticação (`password_verify`), proteção contra força bruta e validação de registro, extraindo-as do Mapper.
* **AddressRepository Format**: Criação do repositório de endereços para encapsular a segurança anti-IDOR na busca e a substituição dinâmica de tags do `AddressFormat` de cada região.
* **SeoUrlRepository Cache**: Criação do repositório de SEO para encapsular a lógica de URLs amigáveis (`primeCache`), injetando cache estático e físico para aliviar o banco na geração de links dinâmicos.
* **FreeShippingService**: Implementação do serviço de frete grátis na camada de domínio, isolando a regra de negócio do valor mínimo exigido para o carrinho.
* **WeightBasedShippingService**: Implementação do serviço de frete por peso, com injeção do `WeightClassRepository` para converter unidades dinamicamente e validação estruturada de faixas geográficas.
* **GeoZoneRepository & Mapper**: Extração das consultas SQL de localização geográfica (Geo Zones) de dentro dos serviços de frete, isolando as regras de validação no repositório com suporte a cache.
* **BaseController (Master Pattern)**: Consolidação do Master Controller em `core/Controller/BaseController.php`, unificando a injeção do `RepositoryFactory` e `MapperFactory`, e introduzindo suporte nativo a respostas JSON.
* **Cart Controllers**: Refatoração de `checkout/cart.php` e `api/cart.php` para herdarem da `BaseController`, substituindo o uso legado do `loader.php` e instâncias do sistema de carrinho pela injeção pura do `CartRepository`, simplificando drasticamente as respostas JSON.
* **Product Controller**: Refatoração de `product/product.php` para utilizar a `BaseController`, injetando `ProductMapper`, `CategoryMapper` e `ManufacturerMapper` pela Factory interna em vez de instanciação direta (`new`).
* **Checkout Controller**: Refatoração do controlador global `checkout/checkout.php` para herdar da `BaseController` e utilizar o `CartRepository` para validação primária de itens antes do processamento.
* **Home Controller**: Refatoração do controlador da página inicial (`common/home.php`) para utilizar a `BaseController`, e correção do bug de sobrescrita da variável `$data` que impedia a renderização correta dos módulos em destaque e banners.
* **Lógica de Filtros de Catálogo**: Otimização via subqueries no `CategoryMapper` para extração limpa de filtros de produtos.
* **Unificação de Carrinho**: Lógica do `CartMapper` agora gerencia transição transparente de `session_id` para `customer_id` e evita duplicidade via hash JSON.
* **Motor de Checkout**: Refatoração para suporte a transações atômicas via PDO no processamento de pedidos e histórico de estoque.
* **Mapeamento de Localização**: `AddressMapper` agora resolve layouts postais regionais via `AddressFormat` em uma única query com joins.
* **Autenticação de Cliente**: Migração para uso de `password_verify` nativo e proteção contra força bruta no `CustomerMapper`.
* **Snapshot de Cupons**: Integração da persistência de histórico de cupons no `OrderMapper` utilizando `UnitOfWork` para garantir integridade transacional.
* **Motor de Design**: Resolução de layouts, rotas e módulos agora ocorre 100% via Alpha Engine (`LayoutRepository`), eliminando o `loader.php`.
* **Composição de Componentes**: Controladores globais (Header, Footer, Cookie) refatorados para herdarem de `BaseController`.
* **Ajuste na Entidade TaxRule**: Inicialização do `$taxClassId` com zero e adição do relacionamento ManyToOne com a TaxClass, mantendo a simetria com o resto do projeto.
* **Blindagem no CollectionToArrayConverter**: Adicionada verificação de segurança no conversor para evitar que classes de domínio não inicializadas quebrem o sistema.
* **Padronização Alpha Engine**: Aplicação de tipagem estrita do PHP 8.4, normalização de propriedades para camelCase e configuração de atributos de relacionamento para hidratação automática de Loja e Idioma.
* **Bridge Compatibility no CategoryMapper**: Sincronização dos nomes de métodos com o Model legado do OpenCart e introdução de aliases SQL (`id AS category_id`).
* **Direct Mapper Invocation**: Adoção da estratégia de invocar Mappers diretamente nos Controllers principais (ex: `Product.php`), eliminando o overhead do carregador de modelos legado.
* **Data Enrichment no ProductMapper**: Migração dos métodos de Atributos, Opções e Produtos Relacionados para o Mapper, permitindo montagem da página de detalhes inteiramente via Alpha Engine.
* **Pricing Logic Consolidation**: Integração do método `getDiscounts` ao `ProductMapper`, unificando a fórmula de cálculo dinâmico de preços progressivos.
* **Traffic Auditing Refactor**: Migração do método `addReport` para o `ProductMapper`, passando a utilizar a entidade `ProductReport` e o `DataAccessObject`.
* **Catalog Autonomy**: Refatoração do `ManufacturerMapper` e integração direta nos controladores de marca, encerrando a dependência de modelos legados para o catálogo.
* **Review System Modernization**: Refatoração do `ReviewMapper` e integração direta no controlador de avaliações utilizando a entidade `Review`.
* **Cart Persistence Logic**: Refatoração do `CartMapper` para gerenciar a adição de produtos com opções complexas e comparação de hash JSON para evitar duplicidade.
* **SEO URL Optimization**: Refatoração do `SeoUrlMapper` com suporte a Cache Estático de Lookup e introdução do método `primeCache` para carregamento antecipado de slugs.
* **Breadcrumb & SEO Integration**: Acoplamento do `SeoUrlMapper` ao `CategoryMapper` para gerar links amigáveis utilizando o método `primeCache` na hierarquia da taxonomia.
* **Product Listing SEO**: Integração do `SeoUrlMapper` ao `ProductMapper` para retornar a chave `href` resolvida com links amigáveis através de carregamento em lote.
* **Regional Address Formatting**: Refatoração do `AddressMapper` com integração nativa à entidade `AddressFormat` para resolver layouts postais por país.
* **Atomic Order Persistence**: Implementação do `OrderMapper` utilizando o padrão de agregação de entidades para gerenciar a persistência da árvore completa de pedidos de forma transacional.
* **Customer Domain Authority**: Refatoração do `CustomerMapper`, migração do controlador de Login para invocação direta, uso de `password_verify` nativo e proteção contra força bruta.
* **Automated Currency Refresh**: Refatoração do controlador de Cron de Moedas para utilizar o `ExtensionMapper`, rodando o motor de cotação via Alpha Engine.
* **GDPR Lifecycle Automation**: Refatoração do cron de GDPR para uso exclusivo do `GdprMapper` e `CustomerMapper`.
* **Atomic GDPR Compliance**: Integração do `UnitOfWork` no processamento de GDPR para garantir que a expiração e remoção de contas ocorram em blocos transacionais atômicos.
* **Transactional Subscription Renewal**: Refatoração do motor de recorrência (`Subscription Cron`) para utilizar Mappers na orquestração de pedidos, endereços e sessões, agora obtidos por meio de fábricas do Registry.
* **Medidas Físicas**: Normalização de `WeightClass` e `LengthClass` para cálculos volumétricos de alta precisão.
* **Módulo Fiscal**: TaxClass, TaxRate e TaxRule agora utilizam tipagem float rigorosa para evitar erros matemáticos em checkout.
* **ShippingMapper**: Refatorado para incluir dependências de `WeightClassRepository` e `LengthClassRepository`, preparando para cálculos de frete volumétrico e de dimensões de alta precisão nos módulos de frete.
* **FlatRateShippingService**: Implementação do serviço moderno de frete fixo, utilizando os repositórios de medidas para normalização e validação de limites físicos (Peso/Dimensão) antes de gerar cotações.
* **CouponRepository**: Implementação do repositório de cupons com suporte ao Snapshot Pattern, centralizando a lógica de validade, limites de uso e integridade histórica de descontos aplicados.
* **VoucherRepository**: Implementação do repositório para gestão de cartões-presente (Vouchers), utilizando leitura do histórico para cálculo rigoroso e seguro do saldo restante.
* **Biblioteca de Moedas (currency.php)**: Refatoração do construtor da biblioteca base do OpenCart. Remoção de SQL acoplado (`SELECT * FROM currency`) em favor da injeção do `CurrencyMapper` resolvido pela `mapperFactory`.
* **Orquestração de Carrinho (CartRepository)**: Centralização do processamento inteligente do carrinho. Hidratação de opções sem loops de banco, cálculo progressivo de preços e isolamento do cálculo de impostos (`getTaxes`) e físico (`getWeight`) utilizando o `WeightClassRepository`.
* **Bibliotecas de Medidas e Moedas**: Limpeza de débitos técnicos legados com a remoção definitiva da injeção de banco de dados (`$this->db`) não utilizada dos arquivos `length.php`, `weight.php` e `currency.php`, consolidando o isolamento de domínio destas classes.
* **API de Carrinho (`api/cart.php`)**: Substituição completa do uso da biblioteca legada pelo `CartRepository`. Injeção de contexto global (Idioma, Loja, Grupo de Cliente) nas chamadas do `ProductMapper` para garantir hidratação precisa de traduções e regras de preço/atacado nas respostas JSON.
* **Configurações Globais (Settings)**: Criação do `SettingMapper` e `SettingRepository` para centralizar a extração das configurações do banco de dados. Implementado Identity Map em memória para garantir que o banco seja consultado apenas 1 vez por requisição, independentemente da quantidade de vezes que as configurações sejam solicitadas. O Model legado `catalog/model/setting/setting.php` foi transformado em Bridge.
* **API de Pedidos (`api/order.php`)**: Transição para a `BaseController` e substituição do acoplamento legado da biblioteca de carrinho (`$this->cart`) pelas validações inteligentes diretas do `CartRepository`. Correção do bug nativo na validação do afiliado (`$thid`).
* **APIs de Checkout (Cliente, Frete, Pagamento, etc.)**: Transição em massa dos controladores `api/customer.php`, `api/shipping_address.php`, `api/payment_method.php`, `api/shipping_method.php`, `api/payment_address.php`, `api/affiliate.php` e `api/subscription.php` para a `BaseController`. Eliminação completa do acoplamento com a biblioteca `$this->cart`, substituída pelo `CartRepository` da Alpha Engine.
* **Controladores de Checkout Frontend**: Refatoração das etapas visuais do fluxo de compra (`checkout/payment_address.php`, `checkout/shipping_address.php`, `checkout/shipping_method.php`, `checkout/register.php`), migrando para a arquitetura `BaseController` e injetando repositórios de domínio (`AddressRepository`, `CountryRepository`, etc) em vez de invocar a `mapperFactory` diretamente.
* **Proxies de Configuração do Sistema**: Transformação dos models legados em `catalog/model/setting/` (`store.php`, `extension.php`, `api.php`, `event.php`, `cron.php`, `startup.php`) em pontes seguras (Proxies) que apenas repassam a requisição para os Repositórios da Alpha Engine, blindando a inicialização do sistema.
* **Painel do Cliente e Endereços (`account/account.php` e `account/address.php`)**: Migração total para a arquitetura `BaseController` e consumo do `WishlistRepository`, `AddressRepository` e `CustomerRepository`.
* **Lista de Desejos (`account/wishlist.php` e `WishlistRepository`)**: Refatoração completa para usar repositories (`ProductRepository`, `WishlistRepository`), removendo imports e instanciações de mappers nos controladores e centralizando a formatação dos produtos e ações na camada de domínio. Integração do `ImagePresenter` para redimensionamento seguro de imagens de produtos.
* **Redimensionamento de Imagens e Apresentação (ImagePresenter)**: Refatoração do apresentador para operar de forma 100% autônoma, eliminando a dependência do model legado e utilizando bibliotecas nativas.
* **Controladores de Ferramentas (Upload, Compare, Blog)**: Refatoração dos controladores para herdar de `BaseController` e consumir os novos repositórios/presenters da Alpha Engine.
* **Fluxos de Correio (Mail Controllers)**: Ajuste nos controladores de disparo de e-mails de GDPR, Pedido e Assinatura para remover dependências de loaders legados e consumir os novos repositórios.


### Corrigido (Fixed)
* **Ajuste no ProductMapper**: Inclusão explícita da descrição na consulta SQL dentro do Mapper para garantir que ela não fique vazia.
* **Correção no TranslationRepository**: Correção de chamada de método inexistente e parâmetros invertidos de idioma e loja no mapper.
* **Substituição da Função Inexistente oc_config**: Substituição da função `oc_config()` por chamadas diretas ao Registry nos mappers `TotalMapper`, `ShippingMapper` e `PaymentMapper`, evitando erros fatais em fluxos de fechamento de pedido.
* **Correção no Controlador Featured**: Adicionado o operador de coalescência nula (`?? ''`) e um cast para `(int)` no comprimento para evitar problemas de tipo.
* **Segurança de Inicialização**: Inicialização de todas as propriedades das entidades com valores padrão para prevenir erros de `uninitialized property`.
* **Resiliência do ApiSessionMapper**: Adição de tratativa protetiva (try-catch) para prevenção de falhas causadas pela ausência da tabela `api_session` consolidada no OpenCart 4.
* **Alinhamento do CustomerAffiliate**: Correção da assinatura do método `getId` / `setId` para conformidade estrita com a `InterfaceEntity` e implementação de `JsonSerializable::jsonSerialize`.
* **Recursividade do DAO**: Correção na resolução de associações ManyToOne/OneToMany para evitar loops infinitos em objetos como `CustomerGroup` e `Language`.

### Removido (Removed)
* **Legacy Model Decommissioning**: Desativação oficial e renomeação para `.old` de 11 modelos legados (incluindo `customer.php`, `order.php` e `subscription.php`), consolidando a autoridade do novo motor.
* **Overhead do Loader**: Remoção progressiva da dependência de `$this->load->model` em favor da injeção via `MapperFactory`.
* **Redundância SQL**: Eliminação de JOINS manuais em controladores para obtenção de nomes de países, zonas e status.
* **Desativação de Modelos de Ferramentas**: Modelos legados `image.php` e `upload.php` na pasta `tool/` esvaziados e renomeados para `.old`.

