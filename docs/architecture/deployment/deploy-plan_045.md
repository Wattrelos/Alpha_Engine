# DP-45: Implementação de Sistema de Eventos (Observer) e Integração com RabbitMQ

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-07-10 19:25:03
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/45

## Descrição

# Implementação de Sistema de Eventos (Observer) e Integração com RabbitMQ

O objetivo é implementar um sistema de eventos assíncrono para a criação de novos pedidos. Usando o padrão **Observer**, o controlador de checkout disparará um evento que será interceptado por um ouvinte para publicar a notificação no **RabbitMQ** usando a biblioteca `php-amqplib`.

## Proposed Changes

### 1. Sistema de Eventos (Padrão Observer)

#### [NEW] [EventInterface.php](/core/Events/EventInterface.php)
* Define a assinatura comum para todos os eventos de domínio do e-commerce.

#### [NEW] [ListenerInterface.php](/core/Events/ListenerInterface.php)
* Define a assinatura para os ouvintes/assinantes (Observers) de eventos.

#### [NEW] [EventDispatcher.php](/core/Events/EventDispatcher.php)
* Gerenciador central de eventos (Subject). Mantém o registro de ouvintes associados aos eventos correspondentes e orquestra o disparo.

#### [NEW] [OrderCreatedEvent.php](/core/Events/OrderCreatedEvent.php)
* Representa o evento disparado quando um novo pedido é gerado. Contém o ID do pedido e o DTO de dados do pedido.

### 2. Infraestrutura de Mensageria (RabbitMQ)

#### [NEW] [QueueService.php](/core/Events/QueueService.php)
* Encapsula o boilerplate da biblioteca `php-amqplib`, lidando com a abertura de conexão, declaração de fila durável e publicação de payloads persistentes no RabbitMQ.
* Utiliza variáveis de ambiente (`RABBITMQ_HOST`, `RABBITMQ_PORT`, etc.) com fallbacks seguros para localhost.

#### [NEW] [OrderCreatedListener.php](/core/Events/OrderCreatedListener.php)
* Ouvinte (Observer) do evento de pedido criado. Monta o payload de notificação simplificado e invoca o `QueueService` para publicar a mensagem na fila `order.created`.

### 3. Bootstrap e Injeção de Dependências

#### [MODIFY] [AppBootstrap.php](/Containers/AppBootstrap.php)
* Instanciar o `QueueService`, registrar o listener `OrderCreatedListener` e vincular o `EventDispatcher` no container de injeção de dependência (`AppContainer`).

### 4. Integração no Fluxo de Checkout

#### [MODIFY] [SubmitCheckoutAction.php](/core/Controller/Actions/Cart/SubmitCheckoutAction.php)
* Atualizar o construtor da Action para receber o `EventDispatcher` via injeção automática de dependências (Reflection no container).
* Disparar o `OrderCreatedEvent` imediatamente após o commit com sucesso da transação do pedido.

### 5. Script do Consumidor (Worker CLI)

#### [NEW] [consume-orders.php](/scratch/consume-orders.php)
* Script executável via CLI (`php scratch/consume-orders.php`) que roda continuamente consumindo mensagens da fila `order.created` do RabbitMQ e simulando ações assíncronas (como logs e auditorias de recebimento).

## Verification Plan

### Automated/Manual Verification
- Executar o consumidor em segundo plano no terminal:
  ```bash
  php scratch/consume-orders.php
  ```
- Submeter uma compra simulada via requisição de checkout e verificar se a mensagem chega ao consumidor RabbitMQ em tempo real.

# Checklist de Implementação de Eventos e RabbitMQ

- [x] Criar classes e interfaces de eventos em `core/Events/`:
  - [x] `EventInterface.php`
  - [x] `ListenerInterface.php`
  - [x] `EventDispatcher.php`
  - [x] `OrderCreatedEvent.php`
  - [x] `QueueService.php`
  - [x] `OrderCreatedListener.php`
