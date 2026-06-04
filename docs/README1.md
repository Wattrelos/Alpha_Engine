# Alpha Engine - Arquitetura de Software do E-commerce Standalone
**Documentação Central de Arquitetura**

Este projeto consiste em um sistema de e-commerce moderno e independente desenvolvido com a Alpha Engine. A arquitetura de execuçãodo código legado foi completamente descontinuada e abandonada no runtime da aplicação. Todo o fluxo de execução — incluindo bootstrap, roteador de requisições, controllers e views — é implementado do zero, adotando as melhores práticas do mercado, enquanto os arquivos legadosdo código legado servem unicamente como referências conceituais e de dados.

## 📍 Status Atual (Checkpoint)

* **Onde paramos (Última Conquista):** 
  * **Saneamento Semântico (EntityMapper para EntityHydrator):** Renomeação da classe utilitária de preenchimento reflexivo e validação e sua realocação para `core/Support` (namespace `Alpha\Support`), eliminando o desvio conceitual de pastas de DTOs e Mappers de persistência.
  * Criamos os repositórios vitais de infraestrutura (`ConfigurationRepository`, `TranslationRepository`), eliminando a dependência do `loader.php` para configurações e traduções (i18n).
  * O `AlphaContainer` foi refatorado para usar dicionários $O(1)$, interceptando mais de 25 modelos legados aposentados (`.old`) de forma performática e blindando a Alpha Engine contra quebras ao interagir com estruturas legadas.
  * Consolidamos a lógica do `CartRepository` (mesclagem de sessões, opções, cálculos de peso e impostos).
  * Refatoramos os Controladores de **Categoria** e **Busca** para atuarem puramente via `BaseController`, consumindo ViewResponses perfeitamente padronizadas.
  * Concluímos a blindagem dos modelos de configuração legados em `catalog/model/setting/` (`api`, `cron`, `event`, `extension`, `startup`, `store`), transformando-os em Proxies que delegam o acesso a dados de forma segura e cacheada para os novos Repositórios e Mappers da Alpha Engine.
  * **Resolução de Memory Leaks Nativos:** Consertamos o vazamento de memória do sistema de eventos de Idioma (`language.php`) trocando JSONs recursivos por Pilhas (Stacks) de arrays nativos.
  * **Alpha Failsafe nas Sessões:** Implementamos um escudo no `SessionMapper` e otimizamos o `ConnectionDB` (PDO) para evitar travamentos de servidor (Erro 500 / Erro 2014) causados por sessões corrompidas e superlotadas.
  * **Defuse do Anti-Pattern de Chaves Estrangeiras:** O `DataAccessObject` (DAO) foi ensinado a ignorar Chaves Estrangeiras zeradas (`0`), convertendo-as para `null` nas entidades, protegendo o Padrão de Domínio sem quebrar a consistência das tabelas compartilhadas.
  * **Otimização de N+1 Queries:** Refatoramos a busca do Menu e dos Pedidos para utilizarem o método `readByIds` (Batch Loading), evitando milhares de consultas repetidas.
  * **Testes Unitários:** O framework de testes via JSON foi atualizado para suportar o namespace FQCN (`Alpha\Model\...`) e processar `LazyCollections` com proteção total contra referências circulares em árvores (ex: subcategorias).
  * Avançamos na refatoração do **Fluxo de Checkout** (etapas de endereço de frete, endereço de pagamento, registro e métodos de entrega) migrando para a arquitetura `BaseController` e consumindo nativamente os Repositórios de Domínio (`AddressRepository`, `CountryRepository`, `CartRepository`, etc.).
  * **Isolamento de Widgets (Fim do Loop Infinito de Memória):** Corrigimos uma falha de design onde componentes parciais (como `Cart` e `Menu`) invocavam o método `render()` do controlador base, gerando um loop recursivo com o `Header` e estourando a memória RAM (512MB). Widgets agora usam o renderizador direto de fragmentos.
  * **Menu Dropdown Nativo (Zero-Twig):** Implementamos a renderização de HTML do menu departamental diretamente no `MenuRepository` (convertendo arrays recursivos em classes utilitárias do Bootstrap 5), alcançando tempo de resposta de sub-milissegundos e contornando a ausência de métodos mágicos no padrão DAO.
  * **Injeção de Dicionário Global:** Restauramos o carregamento da camada de Idiomas no cabeçalho central, retornando os textos dinâmicos e ícones da interface sem depender do Loader legado.
  * **Ponte de Componentes Visuais (View Presenters):** Construímos a lógica de adaptação no controlador da Home e nos Módulos (`Featured`, `Latest`, `Bestseller`) para pegar os dados puros, tipados e performáticos da Alpha Engine e formatá-los para o componente visual `Thumb` legadodo código legado (injeção de `no_image.png`, cache de redimensionamento e cálculo de impostos nativo).
  * **Gestão Global de Assets:** Injeção correta da identidade visual (`personalizada.css`) e scripts estruturais (`bootstrap.bundle.min.js`) de forma centralizada pelo `Header`, evitando redundância e falhas nos dropdowns nativos.
  * **Hidratação do Menu Principal:** Controlador `Menu` refatorado para entregar simultaneamente o HTML pré-renderizado ultra-rápido (`{{ categorias }}`) e os dados crus (`{{ categories }}`) garantindo máxima compatibilidade com layouts.
  * **Refatoração Fluida de Layout (Bootstrap 5):** Reestruturação do rodapé (`footer.twig`) para usar grid responsiva moderna (`col-12 col-sm-6 col-md`), eliminando quebras no mobile e adaptando-se automaticamente a remoção de colunas.
  * **Failsafe Global de AJAX (`common.js`):** Inclusão de um interceptador inteligente que previne "crashes" de tela (JavaScript Fatal Errors) caso formulários tentem ser enviados sem atributo `action`, permitindo log limpo para os desenvolvedores.
  * **Limpeza de Anti-Patterns no Carrinho (`cart.php`):** Substituição de dezenas de validações verbosas (`if(isset(...))`) pelo operador moderno Null Coalescing (`??`). Adequação do carrinho para consumir o `ProductRepository` (Domain) ao invés do Mapper, resolvendo automaticamente o idioma e a loja e corrigindo falhas de hidratação.
  * **Completude do DTO de Produtos (`ProductRepository`):** Injeção nativa das rotas de adição ao carrinho, lista de desejos e comparação (`cart_add`, etc.) diretamente na geração da Thumb, reabilitando a função de "Comprar" na página inicial e nas vitrines com comunicação direta à Alpha Engine.
  * **Home Loader-Free:** Adaptação do controlador `common/home` para utilizar injeção direta de DTOs via repositório, populando nativamente as vitrines de Lançamentos e Destaques sem depender de módulos engessados e N+1 Queries.
  * **Saneamento Total da Camada de Layout Global:** Controladores de infraestrutura visual (`Header`, `Footer`, `Menu`, `Cart`) foram convertidos para *Skinny Controllers* estendendo de `BaseController` e aplicando o padrão *Widget Isolation*.
  * **Resolução do JS Crash no Minicart:** Restauração do ID `#cart` no template para religar os disparos AJAX assíncronos e esvaziamento permanente do modelo legado de checkout (`cart.php`), transferindo as responsabilidades definitivamente para o Repositório.
  * **Estabilização da Sessão no PHP 8.4 Strict:** Realinhamento das propriedades da Entidade `Session` com o banco e remoção dos vestígios legados de auditoria de IP e User-Agent, eliminando de vez os Erros Fatais do MySQL (`Field doesn't have a default value`).
  * **Blindagem do Roteador de SEO (`seo_url.php`):** Aplicação massiva de operadores estritos de array e fallbacks (`?: []`) para prever retornos nulos do `parse_url` no PHP 8.4, além da injeção do cache interno (`SeoUrlRepository`).
  * **Evolução de Frontend (Standalone AJAX):** Criação de um interceptador dinâmico em `common.js` que permite botões e links fora de formulários enviarem requisições POST automatizadas injetando atributos `data-*`, viabilizando interfaces mais flexíveis.
  * **Otimização Extrema de ORM (DAO):** Implementação de desduplicação de colunas para comandos `INSERT` e `UPDATE`, inferência inteligente de chaves estrangeiras (mesmo para entidades nulas) e cast automático de tipos (Booleans/DateTimes), blindando o sistema contra o Strict Mode do MySQL.
  * **Tratamento de Integridade e Observabilidade:** O DAO agora intercepta Unique Constraints graciosamente (lançando exceções de domínio) e possui um SQL Debugger expandido que audita as operações complexas de escrita (`CREATE`) geradas pelo ORM diretamente em log físico.
  * **Aderência Restrita a Contratos (SOLID / PHP 8.4):** Ajustes cirúrgicos de tipagens polimórficas de Mapper e resolução de violações do Princípio de Substituição de Liskov (LSP) no Repositório de Lista de Desejos.
  * **Normalização Legada:** Compatibilização da inicialização do usuário no motor de sessão (`system/library/cart/customer.php`) para operar sobre o schema unificado da Alpha Engine (`id`), estabelecendo o fluxo fluído e seguro de login na loja.
  * **Saneamento de ViewRenderer (Anti-WSOD Total):** Substituição em massa do `$this->load->view()` para o `$this->viewRenderer->render()` em componentes parciais (Cart, Menu, Footer, Language, Currency, Cookie, Search), listagens de Categorias/Busca, Checkout Confirm e Páginas Institucionais, erradicando as Telas Brancas (*White Screen of Death*).
  * **Refatoração do Checkout e Área de Cliente (Lazy Loading):** Remoção de construtores pesados e engessados em `checkout.php`, `confirm.php`, `login.php` e `register.php`. Adoção estrita do *Lazy Loading* via `$this->getRepository()`, reduzindo drasticamente o *Memory Footprint* nas rotas críticas de conversão.
  * **Isolamento de Segurança de Domínio (Skinny Controllers):** O controlador de Login agora delega 100% da inteligência de `password_verify` e proteção contra força bruta ao `CustomerRepository`. A página de Contato teve sua vulnerabilidade de *Fatal Error* corrigida e delega validações ao domínio.
  * **Fragment Caching Bottom-Funnel:** Validação da página Home operando livre de consultas ao banco de dados com a *CacheStrategyInterface*. Aplicação de *Fragment Caching* no sub-widget de Produtos Relacionados na tela de Produto, blindando contra N+1 Queries a página de maior tráfego.
  * **Saneamento e Correção do AuthService:** Resolução do bug de sintaxe e realocação de `AuthService.php` para seu caminho PSR-4 correto (`core/Auth/Services/`), incluindo correção de lógica de hashes de senha (`password_verify`), nomes de getters da entidade `Customer` e injeção de dependência via construtor.
