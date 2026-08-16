# language: pt

Funcionalidade: Arquitetura de Software e Fluxo de Execução da Aplicação (Alpha Engine)
  Como o sistema Alpha Engine
  Eu quero processar as requisições HTTP através de uma arquitetura orientada a camadas (DDD) com Slim 4, PSR-11, Redis, UnitOfWork e RabbitMQ
  Para garantir desacoplamento, performance, consistência transacional e processamento assíncrono de eventos

  Contexto:
    Dado que a aplicação "Alpha Engine" foi inicializada via "public_html/index.php"
    E o contêiner de dependências "AppContainer (PSR-11)" e o motor de visões "TwigEnvironment" foram configurados

  # ============================================================================
  # Cenário 1: Bootstrapping da Aplicação e Roteamento para Controller/Action
  # ============================================================================
  Cenário: Roteamento de requisição HTTP e injeção de dependências no Controller
    Dado que um usuário envia uma requisição HTTP "GET /pt/produtos"
    Quando o "Config/Routes.php" processa a URL e aciona o "LanguageMiddleware"
    E o "AppContainer" resolve e instancia a "FrontAction" injetando suas dependências no construtor
    Então a "FrontAction" deve solicitar os dados do catálogo ao "RepositoryFactory"
    E a página deve ser renderizada utilizando o "TwigEnvironment" a partir de "resources/views/"

  # ============================================================================
  # Cenário 2: Interceptação por Middlewares e Validação de Sessão Distribuída no Redis
  # ============================================================================
  Cenário: Autenticação de sessão de administração via Redis no Middleware Stack
    Dado que um administrador acessa uma rota protegida "/admin/dashboard"
    Quando a requisição passa pelo "AdminSessionMiddleware"
    Então o middleware deve consultar o token de sessão no "Redis (Cache & Session)"
    E se a sessão for válida, a requisição é liberada para a "AdminAction" correspondente
    E se a sessão for inválida, a requisição deve ser redirecionada para a tela de login com erro 401/403

  # ============================================================================
  # Cenário 3: Operação Transacional no Banco de Dados via UnitOfWork (ACID)
  # ============================================================================
  Cenário: Garantia de consistência transacional durante o checkout ou venda PDV
    Dado que uma "AdminAction" ou "FrontAction" inicia uma operação que altera o estado do banco
    Quando a Action solicita uma transação ao "UnitOfWork (UoW)"
    Então o "UnitOfWork" deve instruir o "DataAccessObject (DAO)" a executar "BEGIN TRANSACTION" no "MySQL 8.0"
    E as consultas geradas pelo "QueryBuilder" devem ser validadas e atualizadas no "Identity Map" em memória RAM
    E ao final do processo com sucesso, o "UnitOfWork" deve executar o "COMMIT TRANSACTION" no MySQL

  # ============================================================================
  # Cenário 4: Prevenção de problemas N+1 e Cache de Consultas no Domínio
  # ============================================================================
  Cenário: Consulta otimizada com Identity Map e Cache Redis em Repositórios
    Dado que o "AbstractRepository" precisa carregar entidades e relacionamentos
    Quando a consulta é realizada através do "DataAccessObject (DAO)"
    Então o "DAO" deve checar primeiramente a presença do registro no "Identity Map" em RAM para evitar queries duplicadas N+1
    E o "AbstractRepository" deve ler e gravar os resultados de consultas frequentes no "Redis"
    E os dados do banco "MySQL" devem ser hidratados na "BaseEntity" correspondente

  # ============================================================================
  # Cenário 5: Publicação e Consumo Assíncrono de Eventos de Domínio via RabbitMQ
  # ============================================================================
  Cenário: Disparo de evento de pedido e consumo assíncrono pelo Worker CLI
    Dado que a "FrontAction" concluiu um pedido com sucesso
    Quando a Action dispara o evento "OrderCreatedEvent" através do "EventDispatcher"
    Então o evento deve ser publicado na fila do "RabbitMQ & QueueService"
    E o "Worker CLI Consumer" deve consumir a mensagem da fila assincronamente fora do ciclo HTTP
    E o Worker deve executar a rotina de envio de e-mails e faturamento sem bloquear o usuário

  # ============================================================================
  # Cenário 6: Compatibilidade com código e mapeadores legados via AlphaContainer
  # ============================================================================
  Cenário: Resolução de chamadas de versões legadas da aplicação
    Dado que a aplicação recebe uma chamada utilizando contratos antigos
    Quando a instrução invoca o "AlphaContainer (Legacy Resolver)"
    Então o "AlphaContainer" deve mapear e resolver o "AbstractRepository" ou "BaseMapper" equivalente
    E a execução deve prosseguir sem quebras de retrocompatibilidade
