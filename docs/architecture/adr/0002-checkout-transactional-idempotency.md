---
adr: 2
title: Padrão Transacional, Idempotência e Segurança no Fluxo de Fechamento de Pedidos (Checkout)
status: Approved
date: 2026-07-10
authors:
  - Gemini 3 Pro AI
  - Josias
impacted_components:
  - route: "POST /{lang}/checkout"
  - patterns:
      - Idempotency
      - Unit of Work (UoW)
      - Late Binding Transaction
      - Observer (Post-Commit Events)
  - technologies:
      - Redis
      - MySQL (InnoDB)
      - RabbitMQ
rules:
  idempotency:
    header: "X-Idempotency-Key"
    format: "UUIDv4"
    storage: "Redis"
    ttl_seconds: 300
    atomic_operation: "SET NX EX"
    conflict_status: 422
  transaction_lifecycle:
    validation_phase: "In-Memory validation & Coupon calculation (no DB writes)"
    uow_registration: "UoW::registerNew"
    late_binding: "Open MySQL transaction ONLY at UoW::commit()"
  post_commit:
    event: "OrderCreatedEvent"
    dispatch: "ONLY after successful DB COMMIT"
    handler: "RabbitMQ async enqueue"
validation:
  unit_tests:
    - "Test DuplicateRequestException on concurrent duplicate requests"
  architectural_rules:
    - "No network/external API calls within MySQL transaction blocks"
---

# ADR 002: Padrão Transacional, Idempotência e Segurança no Fluxo de Fechamento de Pedidos (Checkout)

## Status
Aprovado (2026-07-10)

## Contexto Geral
O processo de fechamento de pedidos (Checkout) lida diretamente com o ecossistema financeiro e de estoque da empresa. Falhas nesse fluxo podem gerar compras duplicadas (prejuízo ao cliente), furos de estoque (prejuízo à operação) ou inconsistência de dados operacionais.
Precisamos de um mecanismo que garanta que cliques duplos (redes instáveis, usuários impacientes) não gerem efeitos colaterais e que o banco de dados nunca fique em estado parcial caso ocorra uma queda de infraestrutura no meio do processo.

## Decisão Arquitetural
Fica determinado que o fluxo de escrita (HTTP POST `/{lang}/checkout`) implementará obrigatoriamente a combinação de três padrões arquiteturais principais: Controle de Idempotência Distribuído, Unit of Work (UoW) e Garantias ACID Estritas.

### Mecanismo de Idempotência Distribuído (Redis)
* **Controle via Cabeçalho:** Toda requisição de escrita deve exigir o cabeçalho `X-Idempotency-Key` contendo um UUIDv4 gerado pelo cliente.
* **Validação Atômica:** O Domain Service deve validar o token no Redis utilizando uma operação atômica de escrita condicional (`SET key value NX EX 300`).
* **Estado de Processamento:** O primeiro registro salvará o valor como `"processing"`. Requisições subsequentes com a mesma chave dentro da janela de 5 minutos devem lançar imediatamente uma exceção `DuplicateRequestException` e retornar o status HTTP `422 Unprocessable Entity`.

### Separação de Fases e Unit of Work (UoW)
* **Processamento em Memória Primeiro:** Toda a validação de regras de negócio, cálculo de cupons e hidratação de entidades de domínio devem ocorrer em memória antes da abertura da transação de banco de dados.
* **Padrão UoW:** O repositório apenas registrará a nova entidade na memória (`UoW::registerNew`). Nenhuma conexão de banco de dados para escrita deve ser aberta nesta fase.

### Escopo Transacional Curto (ACID no MySQL)
* **Abertura Tardia:** A transação do MySQL (`BEGIN TRANSACTION`) só deve ser aberta no momento exato do `UoW::commit()`.
* **Atomicidade Extrema:** O mapeamento físico e a execução do `INSERT` via DAO devem ocorrer dentro do mesmo bloco controlado (Try/Catch). Qualquer erro interno exige o disparo imediato de um `ROLLBACK`.

### Desacoplamento Pós-Commit (Padrão Observer)
* **Eventos Assíncronos:** Nenhum serviço externo (Envio de e-mail, Notificação Push, Integração com ERP) pode ser chamado de forma síncrona dentro da transação do banco.
* **Publicação Segura:** O `EventDispatcher` só disparará o evento `OrderCreatedEvent` após o sucesso definitivo do `COMMIT` no banco de dados. O listener associado enviará a mensagem para a fila do RabbitMQ.

## Consequências

### Positivas (Prós)
* **Fim do Gasto Duplo:** Garantia absoluta de que cliques repetidos não criarão pedidos duplicados na base.
* **Alta Disponibilidade do Banco:** Como a transação só abre no final do fluxo, o tempo de lock de tabelas/linhas no MySQL cai para poucos milissegundos.
* **Resiliência a Falhas:** Se o RabbitMQ estiver fora do ar, o pedido do cliente foi salvo com segurança no banco; a fila poderá reprocessar o evento mais tarde (Garantia de Consistência Eventual).

### Negativas (Contras)
* **Complexidade no Cliente:** O front-end (web/mobile) torna-se responsável por gerar, gerenciar e persistir temporariamente a chave `X-Idempotency-Key` caso precise retransmitir o request.
* **Ponto Único de Falha Temporário (Redis):** Se o cache de idempotência cair, o fluxo de escrita ficará travado. *(Mitigação: Redis configurado em modo Cluster/Replicação)*.

## Critérios de Aceitação para o Desenvolvedor (DoD)
Para que a tarefa seja dada como concluída na esteira de desenvolvimento, o desenvolvedor deve provar o cumprimento deste ADR através dos seguintes itens:

1. [ ] **Teste Unitário de Idempotência:** Existência de um caso de teste que simula duas requisições idênticas simultâneas, validando o retorno do erro `DuplicateRequestException` (HTTP 422).
2. [ ] **Isolamento de Transação:** Validação de que nenhuma chamada de rede externa (HTTP/Fila) está dentro do bloco `BEGIN TRANSACTION / COMMIT`.
3. [ ] **Logs e Rastreabilidade:** Inclusão do `X-Idempotency-Key` nos logs de aplicação como ID de correlação para rastreamento (Tracing).