* **Status Atual:** **Sprint de Estabilização do Core Transacional e de UI.** A navegação global da loja (Home, Catálogo, Busca), além das rotas vitais de entrada de clientes (Login, Registro) e fechamento (Checkout Raiz), estão 100% blindadas, leves (operando via *Lazy Loading*) e obedecendo estritamente o padrão *Skinny Controller* da Alpha Engine.
* **Próximos Passos (Retomada):** 
  1. Encapsular as linhas residuais do `EntityHydrator` e do fluxo de validação dentro do método `CustomerRepository->registerCustomer()`, isolando 100% o Registro.
  2. Refatorar o Painel Principal do Cliente (`account/account.php`), Endereços e Lista de Desejos, erradicando os últimos vazamentos lógicos da área logada.
  3. Escovar os sub-controladores AJAX assíncronos do Checkout (métodos de frete e pagamento) para concluir a estabilidade atômica das transações.

---

## � A "Obra de Arte": Arquitetura Alpha

Diferentedo código legado padrão, onde o SQL fica espalhado pelos Models, este projeto introduz o padrão **Data Mapper**.

### Componentes Principais:

1.  **Mappers (`core/Mappers/`)**: Centralizam toda a inteligência de persistência. Eles traduzem as necessidades do domínio para SQL otimizado, tratando as inconsistências de nomes de tabelas e chaves primárias.
2.  **DataAccessObject (`core/Model/DataAccessObject/`)**: Uma camada de abstração PDO que gerencia conexões, transações e a execução segura de queries.
3.  **Unit of Work**: Gerencia transações complexas, garantindo que operações em múltiplas entidades ocorram de forma atômica.
4.  **Repository Pattern**: Camada superior aos Mappers que isola casos de uso e implementa Lazy Loading e Identity Map.
5.  **BaseController**: Master Controller que automatiza injeção de dependências e resolução de contextos (Idioma/Loja).
6.  **Modernização de Banco de Dados**: Padronização de chaves `id` (PK) e `tabela_id` (FK) com tipos inteiros longos.

