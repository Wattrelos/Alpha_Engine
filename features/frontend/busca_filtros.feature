# language: pt

@frontend @busca @filtros @RF011
Funcionalidade: Busca e Filtros Facetados de Produtos
  Como um comprador na loja virtual
  Eu quero pesquisar por palavras-chave e aplicar múltiplos filtros simultâneos
  Para encontrar rapidamente o produto exato de acordo com especificações técnicas e orçamento

  Contexto:
    Dado que a loja virtual Alpha Engine está operacional
    E o índice de busca com indexação em tempo real está ativo

  @busca_simples @sugestao
  Cenário: Busca por palavra-chave com sugestão preditiva (autocomplete)
    Quando o usuário digita "Torneira" no campo de busca do cabeçalho
    Então o sistema deve abrir um menu suspenso de sugestões rápidas
    E deve sugerir termos correlatos como "Torneira Monocomando", "Torneira Gourmet" e "Torneira para Lavatório"
    E deve exibir prévias de produtos com thumbnail e preço em tempo real

  @filtros_combinados
  Cenário: Aplicação de múltiplos filtros facetados na listagem de produtos
    Dado que o usuário está na página de resultados da busca por "Piso"
    Quando o usuário aplica o filtro de categoria "Pisos e Revestimentos"
    E seleciona a faixa de preço de "R$ 50,00" até "R$ 150,00"
    E marca a opção de acabamento "Acetinado"
    Então a grade de produtos deve ser atualizada exibindo apenas itens correspondentes
    E o contador de resultados deve indicar a quantidade exata de produtos filtrados
    E os chips dos filtros ativos devem permitir remoção individual com um clique

  @busca_sem_resultados
  Cenário: Pesquisa por termo inexistente com sugestão de produtos populares
    Quando o usuário realiza uma busca pelo termo "ProdutoInexistente12345"
    Então o sistema deve exibir a mensagem amigável "Nenhum produto encontrado para o termo pesquisado"
    E deve exibir sugestões de termos populares e a vitrine de "Produtos em Destaque"
