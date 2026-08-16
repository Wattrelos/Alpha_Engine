# language: pt

Funcionalidade: Controle de Idempotência e Proteção contra Requisições Duplicadas
  Como o sistema Alpha Engine
  Eu quero bloquear requisições HTTP duplicadas utilizando locks distribuídos no Redis e transações no MySQL
  Para evitar a criação de pedidos duplicados, cobranças duplicadas e duplicação de mensagens na fila RabbitMQ

  Contexto:
    Dado que os serviços Redis, MySQL e RabbitMQ estão ativos e integrados à aplicação
    E o tempo de expiração (TTL) da chave de idempotência no Redis está configurado para 300 segundos

  # ============================================================================
  # Cenário 1: Processamento com sucesso da primeira requisição com chave inédita
  # ============================================================================
  Cenário: Processar pedido com sucesso para uma chave de idempotência inédita
    Dado que o Frontend gera a chave de idempotência "uuid-1234" para um novo pedido
    Quando o Frontend envia a requisição "HTTP POST /checkout" com o cabeçalho "X-Idempotency-Key: uuid-1234"
    E a "CheckoutAction" executa "acquireLock('uuid-1234')" no "IdempotencyService"
    Então o "Redis" deve executar o comando "SETNX idempotency:uuid-1234 PROCESSING EX 300" e retornar "1"
    E a transação no "MySQL" deve ser iniciada via "UnitOfWork" inserindo a ordem "tbkk_order"
    E ao concluir o "COMMIT TRANSACTION", o evento "OrderCreatedEvent(1050)" deve ser publicado no "RabbitMQ"
    E o sistema deve responder ao Frontend com o código "HTTP 201 Created" contendo o "order_id: 1050"

  # ============================================================================
  # Cenário 2: Rejeição de requisição duplicada (Double Click) via Redis Lock
  # ============================================================================
  Cenário: Rejeitar requisição concorrente duplicada gerada por duplo clique
    Dado que a chave de idempotência "uuid-1234" já está registrada no Redis com o status "PROCESSING"
    Quando o cliente efetua um duplo clique e o Frontend envia uma segunda requisição "HTTP POST /checkout" com a mesma chave "X-Idempotency-Key: uuid-1234"
    E a "CheckoutAction" tenta executar "acquireLock('uuid-1234')" no "IdempotencyService"
    Então o "Redis" deve retornar "0" indicando que a chave está bloqueada
    E o "IdempotencyService" deve lançar a exceção "IdempotencyLockException"
    E o sistema deve interromper a execução e responder imediatamente com "HTTP 429 Too Many Requests" e o erro "DUPLICATE_REQUEST"
    E nenhuma nova transação deve ser aberta no "MySQL" nem nenhuma nova mensagem publicada no "RabbitMQ"

  # ============================================================================
  # Cenário 3: Consumo assíncrono do pedido criado pelo Worker CLI Consumer
  # ============================================================================
  Cenário: Consumo e processamento de evento de pedido pela fila RabbitMQ
    Dado que a primeira requisição publicou com sucesso o evento "order.created" para o pedido "1050" no "RabbitMQ"
    Quando o serviço "Worker CLI Consumer" consome a mensagem da fila
    Então o Worker deve executar o processamento do pagamento e envio de notificações por e-mail
    E o Worker deve responder com "ACK" para o "RabbitMQ", removendo a mensagem da fila

  # ============================================================================
  # Cenário 4: Tratamento de respostas no Frontend e redirecionamento final
  # ============================================================================
  Cenário: Tratamento gracioso no Frontend para respostas 429 e 201
    Dado que o Frontend enviou requisições concorrentes com a mesma chave de idempotência "uuid-1234"
    Quando o Frontend recebe a resposta "HTTP 429 Too Many Requests" da segunda requisição
    E em seguida recebe a resposta "HTTP 201 Created" da primeira requisição
    Então o Frontend deve ignorar o erro HTTP 429 sem apresentar mensagens de falha ao usuário
    E deve processar a resposta HTTP 201 com o "order_id: 1050"
    E deve redirecionar o cliente para a página "/checkout/success?order_id=1050"