## 📂 Índice de Logs por Categoria
*   [Catálogo, Conteúdo e SEO](README2.md) - Vitrine, busca, blog e gestão de produtos.
*   [Vendas, Checkout e Clientes](README3.md) - Transações, pagamentos e relacionamento.
*   [Segurança, Sistema e Infraestrutura](README4.md) - Core, banco de dados e performance.
*   [Diretrizes e Convenções](extraAnotations.md) - Regras de ouro e filosofia Alpha.
*   Débitos Técnicos e Anti-Patterns - Registro das armadilhas do legado.

## 🛠️ Progresso da Refatoração
*Estado de normalização das classes de domínio:*
 
*   ✅ **Catálogo**: `Product`, `Category`, `CategoryPath`, `CategoryToLayout`, `Manufacturer`, `ManufacturerToLayout`, `Information`, `StockStatus`, `Review`, `Download`, `DownloadDescription`, `DownloadReport`, `Vendor`.
*   ✅ **CMS (Blog)**: `Article`, `Topic`.
*   ✅ **Design**: `Banner`, `Theme`, `Translation`.
*   ✅ **SEO**: `SeoUrl`.
*   ✅ **Vendas/Checkout**: `Cart`, `SubscriptionPlan`, `Subscription`, `SubscriptionHistory`, `SubscriptionStatus`, `SubscriptionTransaction`, `Order`, `OrderVoucher`, `Coupon`, `CouponCategory`, `CouponHistory`, `CouponProduct`, `Voucher`, `VoucherTheme`, `VoucherHistory`, `OrderReturn`, `ReturnAction`, `ReturnReason`.
*   ✅ **Sistema/Localização**: `Setting`, `WeightClass`, `LengthClass`, `LengthClassDescription`, `TaxClass`, `TaxClassDescription`, `TaxRate`, `TaxRule`, `TaxRateToCustomerGroup`, `Session`, `Startup`, `AddressFormat`, `Cron`, `Event`, `Gdpr`, `Location`, `Notification`, `Upload`.
*   ✅ **Segurança e Auditoria**: `User`, `UserGroup`, `UserLogin`, `Log`, `OrderOption`, `Api`, `ApiSession`, `Statistics`, `Gdpr`.
*   ✅ **Marketing**: `Marketing`, `MarketingReport`.
*   ✅ **Clientes**: `Customer`, `CustomerApproval`, `CustomerHistory`, `CustomerLogin`, `CustomerOnline`, `CustomerPayment`, `CustomerReward`, `CustomerTransaction`, `Address`, `CustomerGroup`, `CustomField`, `CustomFieldDescription`, `CustomFieldValue`, `CustomFieldValueDescription`, `CustomFieldCustomerGroup`, `Notification`.
*   ✅ **Módulos e Extensões**: `Extension`, `ExtensionInstall`, `ExtensionPath`, `Module`.


