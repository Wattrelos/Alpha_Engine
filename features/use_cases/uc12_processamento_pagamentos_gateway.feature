# language: pt

@use_cases @uc12 @pagamento @gateway @transacoes
Funcionalidade: Caso de Uso UC12 - Processamento de Pagamento via Gateway
  Como o sistema de checkout Alpha Engine
  Eu quero comunicar com o Gateway de Pagamentos para autorizar transações (PIX, Cartão, Boleto)
  Para confirmar os pedidos aprovados ou notificar recusas sem perda do carrinho

  Contexto:
    Dado que o sistema "Alpha Engine - Loja Virtual" está ativo e operacional

  @uc12 @include @sucesso_pix
  Cenário: Realizar checkout com pagamento aprovado pelo Gateway e emissão de NF-e
    Dado que eu sou um "Cliente Logado" com itens em meu carrinho
    Quando eu prossigo para o checkout e informo o endereço de entrega
    E eu seleciono a modalidade de pagamento "PIX"
    E eu confirmo a finalização do pedido
    Então o sistema deve acionar o Gateway "System" para processar o pagamento
    E assim que o Gateway confirmar a transação, o status do pedido deve ser alterado para "Pagamento Aprovado"
    E a nota fiscal (NF-e) deve ser encaminhada para emissão automática

  @uc12 @recusa @cartao @fluxo_excecao
  Cenário: Tratamento de pagamento recusado pela operadora do cartão
    Dado que eu estou no checkout realizando o pagamento com "Cartão de Crédito"
    Quando o Gateway "System" recusa a transação por "Saldo Insuficiente"
    Então o sistema deve exibir uma mensagem clara informando a recusa
    E o status do pedido não deve ser finalizado
    E o meu carrinho de compras deve permanecer intacto para seleção de um novo meio de pagamento
