# language: pt

@architecture @legacy_adapter @retrocompatibilidade @container
Funcionalidade: Compatibilidade e Resolução de Adaptadores Legados
  Como a arquitetura evolutiva do Alpha Engine
  Eu quero manter contratos e resoluções legadas via AlphaContainer Legacy Resolver
  Para permitir coexistência e migração gradual de módulos antigos sem quebras de execução

  Contexto:
    Dado que a aplicação "Alpha Engine" foi inicializada via "public_html/index.php"
    E o contêiner de dependências "AppContainer (PSR-11)" e o motor de visões "TwigEnvironment" foram configurados

  @legacy @adapter @retrocompatibilidade
  Cenário: Resolução de chamadas de versões legadas da aplicação
    Dado que a aplicação recebe uma chamada utilizando contratos antigos
    Quando a instrução invoca o "AlphaContainer (Legacy Resolver)"
    Então o "AlphaContainer" deve mapear e resolver o "AbstractRepository" ou "BaseMapper" equivalente
    E a execução deve prosseguir sem quebras de retrocompatibilidade
