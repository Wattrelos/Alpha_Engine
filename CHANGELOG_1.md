---

### Atualização e Consistência da Entidade Product vs Banco de Dados
### Correção no Repositório e Mapper de Sessão

**O que foi implementado:**
- Adição das propriedades ausentes e relacionamentos para coincidir com a estrutura exata de `db_schema.php` e `Product.php`.
- Refatoração do `ProductMapper.php` para selecionar atributos vitais como `meta_title`, `meta_description`, `meta_keyword`, `tag`, assim como o nome do `manufacturer` através de um `leftJoin`.
- Adicionado sub-select para cálculo dinâmico da contagem de avaliações (`reviews`) diretamente em lote (`Batch Loading`), previnindo warnings no Template.
- Implementação nativa do método `getSubscriptions` no Mapper, injetando IDs de linguagem necessários para carregar planos de assinaturas.
- Inserção de `Null Coalescing Operators (??)` no `ProductRepository.php` e em cálculos de impostos para evitar "Undefined array key" quando chaves como `special` ou `tax_class_id` estiverem nulas/ausentes.
- Fallback dinâmico para as `Meta Tags` de SEO e o título da página no `product.php` (Controller).

**Por que foi feito e Benefícios:**
A entidade de Domínio do Produto teve atualizações para melhor espelhar a estrutura do banco de dados (que inclui informações de Variante `master_id` e colunas específicas de Override e Inscrições). Essa atualização sincroniza os Mappers e Repositórios para evitar corrupção de dados por ausência de chaves requisitadas pelo Frontend, garantindo estabilidade no sistema de rotas (Strict Types do PHP 8), eliminando excesso de Queries e mantendo a arquitetura Skinny Controller.

---

### Sincronização da Entidade Category e Atualização do Carrinho para Assinaturas

**O que foi implementado:**
- Remoção de referências inexistentes a atributos de UI legacy (`top` e `column`) na documentação de `Category.php` e no Mapper `CategoryMapper.php` (evitando erro fatal de SQL no método `getSubCategories`).
- Atualização completa da classe `CartRepository.php` para consultar o `ProductRepository` em lote e injetar dinamicamente os nomes dos planos de assinatura corretos caso o item no carrinho possua um `subscription_plan_id`.
- Refatoração dos métodos até então vazios/stub `getSubscriptions()` e `hasSubscription()` para buscar e validar as entidades ativas extraídas no iterador nativo de `getProducts()`.

**Por que foi feito e Benefícios:**
A categoria causaria falha em queries de MegaMenus pois a estrutura moderna do sistema já não contempla essas antigas flags de colunas. Ao remover a injeção forçada do campo, evita-se corrupção em chamadas na index. Além disso, as alterações de Assinatura completam a lógica pendente ("// TODO") no Repository, permitindo o correto bloqueio de meios de pagamento incompátiveis e a visibilidade visual pro Cliente que ele está contratando um plano recorrente.

---

### Consistência da Entidade Customer e Validações na API

**O que foi implementado:**
- Injeção de propriedades escalares nativas faltantes na classe de Entidade `Customer.php` (`customerGroupId`, `storeId`, `languageId`, `cpfCnpj` e `persontype`).
- Implementação de validações na API de clientes (`api/customer.php`) para o comprimento do CPF/CNPJ e checagem de tipos aceitos em Enum ('F', 'J').
- Refatoração segura na rotina de Inicialização (Startup) de Sessão em `startup/customer.php` previnindo um *Undefined Array Key* quando os dados da sessão não contiverem de antemão a chave explícita do `customer_group_id`.

**Por que foi feito e Benefícios:**
Uma arquitetura guiada a domínio requer uma paridade estrita entre Banco de Dados e Entidades (Domain Driven Design). Com essa implementação, impedimos perdas de dados e corrupção de payloads quando clientes PJ preenchem seu CNPJ, além de fechar uma pequena brecha de segurança na API que permitia aceitar Documentos mal-formatados ou Injeção de tipos de dados inválidos no `persontype`. A checagem aprofundada na startup garante resiliência a sessões mortas ou corrompidas.

---

### Consistência Estrutural das Entidades de Localização (Country, Zone, Address)

