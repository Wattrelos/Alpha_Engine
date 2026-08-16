# language: pt

@use_cases @uc10 @uc11 @pedidos @rastreamento @devolucoes
Funcionalidade: Casos de Uso UC10 e UC11 - Acompanhamento de Pedidos e Logística Reversa
  Como um cliente logado na plataforma Alpha Engine
  Eu quero visualizar meus pedidos, consultar o rastreio da entrega e solicitar devoluções
  Para ter total visibilidade e autonomia sobre minhas compras

  Contexto:
    Dado que o sistema "Alpha Engine - Loja Virtual" está ativo e operacional

  @uc10 @exclusivo_logado @rastreamento
  Cenário: Visualização do histórico e rastreamento de pedidos efetuados
    Dado que eu estou autenticado como "Cliente Logado"
    Quando eu aceso a seção "Meus Pedidos"
    Então o sistema deve listar todos os meus pedidos anteriores e atuais
    E ao selecionar um pedido em trânsito, o status detalhado e o código de rastreamento last-mile devem ser exibidos

  @uc11 @exclusivo_logado @devolucao @cdc
  Cenário: Solicitação de logística reversa para produto entregue dentro de 7 dias
    Dado que eu sou um "Cliente Logado" e possuo um pedido entregue há menos de "7" dias
    Quando eu seleciono o item "Torneira Monocomando" e solicito a devolução com motivo "Arrependimento"
    Então o sistema deve registrar a solicitação de devolução
    E deve gerar o código de autorização de postagem de logística reversa para o envio
