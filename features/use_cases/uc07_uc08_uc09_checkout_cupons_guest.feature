# language: pt

@use_cases @uc07 @uc08 @uc09 @checkout @cupons @guest
Funcionalidade: Casos de Uso UC07, UC08 e UC09 - Checkout, Cupons e Compra Expressa Guest
  Como um comprador na loja virtual Alpha Engine
  Eu quero concluir meu pedido informando entrega, aplicando cupons ou comprando sem criar conta
  Para ter flexibilidade e rapidez na finalização da minha compra

  Contexto:
    Dado que o sistema "Alpha Engine - Loja Virtual" está ativo e operacional

  @uc08 @extend @cupom
  Cenário: Aplicar cupom de desconto promocional válido no checkout (UC08)
    Dado que eu estou na etapa de resumo do checkout
    Quando eu insiro o código promocional "PRIMEIRACOMPRA10" no campo de cupom
    E o sistema valida que o cupom está ativo e atinge o valor mínimo
    Então um desconto de "10%" deve ser aplicado sobre o valor total dos produtos
    E o resumo financeiro do checkout deve atualizar o valor total a pagar

  @uc09 @extend @guest_checkout
  Cenário: Realizar compra expressa como visitante sem necessidade de senha (UC09)
    Dado que eu sou um "Visitante" com itens no carrinho e opto por "Comprar como Visitante"
    Quando eu forneço meu e-mail "visitante@email.com", CPF "123.456.789-00" e endereço de entrega
    E eu concluo o pagamento via "Cartão de Crédito"
    Então o pedido deve ser registrado com os dados do comprador visitante
    E o comprovante e número de rastreio devem ser enviados para "visitante@email.com"
