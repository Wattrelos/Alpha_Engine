# language: pt

Funcionalidade: Jornada e Casos de Uso do Cliente na Loja Virtual (Alpha Engine)
  Como um visitante ou cliente logado da plataforma Alpha Engine
  Eu quero navegar, buscar produtos, gerenciar meu carrinho e realizar compras
  Para que eu possa adquirir produtos com segurança e praticidade

  Contexto:
    Dado que o sistema "Alpha Engine - Loja Virtual" está ativo e operacional

  # ============================================================================
  # UC01: Navegar no Catálogo & UC02: Buscar Produtos
  # ============================================================================
  Cenário: Navegação e busca no catálogo por um visitante anônimo
    Dado que eu sou um "Visitante" navegando na loja virtual
    Quando eu busco pelo termo "Piso Porcelanato"
    E eu aplico o filtro de categoria "Pisos e Revestimentos" com faixa de preço de "50" a "150"
    Então o sistema deve exibir a listagem de produtos correspondentes
    E ao selecionar um item, a Página de Detalhes do Produto (PDP) deve ser exibida

  # ============================================================================
  # UC03: Adicionar ao Carrinho & UC04: Selecionar Variantes/Assinaturas (<<include>>)
  # ============================================================================
  Cenário: Adicionar produto ao carrinho com seleção obrigatória de variante
    Dado que eu sou um "Visitante" na página de detalhes de um produto com variações
    Quando eu seleciono a variante de voltagem "220V" e a cor "Preto Matte"
    E eu clico no botão "Adicionar ao Carrinho"
    Então o sistema deve validar a disponibilidade de estoque em tempo real
    E o item com a variante "220V / Preto Matte" deve ser adicionado ao carrinho da sessão
    E o subtotal do carrinho deve ser atualizado com sucesso

  # ============================================================================
  # UC05: Fazer Login / Cadastro & UC06: Mesclar Carrinho na Sessão (<<include>>)
  # ============================================================================
  Cenário: Autenticação de cliente e mesclagem automática do carrinho visitante
    Dado que eu sou um "Visitante" e possuo "2" itens no meu carrinho anônimo da sessão
    Quando eu realizo o login com minhas credenciais válidas "cliente@email.com" e "senha123"
    Então minha sessão deve ser convertida para "Cliente Logado"
    E o sistema deve mesclar automaticamente os "2" itens do carrinho visitante com o carrinho persistido da minha conta
    E o meu carrinho atualizado deve conter a união de todos os itens ativos

  # ============================================================================
  # UC07: Realizar Checkout & UC12: Processar Pagamento (<<include>>)
  # ============================================================================
  Cenário: Realizar checkout completo com pagamento aprovado pelo Gateway
    Dado que eu sou um "Cliente Logado" com itens em meu carrinho
    Quando eu prossigo para o checkout e informo o endereço de entrega
    E eu seleciono a modalidade de pagamento "PIX"
    E eu confirmo a finalização do pedido
    Então o sistema deve acionar o Gateway "System" para processar o pagamento
    E assim que o Gateway confirmar a transação, o status do pedido deve ser alterado para "Pagamento Aprovado"
    E a nota fiscal (NF-e) deve ser encaminhada para emissão automática

  # ============================================================================
  # UC08: Aplicar Cupom de Desconto (<<extend>> UC07)
  # ============================================================================
  Cenário: Aplicar cupom de desconto válido durante o fluxo de checkout
    Dado que eu estou na etapa de resumo do checkout
    Quando eu insiro o código promocional "PRIMEIRACOMPRA10" no campo de cupom
    E o sistema valida que o cupom está ativo e atinge o valor mínimo
    Então um desconto de "10%" deve ser aplicado sobre o valor total dos produtos
    E o resumo financeiro do checkout deve atualizar o valor total a pagar

  # ============================================================================
  # UC09: Comprar como Visitante (Guest) (<<extend>> UC07)
  # ============================================================================
  Cenário: Realizar checkout rápido sem necessidade de criar conta
    Dado que eu sou um "Visitante" com itens no carrinho e opto por "Comprar como Visitante"
    Quando eu forneço meu e-mail "visitante@email.com", CPF "123.456.789-00" e endereço de entrega
    E eu concluo o pagamento via "Cartão de Crédito"
    Então o pedido deve ser registrado com os dados do comprador visitante
    E o comprovante e número de rastreio devem ser enviados para "visitante@email.com"

  # ============================================================================
  # Fluxo de Exceção: Falha no Processamento do Pagamento (UC12)
  # ============================================================================
  Cenário: Tentativa de checkout com pagamento recusado pelo Gateway
    Dado que eu estou no checkout realizando o pagamento com "Cartão de Crédito"
    Quando o Gateway "System" recusa a transação por "Saldo Insuficiente"
    Então o sistema deve exibir uma mensagem clara informando a recusa
    E o status do pedido não deve ser finalizado
    E o meu carrinho de compras deve permanecer intacto para seleção de um novo meio de pagamento

  # ============================================================================
  # UC10: Acompanhar Pedidos (Exclusivo Cliente Logado)
  # ============================================================================
  Cenário: Visualização do histórico e rastreamento de pedidos efetuados
    Dado que eu estou autenticado como "Cliente Logado"
    Quando eu aceso a seção "Meus Pedidos"
    Então o sistema deve listar todos os meus pedidos anteriores e atuais
    E ao selecionar um pedido em trânsito, o status detalhado e o código de rastreamento last-mile devem ser exibidos

  # ============================================================================
  # UC11: Solicitar Devolução de Produto (Exclusivo Cliente Logado)
  # ============================================================================
  Cenário: Solicitação de logística reversa para produto entregue
    Dado que eu sou um "Cliente Logado" e possuo um pedido entregue há menos de "7" dias
    Quando eu seleciono o item "Torneira Monocomando" e solicito a devolução com motivo "Arrependimento"
    Então o sistema deve registrar a solicitação de devolução
    E deve gerar o código de autorização de postagem de logística reversa para o envio