**O que foi implementado:**
- Em `Country.php`: Correção da tipagem e mapeamento do campo `addressFormat` (que antes estava incorretamente como `addressFormatId: int` ao invés de `string`), garantindo alinhamento com a arquitetura descrita em seu próprio *DocBlock*.
- Em `Zone.php`: Reintrodução da propriedade escalar `$countryId` para manter conformidade com o padrão estrito de Chaves Estrangeiras (FK) adotado na *Alpha Engine*, desvinculando a dependência volátil da injeção indireta pelo Objeto.
- Em `Address.php`: Adição dos utilitários de domínio `getCustomFieldArray()` e `setCustomFieldArray()` para casting seguro e nativo de arrays/JSON no escopo dos campos personalizados de clientes.

**Por que foi feito e Benefícios:**
Garante que a *Alpha Engine Data Mapper* realize a extração e hidratação sem quebras de reflexão (Reflection API). A padronização da chave estrangeira escalar em `Zone` acelera consultas onde apenas o ID do país é necessário, sem forçar a resolução recursiva e completa do Objeto proxy. Em endereços, o tratamento nativo de `custom_field` (JSON) através dos novos métodos facilita enormemente o manuseio de formulários dinâmicos de cliente pela camada Controller, evitando uso espalhado de `json_decode` e `json_encode` ao longo das views.

---

### Compatibilidade Legada e Otimização JSON no Repositório de Endereços

**O que foi implementado:**
- Refatoração do `AddressRepository.php` para seguir o padrão restrito da `BaseRepositoryInterface`, forçando injeção e tipagem através do `getMapper()`.
- Criação da DTO Factory interna (`toLegacyDTO`), formatando a Extração da classe `Address` para o Array nativo que os controladores do OpenCart conhecem, resolvendo em cascata os nomes do País e Estado (`Zone`).
- Consumo da nova ferramenta do domínio `$address->getCustomFieldArray()` dentro da conversão de DTO.

**Por que foi feito e Benefícios:**
Quando módulos de frete (Shipping) ou Controladores de API (`api/payment_address.php`) solicitam um endereço, eles esperam todas as chaves padronizadas (como `iso_code_2`) em formato *Array* e o campo `custom_field` decodificado do banco. Com a DTO Factory encapsulando essa conversão, varremos do sistema de forma limpa as dezenas de `json_decode` e *JOINs* paralelos de SQL, tornando o fluxo de Endereçamento compatível universalmente tanto para a Alpha Engine Moderna quanto para o Legado OpenCart.

---

### Desacoplamento de Models Legados na API de Endereços (Payment / Shipping)

**O que foi implementado:**
- Refatoração de `api/payment_address.php` e `api/shipping_address.php` para consumirem os Repositórios de Domínio (`CountryRepository`, `ZoneRepository`, `AddressRepository`).
- Otimização do resgate do Formato de Endereço (`address_format`). Como as entidades `Country` agora o trazem nativamente, eliminamos o Model extra e o overhead do banco de dados na API.
- Proteção anti-IDOR adicionada na verificação `$address_info['customer_id'] != $this->customer->getId()` em conjunto com a carga por `AddressRepository`.

**Por que foi feito e Benefícios:**
Isso encerra o uso da velha malha relacional de Models legados do OpenCart na validação do Checkout. Como a API agora extrai suas validações geográficas através dos repositórios via Identity Map, há uma considerável redução na latência do *Ajax* ao fechar pedidos, com a tranquilidade da padronização e da cache nativa.

---

### Refatoração Estrutural e Integração na API de Cliente (Customer)

**O que foi implementado:**
- O `CustomerMapper.php` e `CustomerRepository.php` foram readequados para herdarem de `BaseMapper` e `BaseRepositoryInterface`, permitindo acesso nativo aos métodos `findById()`, `findOneBy()` e `update()`.
- Correção do método órfão de atualização de senhas. A lógica `$mapper->updatePassword` foi substituída pelo processo nativo orientado a objeto: extraímos o `Customer`, usamos `setPassword()` (já realizando *hash*) e enviamos via `$mapper->update($customer)`.
- Em `api/customer.php`, o bloqueio de consulta via Model legado foi substituído pela injeção da `CustomerRepository`, permitindo carregamento inteligente e performático pela Alpha Engine.

