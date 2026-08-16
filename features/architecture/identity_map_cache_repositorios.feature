# language: pt

@architecture @identity_map @redis @performance @n_plus_one
Funcionalidade: Prevenção de Problemas N+1 e Cache de Consultas com Identity Map e Redis
  Como a camada de repositórios da plataforma Alpha Engine
  Eu quero manter um mapa de identidade em memória e cache distribuído no Redis
  Para evitar consultas redundantes ao MySQL e acelerar a hidratação das entidades de domínio

  Contexto:
    Dado que a aplicação "Alpha Engine" foi inicializada via "public_html/index.php"
    E o contêiner de dependências "AppContainer (PSR-11)" e o motor de visões "TwigEnvironment" foram configurados

  @identity_map @cache_redis @performance
  Cenário: Consulta otimizada com Identity Map e Cache Redis em Repositórios
    Dado que o "AbstractRepository" precisa carregar entidades e relacionamentos
    Quando a consulta é realizada através do "DataAccessObject (DAO)"
    Então o "DAO" deve checar primeiramente a presença do registro no "Identity Map" em RAM para evitar queries duplicadas N+1
    E o "AbstractRepository" deve ler e gravar os resultados de consultas frequentes no "Redis"
    E os dados do banco "MySQL" devem ser hidratados na "BaseEntity" correspondente
