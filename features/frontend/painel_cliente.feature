# language: pt

@frontend @painel_cliente @pedidos @devolucoes @UC10 @UC11 @RF016 @RF017 @RF022 @RN009 @RN011 @RN012
Funcionalidade: Painel do Cliente e Autoatendimento
  Como um cliente logado na plataforma Alpha Engine
  Eu quero gerenciar meu histórico de pedidos, acompanhar entregas e solicitar devoluções
  Para ter total autonomia e transparência no pós-venda

  Contexto:
    Dado que a loja virtual Alpha Engine está operacional
    E o cliente "marcos.souza@email.com" está autenticado em sua conta

  @pedidos @historico @RF016
  Cenário: Listagem do histórico de pedidos anteriores e atuais
    Quando o cliente acessa a área restrita "Meus Pedidos"
    Então o sistema deve listar todos os pedidos ordenados pela data de compra
    E para cada pedido deve exibir o número do pedido, data, valor total e status atual

  @rastreamento @last_mile @RF022 @RN013
  Cenário: Acompanhamento detalhado de rastreamento de pedido em trânsito
    Dado que o pedido "#1042" está com o status "Em Transporte"
    Quando o cliente clica em "Rastrear Pedido" para o pedido "#1042"
    Então a linha do tempo de entrega deve ser renderizada na tela
    E deve exibir as etapas "Pedido Recebido", "Pagamento Confirmado", "Em Transporte" e "Entregue"
    E deve exibir o código de rastreamento last-mile da transportadora com link externo

  @enderecos @multiplos @RF017
  Cenário: Gerenciamento e cadastro de múltiplos endereços de entrega
    Quando o cliente acessa a seção "Meus Endereços"
    E clica em "Adicionar Novo Endereço"
    E preenche os dados do endereço de obra com CEP "05425-070"
    E salva o formulário
    Então o novo endereço deve ser listado entre os endereços salvos
    E deve estar disponível para seleção rápida no checkout

  @devolucao @logistica_reversa @RN009 @RN011 @RN012 @UC11
  Cenário: Solicitação de logística reversa e devolução dentro do prazo legal de 7 dias
    Dado que o cliente possui um pedido entregue há menos de "7" dias
    Quando o cliente solicita a devolução do item "Torneira Monocomando" informando o motivo "Arrependimento"
    Então o sistema deve validar que a solicitação está dentro do prazo do CDC
    E deve gerar o código de autorização de postagem de logística reversa dos Correios
    E deve enviar as orientações de embalagem e envio para o e-mail do cliente