- [x] Configurar injeção de dependências no `Containers/AppBootstrap.php`
- [x] Integrar injeção do `EventDispatcher` e despacho de eventos no `SubmitCheckoutAction.php`
- [x] Criar o script consumidor de teste `scratch/consume-orders.php`
- [x] Validar a integração realizando testes locais

# Walkthrough - Implementação do Sistema de Eventos (Observer) e RabbitMQ

Implementamos com sucesso o sistema de eventos assíncrono (padrão Observer) integrado ao RabbitMQ no fluxo de criação de pedidos da Alpha Engine.

## Mudanças Realizadas:

### 1. Sistema de Eventos no Domínio (`core/Events/`)
*   [EventInterface.php](/core/Events/EventInterface.php): Contrato básico para os eventos.
*   [ListenerInterface.php](/core/Events/ListenerInterface.php): Contrato para os ouvintes/assinantes (Observers).
*   [EventDispatcher.php](/core/Events/EventDispatcher.php): Orquestrador de disparo de eventos e notificação de ouvintes.
*   [OrderCreatedEvent.php](/core/Events/OrderCreatedEvent.php): Evento concreto que guarda o ID do pedido e o DTO de dados.

### 2. Infraestrutura de Mensageria
*   [QueueService.php](/core/Events/QueueService.php): Serviço wrapper para encapsular a conexão, declaração de fila durável e publicação persistente de mensagens no RabbitMQ via `php-amqplib`.
*   [OrderCreatedListener.php](/core/Events/OrderCreatedListener.php): Ouvinte que assina o evento de criação de pedido, monta o payload JSON estruturado e publica na fila `order.created`.

### 3. Bootstrap e Injeção de Dependências
*   [AppBootstrap.php](/Containers/AppBootstrap.php): Inicializa o `QueueService`, associa o ouvinte `OrderCreatedListener` ao evento `order.created` no `EventDispatcher` e o registra no container `AppContainer` via `$container->bind()`.
*   [SubmitCheckoutAction.php](/core/Controller/Actions/Cart/SubmitCheckoutAction.php): Atualizado o construtor para injetar automaticamente por Reflection o `EventDispatcher` e despachar o `OrderCreatedEvent` logo após a gravação física e confirmação do pedido no banco de dados.

### 4. Scripts de Teste e Consumidor CLI
*   [consume-orders.php](/scratch/consume-orders.php): Consumidor assíncrono executado via CLI para ler a fila `order.created` do RabbitMQ e processar as mensagens em tempo real.
*   [test-events.php](/scratch/test-events.php): Script de teste isolado para simular o bootstrap da aplicação e disparo do evento `order.created`.

### 5. Atualização da Documentação e Diagramas
*   [didaticoDiagramaArquitetura.puml](/docs/diagramsForStackholders/didaticoDiagramaArquitetura.puml): Incluído o `EventDispatcher` no bloco de Domínio, `QueueService` e `RabbitMQ` na infraestrutura e a representação visual da entrega assíncrona de mensagens para o `Worker CLI Consumer`.
*   [architectureDiagram.puml](/docs/architecture/architectureDiagram.puml): Diagrama de arquitetura técnica de alta densidade atualizado contendo o mapeamento de componentes de eventos e mensageria RabbitMQ.

---

## Verificação e Resultados dos Testes:

1.  Atualizamos o classmap do Composer com `composer dump-autoload`.
2.  Iniciamos o script consumidor em background:
    ```bash
    php scratch/consume-orders.php
    ```
3.  Executamos o script de testes:
    ```bash
    php scratch/test-events.php
    ```
4.  O console do consumidor registrou a recepção instantânea e a confirmação (Ack) da mensagem com todos os dados do pedido formatados:
    ```
    ========================================================
     [x] NOVO PEDIDO RECEBIDO NA FILA!
       - ID do Pedido: 999
       - Cliente:      João Silva
       - Telefone:     11999999999
       - E-mail:       teste@email.com
       - Total:        R$ 350,00
       - Pagamento:    Pix
       - Envio:        Retirar na Loja
       - Timestamp:    2026-07-10T19:21:30+00:00
    ========================================================
    ```

