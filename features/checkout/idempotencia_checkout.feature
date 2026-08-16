# language: pt

@checkout @idempotencia @redis @concorrencia @resiliencia
Funcionalidade: Controle de Idempotência e Bloqueio de Requisições Duplicadas no Checkout
  Como a infraestrutura de backend da Alpha Engine
  Eu quero bloquear submissões concorrentes ou repetidas com a mesma chave de idempotência
  Para que o cliente não seja cobrado duplamente e não ocorram pedidos duplicados por duplo clique ou reenvio de rede

  Contexto:
    Dado que a loja virtual Alpha Engine está operacional
    E o serviço Redis está ativo para gerenciamento de chaves de idempotência com TTL de 300 segundos
    E o cliente possui um carrinho pronto para fechamento

  @idempotencia @primeiro_envio
  Cenário: Processamento normal da primeira requisição com chave de idempotência única
    Dado que o Frontend gera a chave de idempotência "uuid-checkout-9876-abcd" para um novo pedido
    Quando o Frontend envia a requisição POST para "/pt-br/checkout" com o cabeçalho 'X-Idempotency-Key: "uuid-checkout-9876-abcd"'
    Então o Redis deve registrar a chave no estado de lock "processing"
    E a transação no MySQL deve ser iniciada via UnitOfWork inserindo a ordem "oc_order"
    E o sistema deve responder ao Frontend com o código HTTP 201 contendo o order_id gerado
    E ao concluir o commit, o evento "order.created" deve ser publicado no RabbitMQ

  @idempotencia @duplo_clique @bloqueio_429
  Cenário: Bloqueio imediato de segunda requisição concorrente (duplo clique do cliente)
    Dado que a chave de idempotência "uuid-checkout-9876-abcd" já está registrada no Redis com o status "PROCESSING"
    Quando o cliente efetua um duplo clique e o Frontend envia uma segunda requisição POST para "/pt-br/checkout" com a mesma chave 'X-Idempotency-Key: "uuid-checkout-9876-abcd"'
    Então o Redis deve retornar lock ativo indicando que a chave está bloqueada
    E o sistema deve interromper a execução e responder imediatamente com HTTP 429 ou 422 com o erro "DUPLICATE_REQUEST"
    E nenhuma nova transação deve ser aberta no MySQL nem nenhuma nova mensagem publicada no RabbitMQ

  @idempotencia @frontend_resiliencia
  Cenário: Tratamento resiliente no Frontend para requisições concorrentes
    Dado que o Frontend enviou requisições concorrentes com a mesma chave de idempotência "uuid-checkout-9876-abcd"
    Quando o Frontend recebe a resposta HTTP 429 da segunda requisição
    E em seguida recebe a resposta HTTP 201 da primeira requisição
    Então o Frontend deve ignorar o erro HTTP 429 sem apresentar mensagens de falha ao usuário
    E deve processar a resposta HTTP 201 com o order_id recebido
    E deve redirecionar o cliente para a página "/checkout/success"
