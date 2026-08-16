# language: pt

@use_cases @uc03 @uc04 @carrinho @variantes
Funcionalidade: Casos de Uso UC03 e UC04 - Adição ao Carrinho com Seleção de Variantes
  Como um comprador na loja Alpha Engine
  Eu quero selecionar as especificações do produto (tamanho, cor, voltagem) e adicionar ao carrinho
  Para preparar meu pedido garantindo a reserva do item em estoque

  Contexto:
    Dado que o sistema "Alpha Engine - Loja Virtual" está ativo e operacional

  @uc03 @uc04 @include
  Cenário: Adicionar produto ao carrinho com seleção obrigatória de variante
    Dado que eu sou um "Visitante" na página de detalhes de um produto com variações
    Quando eu seleciono a variante de voltagem "220V" e a cor "Preto Matte"
    E eu clico no botão "Adicionar ao Carrinho"
    Então o sistema deve validar a disponibilidade de estoque em tempo real
    E o item com a variante "220V / Preto Matte" deve ser adicionado ao carrinho da sessão
    E o subtotal do carrinho deve ser atualizado com sucesso
