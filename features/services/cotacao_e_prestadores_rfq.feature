# language: pt

@services @quotation @rfq @boq @mto
Funcionalidade: Cotação de Projetos, Prestadores de Serviço e Levantamento de Materiais
  Como um cliente da Alpha Engine
  Eu quero publicar pedidos de orçamento de obras (RFQ), receber propostas de prestadores no meu raio geográfico e converter a lista de materiais (BoQ) em compras no e-commerce
  Para que eu possa contratar profissionais qualificados e adquirir todos os insumos necessários com agilidade e descontos

  Contexto:
    Dado que a plataforma Alpha Engine e o módulo de serviços e cotação estão operacionais
    E o catálogo de materiais de construção e repositório de prestadores estão ativos

  @RF033 @smoke
  Cenário: Cliente publica solicitação de orçamento de projeto (RFQ) com sucesso
    Dado que o cliente autenticado preenche os dados do projeto:
      | Campo                  | Valor                                                     |
      | Título                 | Reforma de Banheiro Social                                |
      | Categoria              | revestimento                                              |
      | Descrição              | Troca de revestimento cerâmico e instalação de louças     |
      | CEP                    | 01310-100                                                 |
      | Cidade                 | São Paulo                                                 |
      | Estado                 | SP                                                        |
      | Expectativa Orçamento  | R$ 4.500,00                                               |
      | Prazo Desejado         | 20 dias                                                   |
    Quando o cliente submete o formulário de novo projeto
    Então o projeto deve ser registrado com status "open"
    E as coordenadas geográficas devem ser resolvidas para o CEP informado

  @RF034 @geofencing
  Cenário: Matching e disponibilização de oportunidade apenas para prestadores dentro do raio
    Dado que existe o projeto "Reforma de Banheiro Social" localizado em "São Paulo - SP"
    E o prestador "João Reformas" atende no raio de "30" km com base em "São Paulo - SP"
    E o prestador "Campinas Obras" atende no raio de "15" km com base em "Campinas - SP"
    Quando o feed de oportunidades é consultado
    Então o prestador "João Reformas" deve visualizar a oportunidade com distância calculada
    Mas o prestador "Campinas Obras" não deve receber a oportunidade por estar fora do raio

  @RF035 @bid_comparison
  Cenário: Prestador envia proposta e cliente compara no painel de propostas (Bid Comparison)
    Dado que o prestador credenciado envia a seguinte proposta para o projeto:
      | Preço Mão de Obra | Prazo Estimado | Observações                           |
      | R$ 2.800,00       | 15 dias        | Equipe de 2 profissionais com garantia|
    Quando o cliente acessa o painel de comparação de propostas
    Então o orçamento do prestador deve ser exibido com valor, prazo e avaliação do profissional
    E o cliente pode aceitar a proposta para atribuir a obra ao prestador

  @RF036 @takeoff_tool @boq
  Cenário: Prestador contratado realiza o levantamento técnico de materiais (Material Takeoff / BoQ)
    Dado que o prestador foi contratado para a obra "Reforma de Banheiro Social"
    Quando o prestador adiciona os seguintes insumos na ferramenta de Takeoff:
      | Material                   | Unidade | Quantidade | Preço Unit. | Catálogo |
      | Argamassa AC-III 20kg      | saco    | 6          | R$ 32,90    | Sim      |
      | Porcelanato Esmaltado 60x60| cx      | 14         | R$ 89,90    | Sim      |
      | Rejunte Epóxi 1kg          | un      | 3          | R$ 45,00    | Sim      |
    Então o Bill of Quantities (BoQ) deve ser consolidado com o valor total de "R$ 1.590,60"
    E a lista deve ser disponibilizada para aprovação do cliente

  @RF037 @add_to_quote @carrinho @RN015
  Cenário: Conversão da lista de materiais (BoQ) em itens no carrinho de compras com precificação
    Dado que o cliente visualiza a lista técnica de materiais gerada pelo prestador
    Quando o cliente clica em "Adicionar Lista para Cotação & Carrinho" (Add to Quote)
    Então todos os itens vinculados ao catálogo devem ser inseridos no carrinho de compras
    E as regras de desconto progressivo por volume devem ser aplicadas automaticamente aos itens
