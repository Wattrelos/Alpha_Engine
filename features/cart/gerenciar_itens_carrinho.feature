# language: pt

@cart @gerenciar_itens @RF009
Funcionalidade: Gerenciamento e Modificação de Itens no Carrinho
  Como um cliente da loja virtual
  Eu quero alterar quantidades, remover itens e limpar meu carrinho
  Para que eu tenha total controle sobre os produtos e valores antes de finalizar a compra

  Contexto:
    Dado que a loja virtual Alpha Engine está operacional
    E o cliente possui os seguintes itens no carrinho de compras:
      | Produto                  | Preço Unitário | Quantidade | Total Parcial |
      | Cimento Portland CP II   | R$ 32,00       | 5          | R$ 160,00     |
      | Argamassa ACIII 20kg     | R$ 28,00       | 3          | R$ 84,00      |
    E o subtotal inicial do carrinho é de "R$ 244,00"

  @edicao @incremento
  Cenário: Incrementar a quantidade de um item existente no carrinho
    Quando eu aumento a quantidade do item "Cimento Portland CP II" de "5" para "8" unidades
    Então o sistema deve validar a disponibilidade de estoque para a nova quantidade
    E a quantidade do produto "Cimento Portland CP II" no carrinho deve ser atualizada para "8"
    E o subtotal do carrinho deve ser recalculado automaticamente para "R$ 340,00"

  @edicao @decremento
  Cenário: Decrementar a quantidade de um item no carrinho
    Quando eu reduzo a quantidade do item "Argamassa ACIII 20kg" de "3" para "1" unidade
    Então a quantidade do produto "Argamassa ACIII 20kg" no carrinho deve ser atualizada para "1"
    E o subtotal do carrinho deve ser recalculado automaticamente para "R$ 188,00"

  @remocao
  Cenário: Remover um item específico do carrinho
    Quando eu removo o item "Argamassa ACIII 20kg" do carrinho
    Então o produto "Argamassa ACIII 20kg" não deve mais constar no carrinho
    E o carrinho deve conter apenas "1" produto distinto
    E o subtotal do carrinho deve ser recalculado para "R$ 160,00"

  @remocao @quantidade_zero
  Cenário: Reduzir a quantidade de um item para zero deve removê-lo
    Quando eu altero a quantidade do item "Cimento Portland CP II" para "0" unidades
    Então o sistema deve interpretar como exclusão e remover o item do carrinho
    E o carrinho deve conter apenas "1" produto restante
    E o subtotal deve refletir apenas o valor de "Argamassa ACIII 20kg" correspondente a "R$ 84,00"

  @limpar_carrinho
  Cenário: Limpar todos os itens do carrinho de compras
    Quando eu clico na ação de "Esvaziar Carrinho"
    Então todos os itens devem ser removidos da sessão
    E o carrinho deve estar vazio exibindo a mensagem "Seu carrinho de compras está vazio"
    E o valor total e o subtotal devem ser zerados para "R$ 0,00"
