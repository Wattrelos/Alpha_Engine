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
  │   │   └── Controllers/Actions/            # Controladores Slim focados no Admin (Painel)
  │   │           ├── Catalog/                                # Focado no catálogo e cliente final
  │   │           │   └── Manufacturer/                       # Ações relacionadas aos fabricantes
  │   │           │      ├── CreateManufacturerAction.php     # Ação para criar um novo fabricante
  │   │           │      ├── StoreManufacturerAction.php      # Ação para salvar um novo fabricante
  │   │           │      ├── EditManufacturerAction.php       # Ação para editar um fabricante
  │   │           │      ├── UpdateManufacturerAction.php     # Ação para atualizar um fabricante
  │   │           │      ├── DeleteManufacturerAction.php     # Ação para deletar um fabricante
  │   │           │      └── ListManufacturersAction.php
  │   │           │
  │   │           ├── Procurement/                            # Focado na gestão interna e compras
  │   │           │     └── Supplier/                         # Ações relacionadas aos fornecedores
  │   │           │         ├── CreateSupplierAction.php
  │   │           │         ├── StoreSupplierAction.php
  │   │           │         ├── EditSupplierAction.php
  │   │           │         ├── UpdateSupplierAction.php
  │   │           │         ├── DeleteSupplierAction.php
  │   │           │         └── ListSuppliersAction.php
  │   │           ├─Repositories/                           # Repositórios específicos da área administrativa
  │   │           ├─Mappers/                                # Mappers específicos da área administrativa
  │   │           ├── Customer/                             # Ações relacionadas aos clientes
  │   │           ├── Sales/                                # Ações relacionadas às vendas
  │   │           │
  │   │           └── Setting/                              # Contexto de Configurações Globais
  │   │                └── StoreSetting/                    # Especificamente sobre os dados da loja
  │   │                    ├── EditStoreSettingAction.php   # Carrega o formulário com os dados atuais
  │   │                    └── UpdateStoreSettingAction.php # Salva as alterações feitas no formulário
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
  ├── Locales/                                # Nova pasta com arquivos de traduções, Utilizando o componente profissional Symfony Translation
  │   ├── en-gb                               # Pasta contendo os arquivos de tradução para English GB.
  │   └── pt-br                               # Pasta contendo os arquivos de português do Brasil.
  ├── resources/                              # 🎨 Recursos Estáticos Não-Compilados e Views
  │   └── views/                              # Templates Twig
  │       ├── admin/                          # Telas do Painel de Controle (Backoffice)
  │       ├── components/                     # Atomic Design (atoms, molecules, organisms)
  │       ├── layouts/                        # Estruturas base (header, footer, html base)
  │       └── pages/                          # Telas principais do E-commerce (catálogo, carrinho, user)
  │           ├── cart/
  │           │    ├── cart.twig             # Carrinho de compras principal
  │           │    ├── checkout.twig         # Fluxo de checkout unificado
  │           │    ├── payment_method.twig   # Métodos de pagamento do checkout
  │           │    ├── shipping-address.twig # Endereço de entrega do checkout
  │           │    └── success.twig          # Tela de sucesso pós-checkout
  │           ├── category/
  │           │    └── show.html.twig        # Listagem/Filtro de categoria (Aside de filtros, lupas de marcas)
  │           ├── errors/
  │           │    └── 404.html.twig         # Página de erro 404
  │           ├── information/
  │           │    ├── contact.twig          # Formulário de contato
  │           │    ├── show.html.twig        # Páginas institucionais (Termos, etc.)
  │           │    └── sitemap.twig          # Mapa do site
  │           ├── product/
  │           │    ├── search.html.twig      # Resultados da busca de produtos
  │           │    └── show.html.twig        # Detalhe do produto (Preços, opções, botão comprar)
  │           └── users/
  │                ├── login.twig            # Login do cliente
  │                ├── register.twig         # Registro de novo cliente
  │                ├── edit.html.twig        # Edição de dados do cliente
  │                ├── index.html.twig       # Página geral de conta
  │                ├── return.twig           # Solicitações de devoluções
  │                ├── accounts/             # Subtelas do painel do cliente
  │                │    ├── account.twig       # Detalhes da conta principal
  │                │    ├── newsletter.twig    # Configuração de Newsletter
  │                │    ├── order-history.twig # Histórico detalhado de um pedido
  │                │    ├── orders.twig        # Listagem de pedidos anteriores
  │                │    └── wishlist.twig      # Lista de desejos
  │                └── addresses/            # Gerenciamento de endereços do cliente
  │                    ├── create.twig        # Novo endereço
  │                    ├── edit.twig          # Edição de endereço existente
  │                    └── index.twig         # Listagem de endereços
  └── public_html/                            # 🌐 Webroot (Document Root exposto e servido para a Internet)
      ├── index.php                           # Front Controller único da Aplicação (Bootstrap)
      ├── .htaccess                           # Regras de URL Rewrite (Apache)
      ├── css/                                # Folhas de estilo (Custom CSS compilado)
      ├── js/                                 # Scripts Vanilla JS e integrações de formulário (AJAX)
      └── fonts/                              # Tipografia e Ícones Locais

```

