# language: pt
Funcionalidade: Processamento de Checkout com Idempotência Transacional
  Como um cliente realizando compras na Alpha Engine
  Eu quero que o envio de formulários ou cliques duplos no checkout sejam idempotentes
  Para evitar cobranças e criação de pedidos duplicados por falhas de conexão ou duplo clique.

  Contexto:
    Dado que a loja principal "Agsonhos" possui store_id = 1
    E que o cliente possui 2 itens no carrinho de compras
    E que a chave de idempotência "idemp-uuid-9988-7766" é gerada no cliente

  @security @checkout @idempotency
  Cenário: Sucesso na primeira submissão de checkout
    Quando o cliente envia uma requisição POST para "/pt-br/checkout"
    E inclui o cabeçalho "X-Idempotency-Key: idemp-uuid-9988-7766"
    E fornece dados válidos de pagamento e entrega
    Então a Alpha Engine deve iniciar a transação via UnitOfWork
    E salvar o pedido no banco de dados com store_id = 1
    E responder com código HTTP 201 Created e o "id" do pedido
    E a chave "idempotency:idemp-uuid-9988-7766" deve ser gravada no Redis com TTL de 300 segundos.

  @security @checkout @idempotency
  Cenário: Bloqueio de clique duplo ou requisição concorrente duplicada
    Dado que o pedido com a chave "idemp-uuid-9988-7766" já está em processamento
    Quando o cliente envia uma segunda requisição POST para "/pt-br/checkout" com a mesma chave "X-Idempotency-Key: idemp-uuid-9988-7766"
    Então o middleware de idempotência deve interceptar a requisição no Redis com o comando SET NX EX
    E rejeitar a execução duplicada imediatamente sem abrir transação no banco de dados
    E responder com código HTTP 422 Unprocessable Entity
    E o corpo da resposta em JSON deve conter o código "DUPLICATE_REQUEST" e a mensagem "Processamento em andamento.".
