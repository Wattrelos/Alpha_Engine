# 📋 Documentação de Casos de Uso - Jornada do Cliente (Alpha Engine)

> Documento gerado a partir da especificação do diagrama PlantUML: [`UseCaseDiagramCustomer.puml`](file:///var/www/html/agsonhos/docs/business/use-cases/UseCaseDiagramCustomer.puml)

---

## 1. Visão Geral

Este documento descreve os Casos de Uso da jornada do cliente na loja virtual **Alpha Engine**, abrangendo desde a navegação anônima (Visitante) até os fluxos autenticados (Cliente Logado) e interações automáticas com o Gateway de Pagamento.

### 👥 Atores do Sistema

| Ator | Tipo | Descrição |
| :--- | :--- | :--- |
| **Visitante (`Guest`)** | Humano (Primário) | Usuário não autenticado que navega na loja, busca produtos, monta carrinho e pode realizar checkout guest. |
| **Cliente Logado (`Customer`)** | Humano (Primário) | Usuário autenticado que herda todas as permissões do *Visitante* (`Guest <|-- Customer`) e possui acesso exclusivo à gestão de pedidos e devoluções. |
| **Sistema / Gateway (`System`)** | Sistema (Secundário) | Ator externo responsável por autorizar e processar transações de pagamento (Cartão, PIX, Boleto). |

---

## 2. Diagrama de Relacionamentos

```
[Visitante (Guest)] ───(Herança)───► [Cliente Logado (Customer)]
        │                                    │
        ├─► UC_Nav (Navegar no Catálogo)      ├─► UC_Orders (Acompanhar Pedidos)
        ├─► UC_Search (Buscar Produtos)      └─► UC_Return (Solicitar Devolução)
        ├─► UC_Cart (Adicionar ao Carrinho)
        │     └── <<include>> ──► UC_Cart_Inc (Selecionar Variantes/Assinaturas)
        ├─► UC_Auth (Fazer Login / Cadastro)
        │     └── <<include>> ──► UC_Auth_Inc (Mesclar Carrinho na Sessão)
        └─► UC_Checkout (Realizar Checkout)
              ├── <<include>> ──► UC_Payment (Processar Pagamento) ◄── [Sistema / Gateway]
              ├── <<extend>>  ──► UC_Coupon (Aplicar Cupom de Desconto)
              └── <<extend>>  ──► UC_Checkout_Ext (Comprar como Visitante)
```

---

## 3. Especificação Detalhada dos Casos de Uso

### UC01 - Navegar no Catálogo (`UC_Nav`)
* **Ator Principal:** Visitante (`Guest`) / Cliente Logado (`Customer`).
* **Descrição:** Permite visualizar produtos agrupados por categorias, destaques e vitrines na loja virtual.
* **Pré-condições:** Nenhuma.
* **Fluxo Principal:**
  1. O usuário acessa a página inicial ou categoria da loja.
  2. O sistema exibe os produtos disponíveis com imagens, nomes e preços.
  3. O usuário seleciona um produto para visualizar a PDP (Página de Detalhes do Produto).
* **Rastreabilidade:** RF002, RF012.

---

### UC02 - Buscar Produtos (`UC_Search`)
* **Ator Principal:** Visitante (`Guest`) / Cliente Logado (`Customer`).
* **Descrição:** Permite efetuar buscas por termo e aplicar filtros avançados de preço, categoria, marca e atributos.
* **Pré-condições:** Nenhuma.
* **Fluxo Principal:**
  1. O usuário digita o termo desejado no campo de busca.
  2. O sistema exibe os resultados correspondentes indexados.
  3. O usuário aplica filtros (ex: faixa de preço, cor, especificação técnica).
  4. O sistema atualiza a listagem conforme os filtros aplicados.
* **Fluxo Alternativo (Nenhum Resultado):**
  1. O sistema não localiza itens correspondentes.
  2. Exibe mensagem informativa e sugestões de termos correlatos.
* **Rastreabilidade:** RF011.

---

### UC03 - Adicionar ao Carrinho (`UC_Cart`)
* **Ator Principal:** Visitante (`Guest`) / Cliente Logado (`Customer`).
* **Inclusão Obrigatória (`<<include>>`):** `UC04 - Selecionar Variantes/Assinaturas` (`UC_Cart_Inc`).
* **Descrição:** Adiciona um produto com suas variações/configurações ao carrinho de compras da sessão.
* **Pré-condições:** Produto disponível em estoque.
* **Fluxo Principal:**
  1. O usuário acessa a página do produto.
  2. **[<<include>> UC04]** O usuário seleciona as variantes necessárias (tamanho, cor, voltagem, m² ou plano).
  3. O usuário clica em "Adicionar ao Carrinho".
  4. O sistema valida o estoque em tempo real e calcula o subtotal.
  5. O item é adicionado ao carrinho da sessão.
* **Fluxo de Exceção (Sem Estoque):**
  1. O sistema informa a indisponibilidade do item e bloqueia o botão de compra.
* **Rastreabilidade:** RF004, RF006, RF009, RF012.

---

### UC04 - Selecionar Variantes/Assinaturas (`UC_Cart_Inc`)
* **Tipo:** Caso de uso incluído (`<<include>>` por `UC03`).
* **Descrição:** Garante que os atributos específicos do SKU ou modalidade de assinatura sejam definidos antes de incluir o item no carrinho.
* **Fluxo Principal:**
  1. Apresenta ao usuário os seletores de atributos (ex: voltagem, cor, dimensões ou periodicidade de assinatura).
  2. Valida a combinação de SKU selecionada.

---

### UC05 - Fazer Login / Cadastro (`UC_Auth`)
* **Ator Principal:** Visitante (`Guest`).
* **Inclusão Obrigatória (`<<include>>`):** `UC06 - Mesclar Carrinho na Sessão` (`UC_Auth_Inc`).
* **Descrição:** Autentica um usuário existente (E-mail/Senha ou OAuth2) ou registra uma nova conta.
* **Pré-condições:** Nenhuma.
* **Fluxo Principal:**
  1. O visitante acessa a tela de login ou cadastro.
  2. O visitante informa suas credenciais ou opta por login social.
  3. O sistema valida as credenciais e autentica a sessão como `Cliente Logado`.
  4. **[<<include>> UC06]** O sistema mescla o carrinho anônimo da sessão ao carrinho persistido do usuário.
* **Rastreabilidade:** RF014, RF015.

---

### UC06 - Mesclar Carrinho na Sessão (`UC_Auth_Inc`)
* **Tipo:** Caso de uso incluído (`<<include>>` por `UC05`).
* **Descrição:** Sincroniza os itens adicionados como visitante com a conta que acabou de se autenticar, prevenindo perda de itens.
* **Fluxo Principal:**
  1. Identifica itens presentes no carrinho convidado da sessão atual.
  2. Mescla com o carrinho salvo no banco de dados do usuário autenticado.

---

### UC07 - Realizar Checkout (`UC_Checkout`)
* **Ator Principal:** Visitante (`Guest`) / Cliente Logado (`Customer`).
* **Inclusão Obrigatória (`<<include>>`):** `UC12 - Processar Pagamento` (`UC_Payment`).
* **Extensões Opcionais (`<<extend>>`):** `UC08 - Aplicar Cupom de Desconto`, `UC09 - Comprar como Visitante`.
* **Descrição:** Etapa final da compra para confirmação dos itens, endereço de entrega, frete e pagamento.
* **Pré-condições:** Carrinho contendo ao menos um item válido.
* **Fluxo Principal:**
  1. O usuário inicia o checkout a partir do carrinho.
  2. [Opção Extend] O usuário pode acionar `UC09 (Comprar como Visitante)` se não estiver autenticado.
  3. O usuário informa/seleciona o endereço de entrega e o tipo de frete.
  4. [Opção Extend] O usuário pode acionar `UC08 (Aplicar Cupom de Desconto)`.
  5. O usuário seleciona o meio de pagamento (Cartão, Boleto, PIX).
  6. **[<<include>> UC12]** O sistema solicita o processamento do pagamento junto ao Gateway.
  7. Confirmado o pagamento, o pedido é criado e o número de confirmação é exibido.
* **Rastreabilidade:** RF010, RF017, RF018, RF020, RF021.

---

### UC08 - Aplicar Cupom de Desconto (`UC_Coupon`)
* **Tipo:** Caso de uso de extensão (`<<extend>>` de `UC07`).
* **Descrição:** Adiciona um desconto ao valor final da compra mediante código promocional válido.
* **Fluxo Principal:**
  1. No checkout, o usuário digita o código do cupom.
  2. O sistema valida regras do cupom (validade, valor mínimo, limite de uso).
  3. O valor total do checkout é recalculado com o desconto aplicado.

---

### UC09 - Comprar como Visitante (Guest) (`UC_Checkout_Ext`)
* **Tipo:** Caso de uso de extensão (`<<extend>>` de `UC07`).
* **Descrição:** Permite finalizar a compra solicitando apenas e-mail e dados de entrega/pagamento, sem exigir criação de senha.
* **Fluxo Principal:**
  1. No checkout, o visitante escolhe prosseguir sem criar conta.
  2. Informa e-mail de contato, CPF para NF-e e endereço de entrega.
  3. O fluxo de checkout segue normalmente para a etapa de pagamento.

---

### UC10 - Acompanhar Pedidos (`UC_Orders`)
* **Ator Principal:** Cliente Logado (`Customer`) *(Exclusivo)*.
* **Descrição:** Exibe o histórico completo de pedidos do cliente, status de faturamento (NF-e) e rastreamento de entrega.
* **Pré-condições:** Cliente autenticado.
* **Fluxo Principal:**
  1. O cliente acessa "Meus Pedidos".
  2. O sistema exibe a lista de pedidos com status atualizado (ex: Pago, Em Separação, Em Trânsito).
  3. O cliente pode visualizar detalhes do pedido, chave da NF-e ou código de rastreamento.
* **Rastreabilidade:** RF016, RF022.

---

### UC11 - Solicitar Devolução de Produto (`UC_Return`)
* **Ator Principal:** Cliente Logado (`Customer`) *(Exclusivo)*.
* **Descrição:** Inicia o processo de logística reversa e devolução dentro do prazo legal ou de garantia.
* **Pré-condições:** Cliente autenticado e pedido entregue dentro do prazo estipulado para devolução.
* **Fluxo Principal:**
  1. O cliente seleciona um pedido entregue na central de ajuda/atendimento.
  2. Escolhe os itens a serem devolvidos e o motivo da solicitação.
  3. O sistema gera a solicitação e exibe o código de autorização de postagem ou instruções de logística reversa.

---

### UC12 - Processar Pagamento (`UC_Payment`)
* **Tipo:** Caso de uso incluído (`<<include>>` por `UC07`).
* **Ator Secundário:** Sistema / Gateway (`System`).
* **Descrição:** Executa a autorização da transação financeira via Gateway (Cartão de Crédito, PIX ou Boleto Bancário).
* **Fluxo Principal:**
  1. O sistema envia a requisição de pagamento com os dados criptografados para o Gateway.
  2. O Gateway (`System`) autoriza a transação.
  3. O sistema atualiza o status do pedido para "Pagamento Aprovado" e aciona o faturamento.
* **Fluxo de Exceção (Pagamento Recusado):**
  1. O Gateway retorna recusa (ex: saldo insuficiente, dados incorretos).
  2. O sistema notifica o cliente e permite alterar o meio de pagamento sem perder o carrinho.
* **Rastreabilidade:** RF018, RF019, RF020.
