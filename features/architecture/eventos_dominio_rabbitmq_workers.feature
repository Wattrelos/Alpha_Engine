# language: pt

@architecture @domain_events @event_driven @rabbitmq @workers
Funcionalidade: Publicação e Processamento Assíncrono de Eventos de Domínio via RabbitMQ
  Como a infraestrutura orientada a eventos da aplicação Alpha Engine
  Eu quero disparar Domain Events para filas RabbitMQ através do EventDispatcher
  Para processar notificações, emissão de notas e integrações pesadas fora do ciclo da requisição HTTP

  Contexto:
    Dado que a aplicação "Alpha Engine" foi inicializada via "public_html/index.php"
    E o contêiner de dependências "AppContainer (PSR-11)" e o motor de visões "TwigEnvironment" foram configurados

  @domain_events @rabbitmq_queue @async_worker
  Cenário: Disparo de evento de pedido e consumo assíncrono pelo Worker CLI
    Dado que a "FrontAction" concluiu um pedido com sucesso
    Quando a Action dispara o evento "OrderCreatedEvent" através do "EventDispatcher"
    Então o evento deve ser publicado na fila do "RabbitMQ & QueueService"
    E o "Worker CLI Consumer" deve consumir a mensagem da fila assincronamente fora do ciclo HTTP
    E o Worker deve executar a rotina de envio de e-mails e faturamento sem bloquear o usuário
