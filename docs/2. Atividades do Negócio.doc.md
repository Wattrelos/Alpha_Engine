# ***Alpha Engine: Plataforma E-commerce On-Premise & Ponto de Venda para Materiais de Construção***

***Documento de Modelagem de Atividades do Negócio e Engenharia de Processos***  
**Projeto:** Alpha Engine (Plataforma E-commerce On-Premise & Ponto de Venda para Materiais de Construção)

---

## 1. **Índice**

- [1. Índice](#1-índice)
- [2. Objetivo](#2-objetivo)
- [3. Atividades do Negócio (Visão Comercial e Operacional)](#3-atividades-do-negócio-visão-comercial-e-operacional)
  - [3.1. Atividade 1: Processo de Venda com Tratamento de Rejeição de Pagamento (POS)](#31-atividade-1-processo-de-venda-com-tratamento-de-rejeição-de-pagamento-pos)
    - [3.1.1. Diagrama de Atividades](#311-diagrama-de-atividades)
  - [3.2. Atividade 2: Atendimento Presencial no Balcão e Modalidades de Entrega (POS Balcão)](#32-atividade-2-atendimento-presencial-no-balcão-e-modalidades-de-entrega-pos-balcão)
    - [3.2.1. Diagrama de Atividades](#321-diagrama-de-atividades)
  - [3.3. Atividade 3: Fluxo de Compra e Árvore de Decisão do Checkout E-Commerce](#33-atividade-3-fluxo-de-compra-e-árvore-de-decisão-do-checkout-e-commerce)
    - [3.3.1. Diagrama de Atividades](#331-diagrama-de-atividades)
  - [3.4. Atividade 4: Ciclo de Vida e Processamento de Devolução (RMA / Logística Reversa)](#34-atividade-4-ciclo-de-vida-e-processamento-de-devolução-rma--logística-reversa)
    - [3.4.1. Diagrama de Atividades](#341-diagrama-de-atividades)
  - [3.5. Atividade 5: Gestão de Inventário e Baixa de Estoque Concorrente (Optimistic Locking)](#35-atividade-5-gestão-de-inventário-e-baixa-de-estoque-concorrente-optimistic-locking)
    - [3.5.1. Diagrama de Atividades](#351-diagrama-de-atividades)
  - [3.6. Atividade 6: Governança de Dados, Sanitização e Direito ao Esquecimento (LGPD)](#36-atividade-6-governança-de-dados-sanitização-e-direito-ao-esquecimento-lgpd)
    - [3.6.1. Diagrama de Atividades](#361-diagrama-de-atividades)
- [4. Atividades de Desenvolvedor (Infraestrutura, Segurança & Arquitetura)](#4-atividades-de-desenvolvedor-infraestrutura-segurança--arquitetura)
  - [4.1. Atividade 7: Autenticação Dual com Cache Redis e Fallback Gracioso para Sessão PHP](#41-atividade-7-autenticação-dual-com-cache-redis-e-fallback-gracioso-para-sessão-php)
    - [4.1.1. Diagrama de Sequência e Atividades](#411-diagrama-de-sequência-e-atividades)
  - [4.2. Atividade 8: Controle de Idempotência e Proteção de Filas Assíncronas (Anti-Duplo Clique)](#42-atividade-8-controle-de-idempotência-e-proteção-de-filas-assíncronas-anti-duplo-clique)
    - [4.2.1. Diagrama de Sequência e Atividades](#421-diagrama-de-sequência-e-atividades)
  - [4.3. Atividade 9: Tratamento de Falhas de Concorrência com Bloqueio Otimista e Invalidação de Cache](#43-atividade-9-tratamento-de-falhas-de-concorrência-com-bloqueio-otimista-e-invalidação-de-cache)
    - [4.3.1. Diagrama de Sequência e Atividades](#431-diagrama-de-sequência-e-atividades)
  - [4.4. Atividade 10: Esteira de Segurança HTTP e Pipeline de Middlewares do Slim 4 (Anti-CSRF, Rate Limit e RBAC)](#44-atividade-10-esteira-de-segurança-http-e-pipeline-de-middlewares-do-slim-4-anti-csrf-rate-limit-e-rbac)
    - [4.4.1. Diagrama de Atividades e Sequência de Segurança](#441-diagrama-de-atividades-e-sequência-de-segurança)

---

## 2. **Objetivo**

2.1. O objetivo deste documento é descrever e modelar formalmente as principais **Atividades do Negócio** e os **Processos de Engenharia de Software** da plataforma **Alpha Engine**, sob as óticas operacional (loja física e vendas presenciais em Ponto de Venda - POS), comercial (comércio eletrônico digital e checkout omnichannel) e técnica (mecanismos de infraestrutura, tolerância a falhas, concorrência e segurança).

2.2. A modelagem contempla as particularidades do ecossistema de varejo de materiais de construção no Brasil, considerando múltiplos meios de pagamento (PIX instantâneo, Cartão de Crédito com análise antifraude, Boleto Bancário e pagamento em balcão), controle rigoroso de estoque físico em tempo real, logística reversa em consonância com o Código de Defesa do Consumidor (CDC) e governança de dados pessoais alinhada à Lei Geral de Proteção de Dados (LGPD).

---

## 3. **Atividades do Negócio (Visão Comercial e Operacional)**

Abaixo estão detalhadas as operações essenciais executadas pelos atores humanos (Clientes, Vendedores, Operadores de Caixa, Gerentes de Atendimento e DPO) e pelos subsistemas de software da plataforma.

```
+-----------------------------------------------------------------------------------+
|                        MAPA DAS ATIVIDADES DO NEGÓCIO                             |
+------------------------------------+----------------------------------------------+
| 3.1 Venda POS com Rejeição Pagto.  | 3.2 Atendimento Balcão & Modalidades         |
| 3.3 Checkout E-Commerce (Árvore)   | 3.4 Devolução e Trocas (RMA / CDC)           |
| 3.5 Gestão Estoque Concorrente     | 3.6 Governança LGPD / Direito Esquecimento   |
+------------------------------------+----------------------------------------------+
```

---

### 3.1. Atividade 1: Processo de Venda com Tratamento de Rejeição de Pagamento (POS)

- **Objetivo do Processo:** Gerenciar o ciclo de atendimento presencial no caixa e balcão, desde a solicitação de itens pelo cliente, emissão de comanda/ticket de pré-venda, até o processamento financeiro no caixa, com políticas rigorosas de tratamento de rejeição de cartão, retentativas e cancelamento com devolução de mercadoria ao estoque.
- **Participantes / Atores:** Cliente, Vendedor de Balcão, Operador de Caixa, Sistema POS (Backend / Banco de Dados / Redis).
- **Regras de Negócio Aplicadas:** `RN005` (Controle rigoroso de estoque), `RN007` (Cubagem e pesagem), `RN016` (Desconto em pagamento à vista).

#### Descrição Sequencial da Atividade:
1. **Atendimento Inicial:** O Cliente dirige-se ao vendedor e solicita os produtos desejados. O Vendedor efetua a busca por código de barras, SKU ou descrição no terminal POS.
2. **Checagem de Disponibilidade:** Caso não haja saldo disponível no estoque físico, o Vendedor informa o cliente e encerra o fluxo; caso haja saldo, adiciona os produtos ao carrinho e vincula a identificação do Cliente (CPF/CNPJ).
3. **Persistência de Pré-Venda e Idempotência:** O Vendedor aciona o comando "Salvar Pedido". O backend verifica a chave de idempotência temporária no Redis para evitar duplicações por clique duplo, abre transação no MySQL e grava a pré-venda com status `Pendente`, gerando o identificador do pedido (ex: `#150`).
4. **Reserva Operacional Física:** Por diretriz operacional do varejo de materiais de construção, o vendedor retira as mercadorias das prateleiras e as acomoda em sacola/carrinho na área de checkout, assegurando a integridade dos itens até a quitação no caixa.
5. **Encaminhamento ao Caixa:** O Vendedor imprime o ticket de pré-venda e entrega ao Cliente, que se dirige ao caixa.
6. **Captura no Terminal do Caixa:** O Operador de Caixa digita ou efetua a leitura do código do ticket `#150`. O backend valida valores e integridade do pedido.
7. **Processamento do Pagamento:** O Caixa processa o pagamento na maquininha TEF / POS (Cartão de Crédito ou Débito).
8. **Bifurcação de Fluxo - Sucesso vs. Rejeição:**
   - **Fluxo de Sucesso (Aprovado):** O Caixa emite o cupom fiscal (NFC-e), o backend atualiza o status para `Pago`, efetua a baixa definitiva do saldo no banco de dados e o Caixa entrega as mercadorias ao Cliente.
   - **Fluxo de Exceção (Rejeitado por Saldo/Limite):** O Caixa notifica a rejeição. Se o Cliente optar por tentar outro meio (PIX, Dinheiro ou outro cartão), o sistema permite até **3 retentativas**. Caso o Cliente desista da compra:
     1. O Caixa clica em "Cancelar Pedido" no terminal.
     2. O backend altera o status para `Cancelado`.
     3. O sistema estorna qualquer reserva sistêmica e os produtos físicos são devolvidos imediatamente às prateleiras, reativando a visibilidade dos itens para o catálogo geral.

#### 3.1.1. **Diagrama de Atividades**

![Processo de Venda com Tratamento de Rejeição de Pagamento (POS)](/var/www/html/agsonhos/docs/workflows/activity_diagrams/ProcessodeVendacomTratamentodeRejeiçãodePagamento(POS).puml)

---

### 3.2. Atividade 2: Atendimento Presencial no Balcão e Modalidades de Entrega (POS Balcão)

- **Objetivo do Processo:** Modelar o atendimento tradicional em lojas de materiais de construção, oferecendo flexibilidade entre retirada física imediata no balcão e agendamento de entrega em domicílio para mercadorias pesadas/volumosas com cálculo prévio de frete.
- **Participantes / Atores:** Cliente Presencial, Vendedor de Balcão, Operador de Caixa, Sistema de Estoque / Banco de Dados.
- **Regras de Negócio Aplicadas:** `RN001` (Venda fracionada/m²), `RN002` (Peso/dimensões), `RN007` (Frete por cubagem), `RN008` (Retirada presencial).

#### Descrição Sequencial da Atividade:
1. **Abertura de Comanda:** O Cliente solicita itens fracionados ou por unidade ao Vendedor, que abre nova pré-venda no terminal.
2. **Seleção de Produtos:** O Vendedor efetua a leitura dos códigos de barras repetidamente até finalizar a lista. Se algum produto apresentar estoque insuficiente, o sistema notifica e permite substituição por marca equivalente ou encomenda.
3. **Definição da Modalidade Logística:**
   - *Modalidade Retirada Imediata (Pronta Entrega):* O vendedor separa as peças no balcão.
   - *Modalidade Entrega em Domicílio (Frete):* O vendedor insere o CEP de entrega do cliente e o sistema calcula a taxa de frete com base no peso e volume acumulados.
4. **Fechamento e Pagamento:** O Vendedor conclui a comanda e emite o QR Code. O Cliente se dirige ao Caixa, que efetua a leitura, seleciona a forma de pagamento (Cartão, PIX no visor do caixa com QR Code dinâmico ou Dinheiro com cálculo automático de troco).
5. **Finalização e Emissão Fiscal:** O Caixa confirma o pagamento, o sistema liquida o estoque físico na tabela `tbkk_product`, dispara a emissão de NFC-e e o cliente retira seus produtos ou recebe o comprovante de agendamento de entrega.

#### 3.2.1. **Diagrama de Atividades**

![Atendimento Presencial e Venda de Balcão (POS)](/var/www/html/agsonhos/docs/workflows/activity_diagrams/fluxo_venda_pos_balcao.puml)

---

### 3.3. Atividade 3: Fluxo de Compra e Árvore de Decisão do Checkout E-Commerce

- **Objetivo do Processo:** Estruturar a árvore de decisão do checkout digital no e-commerce On-Premise, gerenciando validações de sessão, seleção de endereços múltiplos, motor de cálculo de frete por cubagem (*ShippingStrategyManager*), aplicação de cupons de desconto, roteamento entre múltiplos meios de pagamento e execução de observers pós-venda.
- **Participantes / Atores:** Cliente Web/Mobile, Alpha Engine Backend, Gateways de Pagamento (PIX / Cartão / Boleto).
- **Regras de Negócio Aplicadas:** `RN002` (Peso/cubagem), `RN008` (Regras de frete grátis), `RN015` (Desconto por volume), `RN016` (Desconto no PIX).

#### Descrição Sequencial da Atividade:
1. **Acesso ao Checkout:** O Cliente clica em "Finalizar Compra" na rota `/checkout`. O backend valida a existência de itens no carrinho e disponibilidade de estoque. Em caso de inconsistência, redireciona para `/cart` com alerta.
2. **Endereço e Cotação de Frete:** O Cliente seleciona um de seus endereços cadastrados. O backend invoca o `ShippingStrategyManager`, consultando opções de frete (Frete Fixo, Frete por Peso/Transportadora ou Frete Grátis condicionado).
3. **Aplicação de Cupom Promocional:** O Cliente pode inserir código de cupom. O backend valida vigência, limite de utilizações e valor mínimo, aplicando a dedução ao subtotal.
4. **Roteamento de Pagamento:**
   - **Opção PIX:** A engine gera payload e QR Code dinâmico via `PixGateway` com prazo de expiração (15 minutos). O provedor bancário notifica via webhook HTTP 200 ao liquidar o valor. Se expirar sem quitação, a ordem é cancelada.
   - **Opção Cartão de Crédito:** Os dados do cartão são tokenizados no frontend e submetidos ao gateway (`MercadoPagoAdapter`). O sistema antifraude processa a transação; se recusada, o cliente pode selecionar outro cartão ou meio alternativo.
   - **Opção Boleto Bancário:** O sistema gera linha digitável com reserva temporária de estoque aguardando compensação bancária em até 3 dias úteis.
5. **Conclusão e Disparo de Eventos:** Com o pagamento aprovado, o status evolui para `PAGO / EM PROCESSAMENTO`. O sistema executa os observers assíncronos (`OrderEmailObserver` e `StockDeductionObserver`), limpa o carrinho da sessão e redireciona o cliente para `/checkout/success`.

#### 3.3.1. **Diagrama de Atividades**

![Árvore de Decisão e Workflow do Checkout E-Commerce](/var/www/html/agsonhos/docs/workflows/activity_diagrams/checkout_decision_tree.puml)

---

### 3.4. Atividade 4: Ciclo de Vida e Processamento de Devolução (RMA / Logística Reversa)

- **Objetivo do Processo:** Controlar as etapas de devolução e troca de mercadorias, assegurando o cumprimento das diretrizes do Código de Defesa do Consumidor (CDC Art. 49 para compras online com arrependimento em 7 dias, e garantia legal de 90 dias para bens duráveis) e garantindo a conferência física e fiscal antes do ressarcimento.
- **Participantes / Atores:** Cliente Solicitante, Equipe de Atendimento/Admin, Triagem de Estoque no CD, Gateway de Pagamento.
- **Regras de Negócio Aplicadas:** `RN009` (Política de devolução), `RN010` (Trocas de tintas e pisos), `RN011` (Direito de arrependimento e exceção BOPIS), `RN012` (Documentação e NF-e de entrada).

#### Descrição Sequencial da Atividade:
1. **Abertura do Chamado:** O Cliente acessa seu pedido em `/account/order/return`, preenche a justificativa da devolução e anexa fotografias do produto recebido.
2. **Triagem Administrativa Prévia:** A equipe de atendimento analisa o pedido confrontando com as regras do CDC e políticas de exceção (ex: recusa de arrependimento para retirada física presencial ou tintas preparadas). Se não aprovado, o chamado é indeferido com justificativa detalhada.
3. **Logística Reversa:** Aprovada a solicitação, o sistema gera o código de postagem reversa dos Correios/transportadora e despacha para o e-mail do cliente, que realiza a postagem da embalagem lacrada.
4. **Inspeção Física e Laudo:** Ao receber a encomenda no Centro de Distribuição, a equipe de triagem realiza a vistoria de integridade. Se o produto estiver violado, quebrado ou incompleto, emite laudo de recusa e reenvia ao remetente.
5. **Estorno Financeiro e Reintegração de Estoque:** Estando o material em perfeito estado, a equipe aprova a devolução: o item retorna ao saldo de estoque disponível, a NF-e de entrada é emitida e o cliente recebe o estorno no meio de origem (PIX/Cartão) ou um cupom de crédito (*vale-compras*).

#### 3.4.1. **Diagrama de Atividades**

![Processamento e Ciclo de Vida da Devolução (RMA)](/var/www/html/agsonhos/docs/workflows/activity_diagrams/processamento_devolucao_rma.puml)

---

### 3.5. Atividade 5: Gestão de Inventário e Baixa de Estoque Concorrente (Optimistic Locking)

- **Objetivo do Processo:** Garantir a consistência e atomicidade dos saldos de estoque sob picos de acesso concorrente (múltiplos compradores disputando os mesmos lotes promocionais), eliminando risco de *overselling* sem travar as tabelas para consultas de leitura.
- **Participantes / Componentes:** Cliente/Checkout, `ProductRepository`, `StockService`, Banco de Dados MySQL (`tbkk_product`).
- **Regras de Negócio Aplicadas:** `RN005` (Controle rigoroso de estoque), `RN006` (Alerta de stockout), `RNF002` (Desempenho), `RNF005` (Escalabilidade).

#### Descrição Sequencial da Atividade:
1. **Submissão de Reserva:** O checkout dispara o decremento do estoque para a quantidade $N$ solicitada.
2. **Leitura Não-Bloqueante:** O repositório realiza `SELECT product_id, quantity, version FROM tbkk_product`, obtendo o saldo e a versão atual ($v_{atual}$) sem reter travas pessimistas de linha.
3. **Tentativa de Escrita Otimista:** O serviço executa:
   $$\text{UPDATE tbkk\_product SET quantity = quantity - N, version = version + 1 WHERE product\_id = X AND version = } v_{atual}$$
4. **Avaliação do Resultado:**
   - **Caso 1 Linha Afetada (Sucesso):** A transação é comitada no MySQL, o registro de reserva temporária é gravado e os observers de auditoria são acionados.
   - **Caso 0 Linhas Afetadas (Colisão de Concorrência):** Significa que outro processo atualizou o registro simultaneamente. O sistema ativa o algoritmo de *Exponential Backoff* (aguarda $N$ milissegundos) e repete a tentativa por até **3 vezes**. Caso persista a colisão, exibe mensagem de alta demanda e convida o usuário a tentar novamente.

#### 3.5.1. **Diagrama de Atividades**

![Workflow de Reserva e Decremento de Estoque com Bloqueio Otimista](/var/www/html/agsonhos/docs/workflows/activity_diagrams/gestao_estoque_concorrente.puml)

---

### 3.6. Atividade 6: Governança de Dados, Sanitização e Direito ao Esquecimento (LGPD)

- **Objetivo do Processo:** Implementar o atendimento ao Artigo 18 da Lei Geral de Proteção de Dados (LGPD - Lei nº 13.709/2018), permitindo que o titular dos dados solicite a exclusão de suas informações pessoais com segurança e validação de retenção fiscal obrigatória.
- **Participantes / Componentes:** Titular dos Dados / DPO, Engine de Compliance (`LgpdSanitizer`), Camada de Persistência e Auditoria.
- **Regras de Negócio Aplicadas:** `RNF003` (Segurança e LGPD), `RN012` (Conformidade fiscal).

#### Descrição Sequencial da Atividade:
1. **Solicitação do Titular:** O cliente autenticado solicita o direito ao esquecimento em `/account/lgpd/forget`.
2. **Validação de Identidade (2FA):** O sistema exige validação com token de dois fatores enviado para o e-mail ou telefone cadastrado.
3. **Análise de Exceção Legal de Guarda Fiscal:** O sistema verifica se o cliente possui pedidos faturados nos últimos 5 anos.
   - *Com histórico recente:* Aplica-se o Art. 16, I da LGPD e o Código Tributário Nacional (CTN), mantendo estritamente os dados fiscais da nota fiscal vinculada em `tbkk_order`.
   - *Sem obrigações ativas:* Prossegue para anonimização total da conta.
4. **Sanitização de Dados:** O componente `LgpdSanitizer` mascara Nome, Sobrenome, CPF (`***.456.789-**`), E-mail (`u***@anon.lgpd`), descarta números de telefone, endereços secundários e tokens de cartão, e remove todas as sessões ativas no Redis (`sessao:cliente:*`).
5. **Auditoria e Confirmação:** O status do cliente é inativado (`status = 0`), a senha substituída por hash inválido e um protocolo imutável de conformidade é gravado na tabela `tbkk_lgpd_audit_log`, fornecendo ao titular o comprovante legal.

#### 3.6.1. **Diagrama de Atividades**

![Governança de Dados e Sanitização LGPD](/var/www/html/agsonhos/docs/workflows/activity_diagrams/sanitizacao_lgpd_anonimizacao.puml)

---

## 4. **Atividades de Desenvolvedor (Infraestrutura, Segurança & Arquitetura)**

Esta seção descreve os fluxos técnicos de arquitetura e resiliência projetados para garantir alta disponibilidade, segurança cibernética e integridade transacional na **Alpha Engine**.

```
+-----------------------------------------------------------------------------------+
|                     MAPA DAS ATIVIDADES DE DESENVOLVEDOR                          |
+------------------------------------+----------------------------------------------+
| 4.1 Autenticação Dual & Redis Fallb| 4.2 Idempotência & Fila RabbitMQ             |
| 4.3 Lock Otimista & Limpeza Cache  | 4.4 Pipeline Middlewares & Anti-CSRF         |
+------------------------------------+----------------------------------------------+
```

---

### 4.1. Atividade 7: Autenticação Dual com Cache Redis e Fallback Gracioso para Sessão PHP

- **Objetivo do Processo:** Assegurar que o sistema mantenha a capacidade de autenticar e manter sessões ativas de clientes e administradores mesmo em situações críticas de indisponibilidade ou queda do servidor Redis, efetuando degradação graciosa (*graceful fallback*) para sessões nativas do PHP sem interromper o serviço.
- **Componentes:** `SessionMiddleware` / `AdminSessionMiddleware`, `AbstractAuthService`, `CustomerAuthService`, `Predis\Client` (Redis 6379), `$_SESSION` nativa do PHP, MySQL.

#### Descrição Sequencial da Atividade:
1. **Modo Primário (Redis Ativo):** Na submissão de credenciais válidas em `/login`, o `AbstractAuthService` conecta ao Redis, gera identificador criptográfico (`sessionId = bin2hex(random_bytes(32))`), grava a chave `sessao:cliente:{sessionId}` com TTL de 7200s (2 horas) e emite cookie `HttpOnly; Secure`. Nas requisições subsequentes, o middleware lê o Redis em sub-milissegundos e injeta o usuário nas variáveis globais do Twig.
2. **Modo Fallback (Redis Inacessível):** Caso o Redis apresente erro de conexão (`Connection Refused` ou *Timeout*), o serviço captura a exceção, altera a flag interna `$useRedis = false`, inicializa a sessão nativa do PHP (`session_start()`), salva os dados em `$_SESSION['logged_user']` e retorna o identificador nativo `PHPSESSID`.
3. **Transparência:** O usuário final e a lógica dos controllers operam sem qualquer percepção de interrupção ou falha de infraestrutura.

#### 4.1.1. **Diagrama de Sequência e Atividades**

![Autenticação Dual com Redis e Fallback PHP Session](/var/www/html/agsonhos/docs/workflows/sequence_diagrams/autenticacao_redis_fallback.puml)

---

### 4.2. Atividade 8: Controle de Idempotência e Proteção de Filas Assíncronas (Anti-Duplo Clique)

- **Objetivo do Processo:** Evitar cobranças duplicadas de cartão de crédito, pedidos repetidos e saturação indevida de filas de mensageria (RabbitMQ) decorrentes de múltiplos cliques acidentais no botão de finalização de compra.
- **Componentes:** Frontend JS (`generateUUID()`), `CheckoutAction`, `IdempotencyService`, Redis Lock (`SETNX`), `UnitOfWork`, MySQL, `EventDispatcher`, Fila RabbitMQ (`order.created`).

#### Descrição Sequencial da Atividade:
1. **Geração da Chave:** Ao submeter a ordem, o cliente envia o header HTTP `X-Idempotency-Key: uuid-1234`.
2. **Aquisição de Lock Atômico:** O `IdempotencyService` executa no Redis: `SETNX("idempotency:uuid-1234", "PROCESSING", EX 300)`.
   - Se retornar `1` (primeira requisição): A trava é concedida e a transação no MySQL é iniciada via UnitOfWork.
   - Se retornar `0` (segundo clique simultâneo): O serviço dispara `IdempotencyLockException`, retornando imediatamente `HTTP 429 Too Many Requests` com código `DUPLICATE_REQUEST`, abortando o processamento secundário antes de tocar no banco de dados.
3. **Publicação Pós-Commit:** A mensagem para a fila RabbitMQ (`OrderCreatedEvent`) é despachada estritamente após o `COMMIT` bem-sucedido no MySQL, assegurando que workers assíncronos nunca processem dados de transações não persistidas.

#### 4.2.1. **Diagrama de Sequência e Atividades**

![Controle de Idempotência e Proteção de Fila](/var/www/html/agsonhos/docs/workflows/sequence_diagrams/falha_idempotencia.puml)

---

### 4.3. Atividade 9: Tratamento de Falhas de Concorrência com Bloqueio Otimista e Invalidação de Cache

- **Objetivo do Processo:** Resolver de forma determinística conflitos de alteração concorrente de entidades no painel administrativo (ex: dois operadores atualizando o status do mesmo chamado de devolução ao mesmo tempo), prevenindo que alterações desatualizadas sobrescrevam modificações recentes (*lost updates*).
- **Componentes:** `AdminSessionMiddleware`, `UpdateReturnStatusAction`, `OrderReturnRepository`, `OrderReturnMapper`, `UnitOfWork`, Redis Cache, MySQL.

#### Descrição Sequencial da Atividade:
1. **Tentativa com Versão Desatualizada:** O Administrador B tenta atualizar o chamado `#102` enviando a versão 1 (`version = 1`), porém o Administrador A já havia alterado o registro para versão 2 (`version = 2`).
2. **Detecção de Conflito:** O `OrderReturnMapper` executa a query com filtro de versão:
   $$\text{UPDATE agsc\_product\_return SET return\_status\_id = 5, version = 2 WHERE id = 102 AND version = 1}$$
   O MySQL retorna 0 linhas afetadas. O Mapper identifica a anomalia e lança `ConcurrencyException`.
3. **Rollback e Invalidação do Cache:** O UnitOfWork efetua o `ROLLBACK` da transação. O controller limpa o *Identity Map* em memória e purga a entrada em cache do Redis (`DEL "cache:product_return:102"`).
4. **Sincronização Reativa:** O repositório realiza nova leitura direta do banco e retorna a resposta `HTTP 409 Conflict` com os dados mais recentes para o frontend, que abre modal orientando o operador sobre a modificação prévia e atualiza os dados em tela automaticamente.

#### 4.3.1. **Diagrama de Sequência e Atividades**

![Tratamento de Falhas de Concorrência com Bloqueio Otimista](/var/www/html/agsonhos/docs/workflows/sequence_diagrams/lock_otimista_falha.puml)

---

### 4.4. Atividade 10: Esteira de Segurança HTTP e Pipeline de Middlewares do Slim 4 (Anti-CSRF, Rate Limit e RBAC)

- **Objetivo do Processo:** Implementar uma esteira de segurança em camadas na borda da aplicação, interceptando todas as requisições HTTP antes da execução dos controllers de negócio, mitigando ataques de força bruta, sequestro de requisições (*Cross-Site Request Forgery - CSRF*), injeção de métodos maliciosos e acessos não autorizados.
- **Componentes:** `CorsMiddleware`, `RateLimitMiddleware` (Redis), `CsrfGuardMiddleware` (`Slim\Csrf\Guard`), `SessionMiddleware` / `AdminSessionMiddleware` (RBAC), `LanguageMiddleware`, Twig View Engine, Slim Action.

#### Descrição Sequencial da Atividade:
1. **Camada 1 - CORS:** Valida origens permitidas e responde imediatamente requisições *Preflight* `OPTIONS` com `HTTP 204 No Content`.
2. **Camada 2 - Rate Limiting:** Consulta contadores por IP no Redis; caso o volume ultrapasse a taxa permitida por minuto, aborta com `HTTP 429 Too Many Requests`.
3. **Camada 3 - Proteção Anti-CSRF:** Para todos os métodos de mutação (`POST`, `PUT`, `DELETE`), valida os tokens `csrf_name` e `csrf_value` enviados no corpo ou cabeçalho contra os tokens registrados na sessão. Se inválidos ou expirados, invoca o `FailureHandler` customizado e retorna `HTTP 400 Bad Request`, impedindo a execução de qualquer ação ou alteração no banco.
4. **Camada 4 - Controle de Acesso Baseado em Papéis (RBAC):** Verifica se a rota requer autenticação e se o usuário logado possui as permissões necessárias para o recurso administrativo (`HTTP 401 Unauthorized` ou `HTTP 403 Forbidden`).
5. **Camada 5 - Contexto e Renderização:** Injeta configurações de idioma, status de login e quantidade do carrinho globalmente no Twig, despachando a requisição para a Slim Action correspondente.

#### 4.4.1. **Diagrama de Atividades e Sequência de Segurança**

![Pipeline de Execução de Middlewares HTTP do Slim 4](/var/www/html/agsonhos/docs/workflows/activity_diagrams/pipeline_seguranca_middleware.puml)

![Fluxo de Proteção Anti-CSRF com CsrfGuardMiddleware](/var/www/html/agsonhos/docs/workflows/sequence_diagrams/Middleware_anti_CSRF.puml)

---