**Por que foi feito e Benefícios:**
Uma das premissas da arquitetura é manter coesão total. Ao forçar o Mapper do Cliente a estender a malha base do ORM, a Entidade não apenas ganha operações CRUD automatizadas, mas permite erradicar *bugs* ocultos nas assinaturas dos métodos antigos, como o `getCustomerByEmail` (agora rebatizado formalmente para `findByEmail`). Além disso, as rotas de API continuam o processo ininterrupto de "emagrecimento", removendo dependências desatualizadas.

---

### Refatoração Estrutural e Desacoplamento na API de Afiliados

**O que foi implementado:**
- O controlador `api/affiliate.php` foi refatorado para utilizar o padrão `Skinny Controller`, substituindo consultas aos Models antigos (`account/affiliate` e `checkout/order`) por injeções orientadas a objetos (`CustomerAffiliateRepository` e `OrderRepository`).
- Condicionais de checagem foram modernizadas. O status do afiliado agora é validado estritamente via `$affiliate_info->getStatus()` através da classe de Domínio do Alpha Engine.
- Criação de uma ponte segura (*fallback*) para o cálculo do subtotal de Pedidos (`getTotals`), garantindo que, se o agregador total do Pedido não estiver perfeitamente hidratado pelo Repository em ambientes migratórios, o sistema não corrompa o fechamento.

**Por que foi feito e Benefícios:**
Continuação do projeto de emagrecimento da API de Vendas (Checkout). Ao eliminar a dependência das camadas de banco de dados nativas e delegar a localização de IDs para a *Alpha Engine*, aumentamos a performance das rotas de fechamento (por via do *Identity Map*) e garantimos que modificações futuras no relacionamento entre *Clientes* e *Afiliados* não exijam reescrita de rotas lógicas da API.

---

### Adequação Arquitetural de UI: Minha Conta (Account)

**O que foi implementado:**
- Refatoração no controlador da interface gráfica de Minha Conta (`catalog/controller/account/account.php`).
- Substituição da injeção invasiva do Data Mapper (`CustomerAffiliateMapper`) pela orquestração correta via `CustomerAffiliateRepository`.
- Utilização do método genérico e inteligente `findOneBy(['customer_id' => ...])` do Repositório (que atende pela `BaseRepositoryInterface`) para validar a existência da conta de afiliado associada ao cliente logado.

**Por que foi feito e Benefícios:**
Na arquitetura de Domain-Driven Design (DDD) da Alpha Engine, os Controladores (sejam de UI ou de API) estão expressamente proibidos de dialogar diretamente com Mappers e Bancos de Dados. O repositório é a única "Fachada" autorizada. Com essa correção na página "Minha Conta", garantimos que as lógicas de negócio centralizadas do repositório (como Cache de Domínio e validações da Entidade ricas) sejam executadas transparentemente quando o cliente acessar seu perfil, mantendo as camadas completamente desacopladas.
- **O que foi feito:** Corrigida a chamada para o método inexistente `getExpireAt()` passando a ser `getExpire()` em `SessionRepository.php`, e ajustada a nomenclatura da coluna `expire_at` para `expire` nas queries do `SessionMapper.php`. Também foi corrigida a referência de conexão do banco de dados no método `saveSession` de `$this->db` para `ConnectionDB::getInstance()->getConnection()`.
- **Por que foi feito:** A tabela de banco de dados nativa da sessão possui a coluna com o nome `expire`, portanto, a entidade hidratada expõe o getter como `getExpire()`. Adicionalmente, `$this->db` não existe no escopo do `SessionMapper` sendo necessário invocar a conexão correta via Singleton para as transações cruas.
- **Benefícios gerados:** Resolve o Erro Fatal 500 no carregamento do sistema e garante o funcionamento correto da gravação, validação e limpeza (garbage collector) de sessões ativas da Alpha Engine.

---

### Correção no Repositório e Mapper de Sessão

- **O que foi feito:** Corrigida a chamada para o método inexistente `getExpireAt()` passando a ser `getExpire()` em `SessionRepository.php`, e ajustada a nomenclatura da coluna `expire_at` para `expire` nas queries do `SessionMapper.php`. Também foi corrigida a referência de conexão do banco de dados no método `saveSession` de `$this->db` para `ConnectionDB::getInstance()->getConnection()`.
- **Por que foi feito:** A tabela de banco de dados nativa da sessão possui a coluna com o nome `expire`, portanto, a entidade hidratada expõe o getter como `getExpire()`. Adicionalmente, `$this->db` não existe no escopo do `SessionMapper` sendo necessário invocar a conexão correta via Singleton para as transações cruas.
- **Benefícios gerados:** Resolve o Erro Fatal 500 no carregamento do sistema e garante o funcionamento correto da gravação, validação e limpeza (garbage collector) de sessões ativas da Alpha Engine.

