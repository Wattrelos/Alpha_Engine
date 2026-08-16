# language: pt

@architecture @middleware @pipeline @redis @session
Funcionalidade: Pipeline de Middlewares e Validação de Sessão Distribuída no Redis
  Como a arquitetura de execução do Alpha Engine
  Eu quero interceptar as requisições através de uma pilha de middlewares e validar tokens de sessão no Redis
  Para proteger rotas administrativas de forma centralizada e desacoplada dos Controllers

  Contexto:
    Dado que a aplicação "Alpha Engine" foi inicializada via "public_html/index.php"
    E o contêiner de dependências "AppContainer (PSR-11)" e o motor de visões "TwigEnvironment" foram configurados

  @middleware @admin_session @redis_cache
  Cenário: Autenticação de sessão de administração via Redis no Middleware Stack
    Dado que um administrador acessa uma rota protegida "/admin/dashboard"
    Quando a requisição passa pelo "AdminSessionMiddleware"
    Então o middleware deve consultar o token de sessão no "Redis (Cache & Session)"
    E se a sessão for válida, a requisição é liberada para a "AdminAction" correspondente
    E se a sessão for inválida, a requisição deve ser redirecionada para a tela de login com erro "401/403"
