# language: pt

@checkout @fluxo_cliente @UC07 @UC08 @RF017 @RF018 @RN018
Funcionalidade: Fluxo de Checkout para Cliente Autenticado
  Como um cliente autenticado na loja virtual Alpha Engine
  Eu quero avançar do carrinho para a tela de finalização de compra
  Para que eu possa selecionar endereços cadastrados, aplicar cupons e revisar os valores com segurança

  Contexto:
    Dado que a loja virtual Alpha Engine está operacional
    E o cliente "carlos.silva@email.com" está devidamente autenticado
    E possui itens ativos no carrinho totalizando "R$ 450,00"
    E a proteção contra ataques CSRF está habilitada no checkout

  @enderecos @RF017
  Cenário: Carregamento dos múltiplos endereços de entrega cadastrados
    Quando o cliente acessa a página de checkout "/pt-br/checkout"
    Então o sistema deve exibir a lista de endereços salvos da conta
    E deve permitir selecionar o endereço principal "Av. Paulista, 1000 - Bela Vista, São Paulo - SP"
    E deve carregar os métodos de frete disponíveis para a região do endereço escolhido

  @csrf @seguranca
  Cenário: Proteção e geração automática de tokens CSRF na renderização do checkout
    Quando o cliente envia uma requisição GET para "/pt-br/checkout"
    Então o sistema deve responder com os atributos de token CSRF no formato JSON
    E o campo "csrf_name" e "csrf_value" não devem estar vazios
    E o formulário de submissão do checkout deve incluir os tokens para validação de segurança

  @cupom @sucesso @UC08
  Cenário: Aplicação de cupom de desconto promocional válido no resumo do checkout
    Dado que existe o cupom promocional "ALPHA10" ativo oferecendo 10% de desconto para compras acima de R$ 100,00
    Quando o cliente insere o cupom "ALPHA10" no campo de cupom e confirma
    Então o sistema deve validar o cupom com sucesso
    E deve abater o desconto de "R$ 45,00" no cálculo total
    E a discriminação do desconto "Cupom ALPHA10 (-10%)" deve constar no resumo financeiro

  @cupom @invalido
  Cenário: Tentativa de aplicação de cupom inválido ou expirado
    Dado que o cupom promocional "CUPOMEXPIRADO" está com a validade encerrada
    Quando o cliente insere o cupom "CUPOMEXPIRADO" no checkout
    Então o sistema deve rejeitar o cupom
    E deve apresentar o alerta de erro "O cupom informado é inválido ou expirou a data de validade"
    E o valor total do pedido deve permanecer inalterado em "R$ 450,00"

  @resumo_financeiro
  Cenário: Exibição completa e transparente da discriminação financeira do pedido
    Dado que o carrinho possui:
      | Descrição           | Valor     |
      | Subtotal Produtos   | R$ 450,00 |
      | Frete Transportadora| R$ 35,00  |
      | Desconto Cupom      | -R$ 45,00 |
    Quando o cliente revisa o fechamento da compra
    Então o resumo financeiro deve totalizar o valor final a pagar de "R$ 440,00"
    E todas as linhas de totais (Subtotal, Frete, Desconto e Total) devem ser renderizadas claramente
