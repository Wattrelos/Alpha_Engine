# Definition of Done (DoD) - Fluxo de Fechamento de Pedidos (Checkout)

Este documento estabelece os critérios obrigatórios de qualidade, arquitetura e segurança que devem ser cumpridos pelo desenvolvedor antes de submeter qualquer alteração no fluxo de checkout para revisão de código (Pull Request). 

As diretrizes aqui descritas materializam as decisões tomadas no [ADR-001 (Idempotência e UoW)](./ADR_Idempotency_UoW.md) e baseiam-se na topologia do diagrama `FluxoDidaticoPedido.puml`.

---

## 📋 Checklist de Validação Técnica

### 1. Camada 1: Apresentação e Infraestrutura (HTTP / Controlador)
- [x] **Validação do Token:** O `Slim Middleware` intercepta e rejeita requisições sem o cabeçalho `X-Idempotency-Key` (Retornar HTTP 400 Bad Request).
- [x] **Bloqueio de Duplicidade:** O `Slim Action` captura a exceção `DuplicateRequestException` vinda do domínio e responde imediatamente com `HTTP 422 Unprocessable Entity` e payload JSON padronizado.
- [x] **Sanitização de Entrada:** O payload do POST é validado e tipado em um DTO (Data Transfer Object) antes de atingir o `Domain Service`.

### 2. Camada 2: Domínio e Aplicação (Regras de Negócio)
- [x] **Atomicidade no Cache:** A checagem da chave no `Redis` é feita via comando atômico com tempo de expiração (`SET key value NX EX 300`). Não é permitido usar `EXISTS` seguido de `SET` isolados.
- [x] **Operações Isoladas em Memória:** Todos os cálculos de totais, checagem de cupons e regras de negócio ocorrem estritamente em memória. Nenhuma conexão persistente de escrita foi aberta nesta fase.
- [x] **Isolamento de Efeitos Colaterais:** O `EventDispatcher` foi configurado para disparar o `OrderCreatedEvent` de forma assíncrona, garantindo que falhas na mensageria não impactem a resposta para o usuário.

### 3. Camada 3: Persistência de Dados (Banco de Dados / ORM)
- [x] **Transação de Escopo Curto:** O comando `BEGIN TRANSACTION` do MySQL ocorre exclusivamente dentro do método `UoW::commit()`.
- [x] **Garantia de Rollback:** Todo o bloco físico de escrita (`Mapper`, `QB`, `DAO`) está encapsulado em uma estrutura `try/catch` que executa o `ROLLBACK` explícito em caso de qualquer exceção.
- [x] **Imutabilidade Estrutural:** Não foram utilizados comandos SQL de mutação direta (`UPDATE`) na tabela de pedidos para alterar estados históricos; novos estados geram novos registros ou seguem a máquina de estados prevista.

---

## 🧪 Requisitos Obrigatórios de Testes e Qualidade

- [x] **Teste de Carga / Concorrência:** Existe um teste automatizado simulando disparos simultâneos (Race Condition) com a mesma `X-Idempotency-Key`, provando que apenas 1 requisição obtém sucesso e as demais falham com HTTP 422.
- [x] **Teste de Mutação da Transação:** Existe teste de unidade garantindo que se o `DAO` falhar no `INSERT`, os dados na memória gerenciados pela `Unit of Work` não fiquem em estado inconsistente ou "sujo".
- [x] **Cobertura de Código:** As classes `Domain Service (Idempotência)` e `Unit of Work` possuem cobertura de testes unitários mínima de 95%.

---

## 🔒 Segurança e Observabilidade

- [x] **Mascaramento de Dados:** Dados sensíveis de pagamento (se houver no payload) não são impressos nos logs da aplicação.
- [x] **ID de Correlação:** A chave `X-Idempotency-Key` é injetada no contexto do Logger (Monolog) para servir como `Correlation ID` em toda a esteira de microserviços.
- [x] **Métricas:** Foram adicionados contadores (Counters) para monitorar a taxa de requisições duplicadas bloqueadas pelo sistema.

---

## 🚀 Critérios de Revisão do Pull Request (PR)

Para o Revisor (Reviewer) aprovar este código:
1. O desenvolvedor deve anexar evidências (print dos testes ou log do CI/CD) provando a resiliência contra o clique duplo.
2. O código do repositório correspondente ao diagrama PlantUML não pode conter vazamento de infraestrutura (consultas SQL brutas fora do DAO).
