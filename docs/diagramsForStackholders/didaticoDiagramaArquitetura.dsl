workspace "Alpha Engine Architecture" "Diagrama didático de arquitetura do Alpha Engine" {

    model {
        customer = person "Customer" "Cliente final do e-commerce que navega, monta carrinho e faz compras."
        admin = person "Store Administrator" "Administrador do painel que gerencia produtos, estoque, pedidos e configurações da loja."

        alphaEngine = softwareSystem "Alpha Engine" "Plataforma de E-commerce baseada em PHP Slim 4 e Twig." {
            
            webApp = container "Web Application" "Aplicação web executando a lógica do MVC desacoplado (Slim 4 + Twig + DDD)." "PHP / Slim 4" {
                
                # Camada 0: Entrada e Inicialização
                entrypoint = component "index.php" "Ponto único de entrada (Front Controller) que inicializa o AppBootstrap." "PHP"
                routes = component "Routes.php" "Definição de rotas HTTP, mapeando URIs para Actions e Middlewares." "PHP"
                appContainer = component "AppContainer" "Container PSR-11 gerenciador de dependências e IoC via Reflection." "PHP"

                # Camada 1: Pipeline de Middlewares
                langMW = component "LanguageMiddleware" "Detecta idioma da URL/Sessão e injeta traduções no Twig." "PHP"
                adminMW = component "AdminSessionMiddleware" "Protege o painel administrativo validando sessão no Redis/PHP." "PHP"
                sessionMW = component "SessionMiddleware" "Valida sessão ativa e restringe acesso à área logada do cliente." "PHP"
                legacyMW = component "LegacyRouteRedirectMiddleware" "Intercepta e redireciona URLs legadas via HTTP 301." "PHP"

                # Camada 2: Actions & Controladores
                frontAction = component "Front-end Actions" "Single Action Controllers (__invoke) públicos do e-commerce." "PHP"
                authServices = component "Auth Services" "Lógica de login, criptografia de senhas e controle de estado de sessão." "PHP"
                baseCtrl = component "BaseController" "Classe base abstrata fornecendo atalhos comuns para CRUDs do painel." "PHP"
                adminAction = component "Admin Actions" "Ações administrativas seguras executadas no painel." "PHP"

                # Camada 3: Apresentação (Twig)
                twig = component "TwigEngine" "Motor de templates que escapa saídas contra XSS e possui extensões customizadas." "PHP"
                views = component "Twig Templates (.twig)" "Views e heranças de layouts sem código PHP embutido." "Twig"

                # Camada 4: Domínio (DDD)
                repoFactory = component "RepositoryFactory" "Singleton/Factory que gerencia e garante instâncias únicas de repositórios." "PHP"
                repos = component "Repositories" "Coleção de objetos de domínio em memória abstraindo a infra de persistência." "PHP"
                entities = component "Entities (Domain Model)" "Objetos ricos com validações internas e regras essenciais de negócio." "PHP"
                alphaContainer = component "AlphaContainer" "Tradutor de rotas legadas e chaves de busca para os repositórios." "PHP"

                # Camada 5: Infraestrutura de Persistência
                mapperFactory = component "MapperFactory" "Fábrica gerenciadora de instâncias de Entity Mappers." "PHP"
                mappers = component "Entity Mappers" "Mapeia dados relacionais para entidades de domínio bidirecionalmente." "PHP"
                observers = component "Observers" "Padrão Observer disparador de efeitos colaterais de pós-escrita." "PHP"
                dao = component "DataAccessObject (DAO)" "Abstração de conexão física PDO e execução de instruções SQL." "PHP"
                qb = component "QueryBuilder" "Construtor fluído de SQL parametrizado que previne SQL Injection." "PHP"

                # Camada 6: Suporte
                cache = component "CacheStrategy" "Abstração (Redis / Filesystem) para redução de carga do banco de dados." "PHP"
                support = component "Support & Helpers" "Utilitários para formatação, moedas, impostos e autoloading." "PHP"
            }

            database = container "Database" "Banco de dados relacional para persistência transacional." "MySQL / MariaDB" "Database"
            redis = container "Cache Server" "Servidor de cache chave-valor de alta performance." "Redis" "Cache"
        }

        # Interações de Atores
        customer -> entrypoint "Navega e compra produtos" "HTTP"
        admin -> entrypoint "Acessa o painel de controle" "HTTP"

        # Relacionamentos Internos (Conforme didaticoDiagramaArquitetura.puml)
        entrypoint -> routes "1. Define rotas"
        entrypoint -> appContainer "2. Registra dependências"
        entrypoint -> twig "3. Inicializa view engine"

        routes -> langMW "4. Passa requisição HTTP"
        routes -> adminMW "4. Passa requisição HTTP"
        routes -> sessionMW "4. Passa requisição HTTP"
        routes -> legacyMW "4. Passa requisição HTTP"

        langMW -> frontAction "5a. Executa Action"
        adminMW -> adminAction "5b. Executa Action"

        appContainer -> frontAction "Resolve construtor"
        appContainer -> adminAction "Resolve construtor"

        frontAction -> twig "Renderiza"
        adminAction -> baseCtrl "Usa métodos comuns"
        baseCtrl -> twig "Renderiza"
        twig -> views "Compila templates Twig"

        frontAction -> repos "Consulta e persiste"
        adminAction -> repoFactory "Obtém repositório"
        repoFactory -> repos "Cria/retorna"
        authServices -> repos "Valida credenciais"

        repos -> entities "Retorna/modifica"
        repos -> mapperFactory "Obtém Mapper"
        repos -> cache "Cacheia dados frequentes"
        
        mapperFactory -> mappers "Cria/retorna"
        mappers -> observers "Notifica alterações"
        mappers -> dao "Envia dados mapeados"

        dao -> qb "Usa Builder para montar query"
        dao -> database "Executa SQL parametrizado" "PDO"
        qb -> database "Gera SQL"

        alphaContainer -> repos "Resolve repositórios"
        alphaContainer -> mappers "Resolve mappers"

        repos -> support "Formatações/cálculos"
        frontAction -> support "Formatações/cálculos"

        cache -> redis "Armazena e lê cache" "Redis Protocol"
    }

    views {
        systemContext alphaEngine "SystemContext" {
            include *
            autoLayout
        }

        container alphaEngine "Containers" {
            include *
            autoLayout
        }

        component webApp "Components" {
            include *
            autoLayout
        }

        styles {
            element "Element" {
                background #1168bd
                color #ffffff
            }
            element "Person" {
                shape Person
                background #08427b
            }
            element "Database" {
                shape Cylinder
                background #2a6496
            }
            element "Cache" {
                shape Cylinder
                background #a30000
            }
        }
    }
}
