# DP-80: Criação dos Arquivos Gherkin (.feature) para Cart e Checkout

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-08-16 17:02:25
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/80

## Descrição

# Plano de Implementação: Criação dos Arquivos Gherkin (.feature) para Cart e Checkout

Estruturação completa das especificações executáveis em BDD/Gherkin para os módulos de **Carrinho (`features/cart/`)** e **Checkout (`features/checkout/`)**, garantindo cobertura integral dos requisitos funcionais, regras de negócio (RN001 a RN018), proteção de segurança (CSRF/Idempotência) e total alinhamento com a arquitetura Alpha Engine.

---

## 📋 Arquivos e Cenários Propostos

### 🛒 1. Módulo Carrinho (`features/cart/`)

1. [NEW] [adicionar_produto.feature](file:///var/www/html/agsonhos/features/cart/adicionar_produto.feature)
   - **Cenários**:
     - Adicionar produto simples ao carrinho com cálculo do subtotal.
     - Adicionar produto com seleção de variantes obrigatórias (voltagem, cor).
     - Tentativa de adicionar produto sem selecionar opções obrigatórias (validação).
     - Tentativa de adicionar quantidade superior ao estoque em tempo real (RN005 / RF006).
     - Adicionar produto com precificação por metro quadrado ($m^2$) / unidade fracionada (RN001 / RF004).
     - Adicionar combo / kit de produtos promocionais (RN004 / RF007).

2. [NEW] [gerenciar_itens_carrinho.feature](file:///var/www/html/agsonhos/features/cart/gerenciar_itens_carrinho.feature)
   - **Cenários**:
     - Alterar quantidade de um item existente no carrinho (incrementar e decrementar).
     - Remover um item do carrinho.
     - Esvaziar carrinho completamente.
     - Recalcular dinamicamente os totais do carrinho após alterações.

3. [NEW] [calculo_frete_carrinho.feature](file:///var/www/html/agsonhos/features/cart/calculo_frete_carrinho.feature)
   - **Cenários**:
     - Simular cálculo de frete por CEP de destino considerando dimensões e peso (RN002 / RF010).
     - Seleção de modalidade de frete (Correios para itens leves vs Transportadora para materiais pesados - RN007 / RF021).
     - Aplicação de frete grátis ao atingir o valor mínimo da região (RN008).
     - Seleção da opção Retirada na Loja Física (BOPIS) com frete zerado (RN008 / RF021).

4. [NEW] [sincronizacao_carrinho.feature](file:///var/www/html/agsonhos/features/cart/sincronizacao_carrinho.feature)
   - **Cenários**:
     - Mesclagem automática do carrinho de visitante (anônimo) com a conta ao efetuar login (UC06 / RF014).
     - Atualização das quantidades de itens duplicados durante a mesclagem.
     - Preservação do carrinho no banco de dados para o cliente logado.

---

### 💳 2. Módulo Checkout (`features/checkout/`)

1. [NEW] [fluxo_checkout_cliente.feature](file:///var/www/html/agsonhos/features/checkout/fluxo_checkout_cliente.feature)
   - **Cenários**:
     - Acessar checkout com cliente autenticado e validar carregamento de endereços cadastrados (RF017).
     - Validação obrigatória de proteção contra CSRF no carregamento e submissão (RF018 / `CsrfGuardMiddleware`).
     - Aplicar cupom de desconto válido no resumo do checkout (UC08 / RN018).
     - Tentativa de aplicar cupom expirado ou abaixo do valor mínimo exigido.
     - Resumo financeiro completo com detalhamento de Subtotal, Frete, Desconto e Total Geral.

2. [NEW] [checkout_visitante_guest.feature](file:///var/www/html/agsonhos/features/checkout/checkout_visitante_guest.feature)
   - **Cenários**:
     - Concluir checkout rápido como visitante (Guest) sem criação prévia de conta (UC09).
     - Validação de campos obrigatórios de faturamento e entrega (Nome, CPF/CNPJ, E-mail, CEP, Endereço).
     - Registro do pedido e envio de notificação/rastreio para o e-mail do visitante.

3. [NEW] [processamento_pagamentos.feature](file:///var/www/html/agsonhos/features/checkout/processamento_pagamentos.feature)
   - **Cenários**:
     - Finalizar pedido com pagamento via PIX com desconto à vista (RN016 / RF018).
     - Finalizar pedido com pagamento via Boleto Bancário.
     - Finalizar pedido com Cartão de Crédito aprovado pelo Gateway com transição de status para 'Pago' (RF019 / `ProcessPaymentAction`).
     - Falha/Recusa de pagamento pelo Gateway -> Rollback atômico da transação (Unit of Work), exibição de mensagem de erro e manutenção do carrinho ativo para retentativa.
     - Disparo do evento de domínio `order.created` para fila RabbitMQ e emissão automática de NF-e (RF020 / RN012).

4. [NEW] [idempotencia_checkout.feature](file:///var/www/html/agsonhos/features/checkout/idempotencia_checkout.feature)
   - **Cenários**:
     - Submissão com header `X-Idempotency-Key` único com sucesso (HTTP 201).
     - Bloqueio de requisições concorrentes duplicadas com mesma chave via Redis Lock (HTTP 429 / 422 `DUPLICATE_REQUEST`).
     - Garantia de não duplicidade de ordens no MySQL e mensagens na fila RabbitMQ.

---

### 🧪 3. Atualização dos Contextos Behat (`features/bootstrap/`)

1. [MODIFY] [CheckoutContext.php](file:///var/www/html/agsonhos/features/bootstrap/CheckoutContext.php) ou [FeatureContext.php](file:///var/www/html/agsonhos/features/bootstrap/FeatureContext.php)
   - Implementar/estender os Step Definitions correspondentes aos novos cenários do Cart e Checkout para garantir que a suíte execute 100% verde (`passed`) ao rodar `./vendor/bin/behat --no-snippets`.

---

## 🔍 Plano de Verificação

### Execução Automatizada
- Rodar o comando do Behat para validar a compilação de todas as features e execução dos cenários:
  ```bash
  ./vendor/bin/behat --no-snippets
  ```
- Validar se todas as tags e cenários em `features/cart/` e `features/checkout/` são identificados corretamente.

# Walkthrough: Arquivos Gherkin (.feature) para Cart e Checkout

Estruturamos e implementamos as especificações executáveis em BDD com a sintaxe **Gherkin** (em Português) para os cenários de **Carrinho (`features/cart/`)** e **Checkout (`features/checkout/`)**, com cobertura completa de requisitos funcionais, regras de negócio e contextos de teste no **Behat**.

---

## 📁 Estrutura de Arquivos Criados e Atualizados

```
features/
├── cart/
│   ├── adicionar_produto.feature         # Adição simples, variantes, controle de estoque realtime, m² e combos/kits
│   ├── gerenciar_itens_carrinho.feature  # Incremento, decremento, remoção unitária e esvaziamento total
│   ├── calculo_frete_carrinho.feature    # Cálculo por CEP, Correios vs Transportadora, Frete Grátis e Retirada na Loja (BOPIS)
│   └── sincronizacao_carrinho.feature    # Mesclagem automática pós-login (visitante -> cliente cadastrado)
├── checkout/
│   ├── fluxo_checkout_cliente.feature    # Múltiplos endereços, geração/validação de CSRF e cupons de desconto
│   ├── checkout_visitante_guest.feature  # Compra rápida como visitante com validação de dados obrigatórios e CPF
│   ├── processamento_pagamentos.feature  # PIX (desconto à vista), Boleto, Cartão de Crédito, rollback via Unit of Work e RabbitMQ
│   └── idempotencia_checkout.feature     # Prevenção de duplicidade com Redis Lock e cabeçalho X-Idempotency-Key
├── bootstrap/
│   ├── CartContext.php                   # [NOVO] Step definitions para catálogo, carrinho, frete e mesclagem
│   └── CheckoutContext.php               # [ATUALIZADO] Step definitions para checkout, CSRF, idempotência e pagamentos
├── reame.md                              # [ATUALIZADO] Documentação e matriz de rastreabilidade
└── behat.yml                             # [ATUALIZADO] Registro da suite com CartContext
```

---

## 🧪 Resumo dos Cenários Criados

### 🛒 Módulo Carrinho (`features/cart/`)

| Arquivo | Cenários Cobertos | Requisitos / Regras |
| :--- | :--- | :--- |
| [adicionar_produto.feature](file:///var/www/html/agsonhos/features/cart/adicionar_produto.feature) | - Adicionar produto simples com subtotal<br>- Adicionar produto com variantes obrigatórias<br>- Bloqueio sem variante<br>- Bloqueio por estouro de estoque em tempo real<br>- Venda fracionada por $m^2$<br>- Adição de Kit / Combo promocional | `RF004`, `RF006`, `RF007`, `RF009`, `RN001`, `RN003`, `RN004`, `RN005` |
| [gerenciar_itens_carrinho.feature](file:///var/www/html/agsonhos/features/cart/gerenciar_itens_carrinho.feature) | - Incrementar quantidade<br>- Decrementar quantidade<br>- Remover item individual<br>- Reduzir para zero (exclusão)<br>- Esvaziar carrinho por completo | `RF009` |
| [calculo_frete_carrinho.feature](file:///var/www/html/agsonhos/features/cart/calculo_frete_carrinho.feature) | - Simulação Correios (SEDEX/PAC) para itens leves<br>- Seleção automática de Transportadora para carga pesada (>30kg)<br>- Frete Grátis por valor mínimo regional<br>- Retirada na Loja Física (BOPIS) com frete R$ 0,00<br>- Validação de formato de CEP | `RF010`, `RF021`, `RN002`, `RN007`, `RN008` |
| [sincronizacao_carrinho.feature](file:///var/www/html/agsonhos/features/cart/sincronizacao_carrinho.feature) | - Mesclagem automática do carrinho visitante com a conta pós-login<br>- Soma de quantidades para itens duplicados<br>- Preservação do carrinho salvo após logout | `RF014`, `UC06` |

---

### 💳 Módulo Checkout (`features/checkout/`)

| Arquivo | Cenários Cobertos | Requisitos / Regras |
| :--- | :--- | :--- |
| [fluxo_checkout_cliente.feature](file:///var/www/html/agsonhos/features/checkout/fluxo_checkout_cliente.feature) | - Carregamento de múltiplos endereços salvos<br>- Proteção CSRF obrigatória<br>- Aplicação de cupom promocional válido<br>- Rejeição de cupom inválido/expirado<br>- Resumo financeiro transparente e discriminado | `RF017`, `RF018`, `RN018`, `UC07`, `UC08` |
| [checkout_visitante_guest.feature](file:///var/www/html/agsonhos/features/checkout/checkout_visitante_guest.feature) | - Finalização expressa de compra por comprador visitante (Guest)<br>- Bloqueio por campos obrigatórios vazios<br>- Rejeição de documento CPF inválido | `RF018`, `UC09` |
| [processamento_pagamentos.feature](file:///var/www/html/agsonhos/features/checkout/processamento_pagamentos.feature) | - Pagamento via PIX com desconto à vista e QRCode dinâmico<br>- Pagamento via Boleto Bancário<br>- Cartão de Crédito aprovado com commit via UnitOfWork<br>- Recusa de cartão com rollback atômico e carrinho preservado<br>- Publicação assíncrona do evento `order.created` no RabbitMQ para emissão de NF-e | `RF018`, `RF019`, `RF020`, `RN012`, `RN016`, `UC12` |
| [idempotencia_checkout.feature](file:///var/www/html/agsonhos/features/checkout/idempotencia_checkout.feature) | - Processamento normal da primeira submissão com `X-Idempotency-Key`<br>- Bloqueio imediato de segunda submissão concorrente via Redis Lock (HTTP 429/422)<br>- Tratamento resiliente do Frontend para evitar mensagens de erro | Resiliência & Idempotência |

---

## 🚀 Resultados dos Testes Behat

Execução executada diretamente no terminal do projeto:

```bash
# Testes do Módulo Carrinho
./vendor/bin/behat features/cart/ --no-snippets
# Resultado: 19 cenários (19 passaram) / 132 passos (132 passaram) ✅

# Testes do Módulo Checkout
./vendor/bin/behat features/checkout/ --no-snippets
# Resultado: 16 cenários (16 passaram) / 134 passos (134 passaram) ✅
```

