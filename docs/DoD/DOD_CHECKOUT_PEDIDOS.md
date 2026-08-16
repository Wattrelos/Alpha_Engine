---
type: "Quality_Governance"
scope: "Checkout_Closing_Flow"
trace_adr: ["ADR-001"]
trace_diagram: "FluxoPedido.puml"
version: "1.0"
---

# 📋 Definition of Done (DoD) - Checkout

## 🛠️ Lógica de Validação por Camada

### C1: HTTP / Controller
- [ ] **Auth & Idempotency:** Interceptação via `Slim Middleware`. `X-Idempotency-Key` ausente -> `HTTP 400`.
- [ ] **Duplicidade:** Capturar `DuplicateRequestException` vinda do domínio -> `HTTP 422` (JSON padronizado).
- [ ] **Sanitização:** Payload POST obrigatoriamente tipado em DTO antes do `Domain Service`.

### C2: Domínio & Aplicação
- [ ] **Redis Atomic:** Checagem e trava via operação atômica única: `SET key value NX EX 300`. Proibido `EXISTS` + `SET`.
- [ ] **In-Memory Logic:** Cálculos, cupons e regras exclusivamente em memória. Escrita em DB proibida nesta fase.
- [ ] **Async Side-Effects:** Disparar `OrderCreatedEvent` via `EventDispatcher` assíncrono. Falha na mensageria não bloqueia resposta.

### C3: Persistência & DB
- [ ] **Short Transaction:** `BEGIN TRANSACTION` restrito ao escopo do método `UoW::commit()`.
- [ ] **Rollback Safety:** Blocos físicos (`Mapper`, `QB`, `DAO`) encapsulados em `try/catch` com `ROLLBACK` explícito em exceções.
- [ ] **Imutabilidade:** Alteração de estado histórico via novos registros ou máquina de estados. Proibido `UPDATE` direto em dados históricos.

---

## 🧪 Testes & Quality Thresholds

- [ ] **Race Condition Test:** Automatizado. Simular disparos simultâneos com mesma `X-Idempotency-Key` -> 1 Sucesso, demais `HTTP 422`.
- [ ] **Transaction Mutation Test:** Validar que falha de `INSERT` no `DAO` mantém dados da `Unit of Work` limpos (sem estado sujo).
- [ ] **Coverage:** Mínimo 95% em `Domain Service (Idempotência)` e `Unit of Work`.

---

## 🔒 Segurança & Observabilidade

- [ ] **Data Masking:** Mascarar dados sensíveis de pagamento. Proibido escrita em logs corporativos.
- [ ] **Correlation ID:** Injetar `X-Idempotency-Key` no contexto do `Monolog` para rastreamento distribuído.
- [ ] **Metrics:** Incrementar contadores (`Counters`) específicos para requisições duplicadas bloqueadas.

---

## 🚀 PR Review Criteria (Revisão Oblíqua)

1. **Evidências:** Anexar logs do CI/CD ou prints provando bloqueio de clique duplo.
2. **Leaking:** Validar isolamento de camadas. Consultas SQL brutas fora da camada de persistência (`DAO`) -> Rejeitar PR.
