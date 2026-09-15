# DP-84: Estruturação e Preenchimento do Documento de Atividades do Negócio

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-08-17 12:22:12
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/84

## Descrição

# Plano de Estruturação e Preenchimento do Documento de Atividades do Negócio

Consolidação formal de todas as rotinas operacionais, comerciais e de infraestrutura do sistema **Alpha Engine (E-commerce On-Premise de Materiais de Construção)** no arquivo [`Alpha. Atividades do Negócio.doc.md`](/docs/documentos_para_a_faculdade/Alpha.%20Atividades%20do%20Neg%C3%B3cio.doc.md).

---

## Estrutura do Documento Proposta

### 1. Atualização do Índice
- Atualização completa do sumário navegável com links âncora para todas as atividades de negócio e de desenvolvedor.

### 2. Seção 2: Objetivo
- Consolidação e aprimoramento da descrição do objetivo do documento para o contexto do varejo brasileiro de materiais de construção e comércio eletrônico omnichannel.

### 3. Seção 3: Atividades do Negócio (Fluxos Comerciais e Operacionais)
Cada atividade conterá: **Atores envolvidos**, **Descrição textual sequencial passo a passo**, **Regras de negócio aplicadas**, **Condições de exceção / quebras de fluxo**, e a vinculação direta ao **Diagrama de Atividades PlantUML**:

1. **3.1. Atividade 1: Venda Presencial no Ponto de Venda (POS) com Tratamento de Rejeição de Pagamento**
   - *Atores:* Cliente, Vendedor, Caixa, Sistema POS.
   - *Descrição:* Criação de pré-venda, controle de reserva física na sacola do balcão, tentativas de pagamento no caixa (cartão, PIX, dinheiro com política de até 3 retentativas), emissão de cupom fiscal ou cancelamento com liberação imediata do estoque.
   - *Diagrama:* `ProcessodeVendacomTratamentodeRejeiçãodePagamento(POS).puml`
2. **3.2. Atividade 2: Atendimento Presencial no Balcão e Modalidades de Entrega (POS Balcão)**
   - *Atores:* Cliente Presencial, Vendedor de Balcão, Operador de Caixa, Sistema de Estoque.
   - *Descrição:* Consulta de saldo físico em tempo real, geração de comanda, seleção de entrega imediata no balcão ou entrega em domicílio com cálculo de frete, pagamento no caixa e emissão de NFC-e.
   - *Diagrama:* `fluxo_venda_pos_balcao.puml`
3. **3.3. Atividade 3: Fluxo de Compra e Árvore de Decisão do Checkout E-Commerce**
   - *Atores:* Cliente Web/Mobile, Engine Backend (Alpha Engine), Gateway de Pagamento.
   - *Descrição:* Validação de carrinho, seleção de endereços múltiplos, cotação de frete por cubagem (*ShippingStrategyManager*), aplicação e validação de cupons de desconto, roteamento de pagamento (PIX com QR Code dinâmico, Cartão de Crédito com antifraude, Boleto com reserva temporária) e disparo de observers de pedido.
   - *Diagrama:* `checkout_decision_tree.puml`
4. **3.4. Atividade 4: Ciclo de Vida e Processamento de Devolução (RMA / Logística Reversa)**
   - *Atores:* Cliente, Equipe de Atendimento/Admin, Triagem de Estoque, Gateway de Pagamento.
   - *Descrição:* Solicitação de RMA pelo cliente com fotos/justificativa, análise da política de trocas e CDC (Art. 49 e 90 dias de garantia), geração de código de postagem reversa, recebimento no CD com laudo de qualidade, retorno do item ao saldo de estoque e estorno financeiro (PIX/Cartão) ou emissão de vale-compras.
   - *Diagrama:* `processamento_devolucao_rma.puml`
5. **3.5. Atividade 5: Gestão de Inventário e Baixa de Estoque Concorrente (Optimistic Locking)**
   - *Atores:* Cliente/Checkout, ProductRepository, StockService, Banco de Dados MySQL.
   - *Descrição:* Controle de concorrência sem bloqueio de leitura (*non-blocking SELECT*), atualização atômica verificando versão (`version = v_atual`), tratamento de colisões com *Exponential Backoff* (até 3 tentativas) e emissão de alertas de ruptura de estoque (*stockout*).
   - *Diagrama:* `gestao_estoque_concorrente.puml`
6. **3.6. Atividade 6: Governança de Dados, Sanitização e Direito ao Esquecimento (LGPD)**
   - *Atores:* Titular dos Dados / DPO, Engine de Compliance, Banco de Dados.
   - *Descrição:* Atendimento ao Art. 18 da LGPD, autenticação em duas etapas (2FA), análise de obrigações tributárias de guarda por 5 anos (Art. 16, I da LGPD e CTN), anonimização/mascaramento de campos pessoais (CPF, e-mail, telefone), destruição de tokens de cartão e invalidação de sessões ativas no Redis.
   - *Diagrama:* `sanitizacao_lgpd_anonimizacao.puml`

