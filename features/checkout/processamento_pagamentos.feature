# language: pt

@checkout @pagamento @gateway @unit_of_work @rabbitmq @UC12 @RF018 @RF019 @RF020 @RN012 @RN016
Funcionalidade: Processamento de Meios de Pagamento no Checkout
  Como um comprador no checkout da Alpha Engine
  Eu quero escolher entre PIX, Boleto ou Cartão de Crédito e processar o pagamento
  Para que meu pedido seja confirmado de forma segura e com suporte a transações atômicas

  Contexto:
    Dado que a loja virtual Alpha Engine está operacional
    E o pedido "#1050" no valor de "R$ 600,00" foi gerado com status inicial "Pendente"
    E a fábrica de gateways de pagamento está conectada

  @pix @desconto_a_vista @RN016
  Cenário: Pagamento via PIX com aplicação automática de desconto à vista
    Dado que a regra de negócio concede "5%" de desconto para pagamento à vista via PIX
    Quando o cliente seleciona a opção de pagamento "PIX"
    Então o valor do pedido com desconto deve ser recalculado para "R$ 570,00"
    E o sistema deve gerar o QRCode dinâmico e o código "Copia e Cola" do PIX com validade de 30 minutos

  @boleto @a_vista
  Cenário: Pagamento via Boleto Bancário
    Quando o cliente escolhe a modalidade de pagamento "Boleto Bancário"
    E conclui o pedido
    Então o sistema deve gerar a linha digitável do boleto com data de vencimento para "3 dias úteis"
    E o status do pedido deve permanecer "Aguardando Pagamento"

  @cartao @sucesso @RF019 @unit_of_work
  Cenário: Pagamento via Cartão de Crédito aprovado pelo Gateway com commit atômico
    Dado que o cliente informa os dados do cartão de crédito com limite disponível
    Quando a action ProcessPaymentAction executa o pagamento no Gateway
    Então a transação no MySQL deve ser iniciada via UnitOfWork
    E o Gateway deve autorizar a cobrança com sucesso
    E o status do pedido deve transicionar para "Pagamento Aprovado"
    E o carrinho de compras do cliente deve ser esvaziado
    E as alterações devem ser persistidas via commit atômico no banco de dados

  @cartao @recusa @rollback @excecao
  Cenário: Falha no pagamento por cartão de crédito e reversão via Rollback
    Dado que a operadora do cartão recusa a transação por "Saldo Insuficiente"
    Quando a action ProcessPaymentAction recebe a recusa da cobrança
    Então a transação no banco de dados deve sofrer rollback imediato
    E nenhuma alteração de status do pedido para aprovado deve ocorrer
    E o sistema deve responder com HTTP 400 contendo a mensagem "O pagamento foi recusado pela operadora: Saldo Insuficiente"
    E o carrinho de compras do cliente deve permanecer com todos os produtos intactos para nova tentativa

  @eventos @rabbitmq @nfe @RF020 @RN012
  Cenário: Disparo assíncrono do evento de domínio order.created para fila RabbitMQ após aprovação
    Dado que o pedido "#1050" teve seu pagamento aprovado com sucesso
    Quando o commit da transação do pedido é concluído
    Então o EventDispatcher deve publicar o evento "order.created" na fila "orders_queue" do RabbitMQ
    E o worker em segundo plano deve consumir o evento para iniciar a emissão automática de NF-e
    E deve disparar a notificação por e-mail com os detalhes da compra confirmada
