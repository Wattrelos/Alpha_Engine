# language: pt

@cart @frete @logistica @RN002 @RN007 @RN008 @RF010 @RF021
Funcionalidade: Cálculo e Simulação de Frete no Carrinho de Compras
  Como um cliente da loja virtual
  Eu quero simular as opções de frete e prazos informando meu CEP
  Para que eu possa escolher a melhor forma de entrega (Correios, Transportadora ou Retirada na Loja) antes de fechar a compra

  Contexto:
    Dado que a loja virtual Alpha Engine está operacional
    E o módulo de cálculo de frete dinâmico está integrado

  @simulacao @correios @itens_leves
  Cenário: Simulação de frete para produtos leves via Correios
    Dado que o carrinho contém os seguintes produtos leves:
      | Produto                  | Peso (kg) | Dimensões (cm) | Quantidade |
      | Fita Veda Rosca 18mm     | 0.05      | 5x5x2          | 3          |
      | Conjunto de Chaves Torx  | 0.40      | 20x10x3        | 1          |
    Quando o cliente simula o frete para o CEP "01310-100" (São Paulo - SP)
    Então o sistema deve consultar a API de logística baseada em peso cubado
    E deve disponibilizar as opções:
      | Modalidade | Prazo Estimado | Valor     |
      | SEDEX      | 1 dia útil     | R$ 22,50  |
      | PAC        | 4 dias úteis   | R$ 14,80  |

  @simulacao @transportadora @materiais_pesados @RN007
  Cenário: Seleção automática de Transportadora para materiais pesados de construção
    Dado que o carrinho contém materiais pesados:
      | Produto              | Peso (kg) | Quantidade | Peso Total |
      | Bloco de Concreto 14 | 12.00     | 50         | 600.00 kg  |
      | Areia Ensacada 20kg  | 20.00     | 10         | 200.00 kg  |
    Quando o cliente simula o frete para o CEP "04571-000"
    Então o sistema deve identificar peso superior a 30kg
    E deve restringir o cálculo exclusivamente para a modalidade "Transportadora Carga Pesada"
    E a cotação calculada de frete deve ser exibida como "R$ 180,00"

  @frete_gratis @RN008
  Cenário: Aplicação automática de Frete Grátis ao atingir valor mínimo regional
    Dado que a política regional estabelece frete grátis para compras acima de "R$ 299,00" no estado "SP"
    E o cliente possui "R$ 350,00" em produtos elegíveis no carrinho
    Quando o cliente informa o CEP de entrega "01001-000"
    Então o sistema deve aplicar o benefício "Frete Grátis" com valor de "R$ 0,00"
    E o resumo do carrinho deve destacar o selo "Frete Grátis Aplicado"

  @retirada_loja @bopis @RN008 @RF021
  Cenário: Seleção de Retirada na Loja Física (BOPIS) com custo zero de frete
    Dado que o cliente possui itens no carrinho
    Quando o cliente seleciona a modalidade de entrega "Retirada na Loja Física (Matriz)"
    Então o valor do frete deve ser ajustado para "R$ 0,00"
    E o sistema deve exibir as instruções de retirada e o prazo de disponibilidade de "2 horas após confirmação"

  @validacao_cep
  Cenário: Validação de formato de CEP inválido na simulação
    Dado que o cliente possui itens no carrinho
    Quando o cliente informa um CEP inválido "9999-ABC"
    Então o sistema deve exibir a mensagem de erro "Informe um CEP válido com 8 dígitos numéricos"
    E nenhuma cotação de frete deve ser adicionada ao resumo