### 4. Seção 4: Atividades de Desenvolvedor (Infraestrutura, Segurança & Arquitetura)
Detalhamento dos mecanismos técnicos que sustentam a resiliência da aplicação:

1. **4.1. Atividade 7: Autenticação Dual com Cache Redis e Fallback Gracioso para Sessão PHP**
   - *Atores/Componentes:* Usuário/Admin, `SessionMiddleware`, `AbstractAuthService`, Redis (Predis 6379), `$_SESSION` nativa do PHP.
   - *Descrição:* Conexão primária em Redis para sessões distribuídas com TTL de 2 horas. Detecção de indisponibilidade (`Connection Refused`), captura de exceção e chaveamento automático (*failover*) para `$_SESSION` local sem interrupção para o usuário.
   - *Diagrama:* `autenticacao_redis_fallback.puml`
2. **4.2. Atividade 8: Controle de Idempotência e Proteção de Filas Assíncronas (Anti-Duplo Clique)**
   - *Atores/Componentes:* Cliente, `CheckoutAction`, `IdempotencyService`, Redis Lock (`SETNX`), UnitOfWork, MySQL, RabbitMQ.
   - *Descrição:* Geração de `X-Idempotency-Key` no frontend, aquisição de lock atômico no Redis com TTL de 300s, bloqueio de requisições concorrentes idênticas (HTTP 429 Too Many Requests) e publicação de eventos assíncronos estritamente pós-commit no banco.
   - *Diagrama:* `falha_idempotencia.puml`
3. **4.3. Atividade 9: Tratamento de Falhas de Concorrência com Bloqueio Otimista e Invalidação de Cache**
   - *Atores/Componentes:* Administrador, `AdminSessionMiddleware`, `UpdateReturnStatusAction`, `OrderReturnRepository`, Redis, MySQL.
   - *Descrição:* Detecção de edição simultânea via versão de linha em banco relacional, captura de `ConcurrencyException` por 0 linhas afetadas, rollback da transação, limpeza do *Identity Map* e cache de entidades no Redis (`DEL cache:*`), retornando HTTP 409 Conflict com payload atualizado para o frontend.
   - *Diagrama:* `lock_otimista_falha.puml`
4. **4.4. Atividade 10: Esteira de Segurança HTTP e Pipeline de Middlewares do Slim 4 (Anti-CSRF, Rate Limit e RBAC)**
   - *Atores/Componentes:* Requisição HTTP, `CorsMiddleware`, `RateLimitMiddleware`, `CsrfGuardMiddleware`, `SessionMiddleware`, Twig View Engine, Slim Action.
   - *Descrição:* Execução encadeada de middlewares: validação de CORS e preflight OPTIONS, limite de requisições por IP no Redis (HTTP 429), proteção de integridade anti-CSRF com tokens únicos para métodos de mutação (POST/PUT/DELETE) rejeitando requisições maliciosas com HTTP 400 antes do controller, validação de permissões RBAC e injeção de contexto global no Twig.
   - *Diagrama:* `pipeline_seguranca_middleware.puml` (com detalhamento em `Middleware_anti_CSRF.puml`)

---

## Verificação
- Revisar a aderência aos diagramas `.puml` presentes em `docs/workflows/activity_diagrams/` e `docs/workflows/sequence_diagrams/`.
- Garantir a clareza da redação acadêmica e a precisão da nomenclatura técnica (Slim 4, Redis, RabbitMQ, MySQL, RBAC, CDC, LGPD).


Ambos os documentos acadêmicos do projeto **Alpha Engine (E-commerce On-Premise de Materiais de Construção)** foram totalmente estruturados, preenchidos e validados:

1. [`Alpha. Requisitos do Sistema.doc.md`](/docs/documentos_para_a_faculdade/Alpha.%20Requisitos%20do%20Sistema.doc.md)
2. [`Alpha. Atividades do Negócio.doc.md`](/docs/documentos_para_a_faculdade/Alpha.%20Atividades%20do%20Neg%C3%B3cio.doc.md)

---

## 1. Documento de Requisitos do Sistema (`Alpha. Requisitos do Sistema.doc.md`)

- **Objetivo & Metodologia:** Contextualização do modelo On-Premise para varejo de construção no Brasil, técnicas de elucidação, conformidade legal (CDC, LGPD e SEFAZ).
- **25 Requisitos Funcionais (RF001 a RF025):** Tabelas individuais com regras de negócio e especificações de implementação divididas em 6 módulos funcionais.
- **8 Requisitos Não Funcionais (RNF001 a RNF008):** Tabelas com critérios de aceitação mensuráveis e estratégias técnicas (LCP < 2.5s, 500 RPS, SLA 99.9%, TLS 1.3, Apache `.htaccess`).
- **18 Regras de Negócio (RN001 a RN018):** Detalhamento de venda fracionada por m²/cx/kg, cubagem, restrições CDC com exceção BOPIS, estoques realtime e precificação B2B/B2C.
- **Matrizes de Rastreabilidade:** Mapeamento bidirecional RF x RN e RF x RNF.

