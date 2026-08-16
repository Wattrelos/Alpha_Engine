# language: pt

@cart @adicionar_produto
Funcionalidade: Adicionar Produtos ao Carrinho de Compras
  Como um cliente (visitante ou autenticado) da Alpha Engine
  Eu quero selecionar produtos do catálogo e adicioná-los ao carrinho de compras
  Para que eu possa reunir os itens desejados e prosseguir para a finalização do pedido

  Contexto:
    Dado que a loja virtual Alpha Engine está operacional
    E o repositório de produtos e o catálogo estão ativos

  @smoke @regra_negocio
  Cenário: Adicionar produto simples ao carrinho com sucesso
    Dado que o produto "Furadeira de Impacto 750W" possui estoque disponível de 15 unidades
    E o preço unitário do produto é "R$ 249,90"
    Quando eu adiciono "1" unidade do produto "Furadeira de Impacto 750W" ao carrinho
    Então o produto deve estar presente no carrinho de compras
    E a quantidade total de itens no carrinho deve ser "1"
    E o subtotal do carrinho deve ser "R$ 249,90"

  @variantes @RN003
  Cenário: Adicionar produto com variantes obrigatórias selecionadas
    Dado que o produto "Lustre Pendente Moderno" possui as seguintes variações:
      | Atributo | Opções disponíveis     |
      | Voltagem | 110V, 220V, Bivolt     |
      | Cor      | Preto Fosco, Dourado   |
    Quando eu seleciono a voltagem "220V" e a cor "Preto Fosco"
    E eu adiciono "2" unidades ao carrinho
    Então o produto com a variante "220V / Preto Fosco" deve ser adicionado ao carrinho
    E o carrinho deve exibir a especificação exata das opções escolhidas

  @validacao @RN003
  Cenário: Bloqueio ao tentar adicionar produto sem selecionar variante obrigatória
    Dado que o produto "Piso Vinílico Click" exige a seleção de padrão de cor
    Quando eu tento adicionar o produto ao carrinho sem selecionar nenhuma opção de cor
    Então o sistema deve impedir a inclusão no carrinho
    E deve exibir uma mensagem de validação "Por favor, selecione a variação desejada antes de continuar"

  @estoque @RN005 @RF006
  Cenário: Bloqueio ao tentar adicionar quantidade superior ao estoque em tempo real
    Dado que o produto "Porcelanato Acetinado 80x80" possui apenas "3" caixas em estoque
    Quando eu tento adicionar "5" caixas do produto ao carrinho
    Então o sistema deve rejeitar a adição da quantidade excedente
    E deve apresentar o alerta de estoque "A quantidade solicitada excede o saldo disponível em estoque (3 caixas)"
    E o carrinho não deve ser atualizado com itens indisponíveis

  @fracionamento @RN001 @RF004
  Cenário: Adicionar produto vendido por metro quadrado (m²) com cálculo de conversão
    Dado que o produto "Piso Cerâmico Esmaltado" é comercializado por "metro_quadrado"
    E cada caixa do produto cobre exatamente "2.50" m² ao preço de "R$ 45,00" por m²
    Quando eu informo que preciso cobrir uma área de "10.00" m²
    Então o sistema deve converter automaticamente para "4" caixas fechadas
    E o subtotal calculado para o item no carrinho deve ser "R$ 450,00"

  @combo @kit @RN004 @RF007
  Cenário: Adicionar combo promocional (Kit de Produtos) com desconto agrupado
    Dado que existe o kit "Combo Pintura Completa" composto por:
      | Item               | Quantidade |
      | Tinta Acrílica 18L | 1          |
      | Rolo de Lã 23cm    | 2          |
      | Fita Crepe 48mm    | 3          |
    E o valor original somado dos itens é "R$ 420,00" e o preço promocional do combo é "R$ 359,90"
    Quando eu adiciono o "Combo Pintura Completa" ao carrinho
    Então todos os itens componentes do kit devem ser reservados no carrinho
    E o valor total cobrado pelo combo no resumo do carrinho deve ser "R$ 359,90"
