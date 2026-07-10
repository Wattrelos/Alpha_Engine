<!-- 
Obrigado por enviar este Pull Request! Antes de solicitar a revisão, 
certifique-se de preencher todas as seções abaixo para garantir 
a segurança e a integridade do fluxo de checkout.
-->

## 🎯 Descrição da Alteração
<!-- Descreva de forma sucinta o que este PR altera no fluxo de Checkout. -->


## 🔗 Artefatos de Referência
*   **ADR Vinculado:** [ADR-001: Idempotência e Resiliência Transacional](../docs/architecture/ADR_Idempotency_UoW.md)
*   **Diagrama de Sequência:** [FluxoDidaticoPedido.puml](../docs/architecture/diagrams/FluxoDidaticoPedido.puml)

---

## 🛠️ Autochecklist do Desenvolvedor (Obrigatório)

Por se tratar de uma operação crítica (escrita financeira/estoque), marque os itens abaixo para confirmar o cumprimento das regras arquiteturais:

### 1. Camada 1 & 2: Idempotência e Domínio
- [ ] O cabeçalho `X-Idempotency-Key` é obrigatório na rota alterada.
- [ ] A validação no Redis utiliza operação atômica (`SET NX EX`).
- [ ] Requisições duplicadas lançam `DuplicateRequestException` e retornam `HTTP 422`.
- [ ] Toda a regra de negócio/cálculos ocorre em memória antes da abertura da transação.

### 2. Camada 3: Persistência e Transação
- [ ] O comando `BEGIN TRANSACTION` ocorre estritamente dentro do escopo final do `UoW::commit()`.
- [ ] Existe um bloco `try/catch` garantindo o `ROLLBACK` imediato do MySQL em caso de falhas.
- [ ] Nenhum serviço externo (chamada HTTP, API de pagamento ou mensageria síncrona) está dentro do bloco de transação do banco de dados.

### 3. Qualidade e Observabilidade
- [ ] Os testes unitários cobrem o cenário de concorrência / clique duplo (Race Condition).
- [ ] A cobertura de testes das classes de Idempotência e UoW está acima de 95%.
- [ ] A chave `X-Idempotency-Key` está configurada como ID de correlação nos logs gerados.

---

## 📸 Evidências de Teste (Anexar)

### Teste de Concorrência (Clique Duplo)
<!-- Cole aqui o log do terminal ou o resultado do teste automatizado provando que a segunda requisição simultânea recebeu o HTTP 422. -->
```logs
// Cole os logs ou insira um print aqui
```

### Teste de Rollback em caso de falha física
<!-- Comprovação de que, caso ocorra um erro no DAO/Mapper, a transação sofreu Rollback e não deixou dados parciais. -->
```logs
// Cole os logs ou insira um print aqui
```

---

## 🕵️‍♂️ Notas para o Revisor (Reviewer)
<!-- Indique pontos específicos do código que exigem maior atenção na validação da segurança ou concorrência. -->