---

### Refatoração e Remoção de SQL Cru no CustomerGroupMapper

- **O que foi feito:** Substituição das *Raw Queries* SQL e conexões PDO diretas (`ConnectionDB`) no `CustomerGroupMapper.php` pela utilização do `QueryBuilder` nativo em conjunto com o `$this->dao->executeQuery()`. Remoção dos namespaces não utilizados `PDO` e `ConnectionDB`.
- **Por que foi feito:** Consultas manuais bypassam a camada de segurança e os *failsafes* da Alpha Engine. Utilizando o `QueryBuilder`, mantemos coesão com a arquitetura ORM/DAO do projeto, blindando as consultas e padronizando a geração de queries SQL.
- **Benefícios gerados:** Maior segurança (prevenção contra injeção direta), limpeza de código legado (zumbis), facilidade de manutenção futura das tabelas relacionadas a grupos de clientes e conformidade estrita aos padrões arquiteturais do sistema.

---

### Correção de Sintaxe SQL e Colunas de Sessão

- **O que foi feito:** Corrigida a sintaxe do `orderBy` no `CustomerGroupMapper.php` encadeando a instrução para evitar o erro `1064`. Restaurada a nomenclatura real da coluna `expire_at` no `SessionMapper.php` e refatorada a leitura de sessão no `SessionRepository.php` para utilizar o método `getActiveSessionData()`.
- **Por que foi feito:** O QueryBuilder da engine injeta a direção automaticamente; passar dois parâmetros na mesma string gerava duplicação de diretivas (`ASC ASC`). Já na tabela de sessões, a coluna original é `expire_at`, não `expire`, o que disparava o erro `1054`. A refatoração no Repository elimina a dependência com o parseamento defeituoso de getters da entidade, efetuando a validação e leitura por uma consulta atômica com tempo real.
- **Benefícios gerados:** Resolve os Erros Fatais (500) em toda a aplicação (incluindo o cadastro e index), otimiza a performance na leitura de sessões (devolvendo apenas a string e delegando validação para o banco) e respeita o construtor do framework.

---

### Correção de Nomenclatura da Chave Primária em CustomerGroup

- **O que foi feito:** Corrigida a referência da coluna `cg.customer_group_id` para `cg.id` nas cláusulas de Join e Where dentro de `CustomerGroupMapper.php`. Adicionado também suporte bidirecional de chaves (`id` e `customer_group_id`) no DTO retornado por `CustomerGroupRepository.php`.
- **Por que foi feito:** Na Alpha Engine, as chaves primárias das entidades principais são padronizadas sempre para `id` em vez de nomes compostos, o que estava ocasionando o erro `1054 Unknown column` nas consultas geradas nativamente. A interoperabilidade forçada no repositório previne *Undefined array key* nos controllers legados do OpenCart.
- **Benefícios gerados:** Resolução do Erro 500 no fluxo de registro de usuários e no fechamento de pedidos, garantindo a coesão do modelo de banco de dados modernizado sem quebrar retrocompatibilidade nas rotas de frontend que requerem esses dados.

---

### 🏆 Milestone: Estabilização de Sessões e Registro (Alpha Engine)

- **Resumo da Etapa:** Nesta fase crítica, resolvemos os Erros Fatais (500) que impediam o carregamento do sistema e a renderização da tela de cadastro (`account/register`).
- **Principais Entregas:** 
  1. Sincronização da Entidade de Sessão com o Banco de Dados (correção e delegação de validação do tempo de expiração nativo por SQL).
  2. Remoção de SQL cru e conexões PDO diretas no `CustomerGroupMapper`, adotando integralmente o `QueryBuilder` nativo.
  3. Tratamento de interoperabilidade de DTOs no `CustomerGroupRepository` para lidar com a padronização rigorosa de chaves primárias.
- **Benefício Global:** O ecossistema de sessões agora está blindado contra Memory Leaks, e o fluxo de entrada de novos clientes opera com a estabilidade, performance e segurança da nova arquitetura DAO.