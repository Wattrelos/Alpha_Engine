# language: pt

@architecture @bootstrap @psr11 @slim4 @twig
Funcionalidade: Bootstrapping da Aplicação, Roteamento e Injeção de Dependências (PSR-11)
  Como o núcleo da plataforma Alpha Engine
  Eu quero processar requisições HTTP através do pipeline do Slim 4 com injeção de dependências PSR-11 e renderização Twig
  Para garantir baixo acoplamento, alta coesão e separação limpa de responsabilidades

  Contexto:
    Dado que a aplicação "Alpha Engine" foi inicializada via "public_html/index.php"
    E o contêiner de dependências "AppContainer (PSR-11)" e o motor de visões "TwigEnvironment" foram configurados

  @bootstrap @routing @di
  Cenário: Roteamento de requisição HTTP e injeção de dependências no Controller
    Dado que um usuário envia uma requisição HTTP "GET /pt/produtos"
    Quando o "Config/Routes.php" processa a URL e aciona o "LanguageMiddleware"
    E o "AppContainer" resolve e instancia a "FrontAction" injetando suas dependências no construtor
    Então a "FrontAction" deve solicitar os dados do catálogo ao "RepositoryFactory"
    E a página deve ser renderizada utilizando o "TwigEnvironment" a partir de "resources/views/"
