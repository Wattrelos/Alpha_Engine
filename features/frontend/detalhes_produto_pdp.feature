# language: pt

@frontend @pdp @produto @RF001 @RF002 @RF008 @RF012 @RN001 @RN003
Funcionalidade: Página de Detalhes do Produto (PDP)
  Como um cliente interessado em um produto
  Eu quero visualizar imagens em alta definição, especificações técnicas detalhadas e selecionar variações
  Para ter total segurança na escolha do material antes de adicionar ao carrinho

  Contexto:
    Dado que a loja virtual Alpha Engine está operacional
    E o catálogo possui o produto "Lustre Pendente Moderno" devidamente cadastrado

  @galeria @zoom @RF001
  Cenário: Visualização de galeria de fotos em alta definição com efeito zoom
    Quando o usuário acessa a página do produto "Lustre Pendente Moderno"
    Então a Página de Detalhes do Produto (PDP) deve ser exibida
    E deve carregar a imagem principal em alta resolução com recurso de zoom ao passar o mouse
    E deve exibir a galeria de miniaturas de fotos em diferentes ângulos

  @especificacoes @RN002 @RN003
  Cenário: Exibição completa de especificações técnicas obrigatórias
    Quando o usuário navega até a aba "Especificações Técnicas" da PDP
    Então o sistema deve exibir as dimensões do produto (altura, largura e profundidade)
    E deve exibir o peso bruto para cálculo de frete
    E deve discriminar a voltagem, tipo de soquete e consumo energético

  @seletor_variantes @dinamico
  Cenário: Mudança visual e atualização de estoque ao selecionar variante
    Dado que o produto possui as variantes "110V" e "220V"
    Quando o usuário clica na opção de voltagem "220V"
    Então o botão de seleção deve ficar destacado visualmente como ativo
    E o indicador de estoque em tempo real deve atualizar para "Disponível para pronta entrega (8 unidades)"
    E o botão "Adicionar ao Carrinho" deve ser habilitado

  @cross_selling @RF008
  Cenário: Exibição de produtos recomendados e correlatos (Cross-selling)
    Quando o usuário está visualizando a PDP do "Porcelanato Acetinado 80x80"
    Então o sistema deve exibir a seção "Frequentemente Comprados Juntos"
    E deve sugerir itens correlatos como "Argamassa ACIII 20kg", "Espaçador Nivelador" e "Rejunte Flexível"
    E deve permitir adicionar o combo completo com um único clique