## 📦 Estrutura do Core

```text
## 📦 Estrutura do Core

```text
  ├── catalog/                                # Pasta legadado código legado. Não está mais sendo utilizada.
  ├── changelog/                              # 📝 Notas técnicas, registros de refatoração e log de IAs
  ├── Config/                                 # 📂 Configurações da Aplicação
  │   └── Routes.php                          # 📁 Rotas PSR-15 centralizadas (Slim Framework)
  ├── Containers/                             # 📂 Infraestrutura de Injeção de Dependências (DI)
  │   ├── AppContainer.php                    # 📦 Contêiner Pimple/PHP-DI com definições de classes
  │   └── AppBootstrap.php                    # 📦 Bootstrap de inicialização
  ├── core/                                   # 🧠 Core da Alpha Engine (Backend Standalone)
  │   ├── Admin/                              # 🛡️ Módulo do Painel Administrativo
  │   │   ├── Controllers/Actions/            # Controladores Slim focados no Admin (Painel)
  │   │   ├── Mappers/                        # Mappers específicos da área administrativa
  │   │   └── ...                             # (Estrutura isolada de Backoffice)
  │   ├── Auth/                               # 🔐 Módulo de Autenticação e Segurança
  │   │   ├── Middleware/                     # Guards PSR-15 (Signature, Session, Language, Redirects)
  │   │   └── Services/                       # Regras de negócio de acesso (ex: CustomerAuthService)
  │   ├── Controller/                         # 🎮 Controladores (Skinny Controllers / Actions)
  │   │   ├── BaseController.php              # Controller abstrato base da aplicação
  │   │   └── Actions/                        # Ações HTTP no padrão ADR (Action-Domain-Responder)
  │   │       ├── Cart/                       # Rotas de Carrinho e Checkout
  │   │       ├── Customer/Auth/              # Rotas de Login, Registro e Conta Logada
  │   │       └── ...
  │   ├── Mappers/                            # 🗺️ Data Mappers (Acesso e isolamento de Banco de Dados)
  │   │   ├── EntityMappers/                  # Tradutores entre Banco e Entidades (ex: ProductMapper)
  │   │   └── Observers/                      # Padrão Observer para side-effects (ex: enviar emails)
  │   ├── Model/                              # 🏛️ Coração do Domínio (DDD)
  │   │   ├── DataAccessObject/               # Camada DAO (Conexões PDO, QueryBuilder, UnitOfWork)
  │   │   ├── DataTransferObject/             # DTOs de transporte (ex: ViewResponse)
  │   │   └── Domain/                         # Lógica de Domínio Estrutural
  │   │       ├── Entities/                   # Objetos de domínio puros e tipados (PHP 8.4)
  │   │       └── Repositories/               # Orquestradores de regras de negócio agregadas
  │   ├── Support/                            # 🛠️ Utilitários Transversais e Helpers Nativos
  │   │   ├── Session.php                     # Gerenciamento Nativo de Sessões PHP Standalone
  │   │   ├── EntityHydrator.php              # Padrão Hydrator para popular entidades reflexivamente
  │   │   ├── AlphaString.php                 # Sanitização moderna e validação de strings
  │   │   └── Presenters/                     # Formatadores visuais dedicados (ex: ImagePresenter)
  │   └── View/                               # 🖼️ Camada de Renderização
  │       └── ViewRenderer.php                # Motor renderizador base (integrado ao Twig)
  │
  ├── docs/                                   # 📚 Arquivos de Documentação Arquitetural e Progresso
  ├── resources/                              # 🎨 Recursos Estáticos Não-Compilados e Views
  │   └── views/                              # Templates Twig
  │       ├── admin/                          # Telas do Painel de Controle (Backoffice)
  │       ├── components/                     # Atomic Design (atoms, molecules, organisms)
  │       ├── layouts/                        # Estruturas base (header, footer, html base)
  │       └── pages/                          # Telas principais do E-commerce (catálogo, carrinho, user)
  │
  └── public_html/                            # 🌐 Webroot (Document Root exposto e servido para a Internet)
      ├── index.php                           # Front Controller único da Aplicação (Bootstrap)
      ├── .htaccess                           # Regras de URL Rewrite (Apache)
      ├── css/                                # Folhas de estilo (Custom CSS compilado)
      ├── js/                                 # Scripts Vanilla JS e integrações de formulário (AJAX)
      └── fonts/                              # Tipografia e Ícones Locais