---

## 2. Documento de Atividades do Negócio (`Alpha. Atividades do Negócio.doc.md`)

### Atividades de Negócio (Operação Comercial e Experiência do Usuário):
1. **Atividade 1: Venda Presencial no Ponto de Venda (POS) com Tratamento de Rejeição de Pagamento**
   - *Diagrama:* `ProcessodeVendacomTratamentodeRejeiçãodePagamento(POS).puml`
   - *Escopo:* Criação de pré-venda, sacola de reserva física, retentativas de pagamento no caixa (até 3 tentativas) e cancelamento com estorno imediato ao catálogo.
2. **Atividade 2: Atendimento Presencial no Balcão e Modalidades de Entrega (POS Balcão)**
   - *Diagrama:* `fluxo_venda_pos_balcao.puml`
   - *Escopo:* Consulta de saldo físico em tempo real, separação de balcão vs. frete em domicílio com cubagem, pagamento no caixa e emissão de NFC-e.
3. **Atividade 3: Fluxo de Compra e Árvore de Decisão do Checkout E-Commerce**
   - *Diagrama:* `checkout_decision_tree.puml`
   - *Escopo:* Validação de carrinho, múltiplos endereços, motor de frete `ShippingStrategyManager`, cupons, roteamento PIX/Cartão/Boleto e disparo de observers.
4. **Atividade 4: Ciclo de Vida e Processamento de Devolução (RMA / Logística Reversa)**
   - *Diagrama:* `processamento_devolucao_rma.puml`
   - *Escopo:* Abertura de chamado com fotos, triagem jurídica (Art. 49 CDC e garantia legal de 90 dias), código de postagem, inspeção física no CD, estorno financeiro ou vale-compras.
5. **Atividade 5: Gestão de Inventário e Baixa de Estoque Concorrente com Bloqueio Otimista**
   - *Diagrama:* `gestao_estoque_concorrente.puml`
   - *Escopo:* Leitura não-bloqueante (*non-blocking SELECT*), atualização atômica com versão de linha (`version = v_atual`), retentativas com *Exponential Backoff* e prevenção de *overselling*.
6. **Atividade 6: Governança de Dados, Sanitização e Direito ao Esquecimento (LGPD)**
   - *Diagrama:* `sanitizacao_lgpd_anonimizacao.puml`
   - *Escopo:* Atendimento ao Art. 18 da LGPD, autenticação 2FA, regras de guarda fiscal de 5 anos (Art. 16, I e CTN), anonimização com `LgpdSanitizer` e log imutável de auditoria.

### Atividades de Desenvolvedor (Infraestrutura, Resiliência e Segurança):
7. **Atividade 7: Autenticação Dual com Cache Redis e Fallback Gracioso para Sessão PHP**
   - *Diagrama:* `autenticacao_redis_fallback.puml`
   - *Escopo:* Sessões distribuídas em Redis (porta 6379 com TTL de 2h) com captura de falha de conexão e chaveamento automático (*failover*) para `$_SESSION` nativa do PHP.
8. **Atividade 8: Controle de Idempotência e Proteção de Filas Assíncronas (Anti-Duplo Clique)**
   - *Diagrama:* `falha_idempotencia.puml`
   - *Escopo:* Cabeçalho `X-Idempotency-Key`, trava atômica no Redis (`SETNX`), bloqueio HTTP 429 para cliques simultâneos e publicação em RabbitMQ estritamente pós-commit.
9. **Atividade 9: Tratamento de Falhas de Concorrência com Bloqueio Otimista e Invalidação de Cache**
   - *Diagrama:* `lock_otimista_falha.puml`
   - *Escopo:* Detecção de conflitos concorrentes via `version`, captura de `ConcurrencyException`, rollback transacional, expurgo de cache no Redis (`DEL cache:*`) e resposta HTTP 409 Conflict para sincronização em tela.
10. **Atividade 10: Esteira de Segurança HTTP e Pipeline de Middlewares do Slim 4**
    - *Diagramas:* `pipeline_seguranca_middleware.puml` e `Middleware_anti_CSRF.puml`
    - *Escopo:* Intercepção em camadas com validação CORS/OPTIONS, limitação de taxa por IP no Redis (Rate Limit), proteção Anti-CSRF com tokens de sessão para métodos de mutação (rejeição HTTP 400 antes do controller) e controle de acesso RBAC.

---

## Verificação de Entregas

- [`Alpha. Requisitos do Sistema.doc.md`](/docs/documentos_para_a_faculdade/Alpha.%20Requisitos%20do%20Sistema.doc.md): 605 linhas, sumário completo, tabelas padronizadas de RF/RNF, 18 regras de negócio e matrizes de rastreabilidade.
- [`Alpha. Atividades do Negócio.doc.md`](/docs/documentos_para_a_faculdade/Alpha.%20Atividades%20do%20Neg%C3%B3cio.doc.md): 278 linhas, sumário navegável, 10 atividades detalhadas passo a passo com referências aos diagramas PlantUML (`.puml`).

