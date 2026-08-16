# language: pt

@frontend @catalogo @navegacao @RF003 @RF011
Funcionalidade: Navegação no Catálogo e Categorias da Loja Virtual
  Como um visitante ou cliente da Alpha Engine
  Eu quero navegar pelas categorias, menus e vitrines da loja virtual
  Para que eu encontre facilmente os materiais de construção e ferramentas que desejo comprar

  Contexto:
    Dado que a loja virtual Alpha Engine está operacional
    E a árvore de categorias do catálogo está carregada

  @home @vitrine
  Cenário: Visualização dos banners e carrossel de produtos em destaque na Home
    Quando o visitante acessa a página inicial "/"
    Então o sistema deve renderizar o layout principal com cabeçalho, menu e rodapé
    E deve exibir o carrossel de banners promocionais da "Semana do Piso e Revestimento"
    E deve listar as vitrines de "Mais Vendidos" e "Lançamentos" com cards de produtos completos

  @categorias @menu
  Cenário: Navegação por categoria e subcategoria através do menu principal
    Quando o visitante seleciona a categoria "Pisos e Revestimentos" no menu
    E escolhe a subcategoria "Porcelanatos"
    Então o sistema deve exibir a listagem de produtos da categoria "Porcelanatos"
    E deve exibir a trilha de navegação (breadcrumbs) "Início > Pisos e Revestimentos > Porcelanatos"
    E deve apresentar o seletor de ordenação por "Menor Preço", "Maior Preço" e "Mais Populares"

  @paginacao
  Cenário: Paginação de produtos em categorias com grande volume de itens
    Dado que a categoria "Ferramentas Elétricas" possui "48" produtos cadastrados
    Quando o visitante navega pela listagem de produtos com limite de "12" itens por página
    Então a página atual deve exibir os primeiros "12" produtos
    E a barra de paginação deve disponibilizar a navegação para "4" páginas
    E ao clicar na página "2", os próximos "12" produtos devem ser carregados
