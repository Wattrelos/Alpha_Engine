# language: pt

@architecture @unit_of_work @acid @mysql @transacoes
Funcionalidade: Consistência Transacional (ACID) via Unit of Work
  Como a camada de persistência e domínio da aplicação Alpha Engine
  Eu quero coordenar transações de banco de dados através do padrão Unit of Work
  Para assegurar atomicidade, consistência, isolamento e durabilidade (ACID) em operações críticas

  Contexto:
    Dado que a aplicação "Alpha Engine" foi inicializada via "public_html/index.php"
    E o contêiner de dependências "AppContainer (PSR-11)" e o motor de visões "TwigEnvironment" foram configurados

  @unit_of_work @atomic_commit @checkout_pdv
  Cenário: Garantia de consistência transacional durante o checkout ou venda PDV
    Dado que uma "AdminAction" ou "FrontAction" inicia uma operação que altera o estado do banco
    Quando a Action solicita uma transação ao "UnitOfWork (UoW)"
    Então o "UnitOfWork" deve instruir o "DataAccessObject (DAO)" a executar "BEGIN TRANSACTION" no "MySQL 8.0"
    E as consultas geradas pelo "QueryBuilder" devem ser validadas e atualizadas no "Identity Map" em memória RAM
    E ao final do processo com sucesso, o "UnitOfWork" deve executar o "COMMIT TRANSACTION" no MySQL