```


## ⚙️ Requisitos

*   PHP 8.2+ (Otimizado para PHP 8.4)
*   MySQL 8.0+
*   Composer (Autoloader PSR-4 configurado para o namespace `Alpha`)
*   Twig

## A imporftância da documentação:
Fazer essa pausa para documentar e versionar é uma excelente prática. No desenvolvimento de sistemas complexos, o código é apenas uma parte da solução; o conhecimento sobre o porquê das decisões arquiteturais é o que garante a longevidade do projeto. Repósitórios, tais como o GitHub é o teu maior aliado para rastrear essa evolução da Alpha Engine.

## 💡 Filosofia do Projeto

O objetivo não é apenas "fazer funcionar", mas criar uma estrutura standalone onde o código seja autodocumentado, seguro por padrão e fácil de testar. A remoção de lógica complexa e o descarte dos controllers e modelsdo código legado permite que a interface se concentre apenas na apresentação e fluxo de dados nativos.

---
*Trabalho em constante evolução para elevar o padrão de engenharia do ecossistema de e-commerce com a Alpha Engine.*

## Regras de negócio para a equipe de produção:
1. Todas as chaves primárias tem nomo "id" para não confundir com as chaves estrangeiras FK que tem [nome da tabela pai] + "_id".
2. Para prevenir estouro de índice (overflow), as chaves PK e FK terão o tipo inteiro longo (BIGINT).
2. No banco de dados, a nomecratura segue o padrão snake_case, porém, na aplicação, o padrão é PascalCase para nomes de classes e arquivos, enquanto o padrão camelCase para nomes de variáveis e métodos. A exceção é na camada View, onde os nomes de pastas e aquivos são quase todos minúsculos.
3. **Nomecraturas:** Em desenvolvimento web, cada linguagem possui seu próprio padrão. Para HTML e CSS o mais recomendado é o kebab-case (separado por hífens), enquanto no JavaScript domina o camelCase (letras iniciais maiúsculas após a primeira) para variáveis e PascalCase para classes
    * HTML e CSS (Classes e IDs) O padrão oficial e mais adotado pela indústria (como no Guia de Estilo CSS da Airbnb) é o kebab-case. Ele facilita a leitura e se alinha à forma como o navegador interpreta o DOM.
      Exemplo: <div class="menu-navegacao principal-ativo"></div>Por que evitar camelCase: menuNavegação no HTML pode ser lido de forma inconsistente por certas ferramentas de busca e bibliotecas.
    * JavaScript O JavaScript é sensível a maiúsculas e minúsculas, e a comunidade segue diretrizes estritas (conforme o Guia de Estilo da Airbnb):
        Variáveis, Funções e Propriedades: camelCase (a primeira letra é minúscula, e as demais palavras iniciam com maiúscula). Exemplo: let totalItens = 10; ou function calcularPreco() {}Classes e Construtores: PascalCase (todas as palavras começam com letra maiúscula).Exemplo: class UsuarioAutenticado {}Constantes Globais (Fixas): UPPER_SNAKE_CASE (todas maiúsculas com _ underline).Exemplo: const TAXA_DE_JUROS = 0.05;
    Apesar de não quebrarem o código se escritos de forma diferente, manter a consistência melhora a legibilidade, ajuda a manutenção em equipe e garante que teu código funcione perfeitamente com os linters (ferramentas de análise de código) e frameworks atuais. Evite sempre o uso de acentos e caracteres especiais nos nomes.




Padrões de projetos (Patterns) utilizados nessa aplicação:
1.  Factory
2.  DDAO
3.  DTO
4.  Singletom
5.  Data Mapper
6.  Template Method
7.  Observer
8.  Mediator
9.  Repository
10. Strategy

---
*Nota: Este documento deve ser atualizado ao final de cada ciclo de saneamento para refletir o estado real da engenharia do projeto.*
