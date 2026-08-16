# language: pt

@use_cases @uc01 @uc02 @catalogo @busca
Funcionalidade: Casos de Uso UC01 e UC02 - Navegação no Catálogo e Busca de Produtos
  Como um visitante ou cliente da plataforma Alpha Engine
  Eu quero navegar pelas categorias da loja e realizar buscas com filtros
  Para encontrar rapidamente os produtos que desejo adquirir

  Contexto:
    Dado que o sistema "Alpha Engine - Loja Virtual" está ativo e operacional

  @uc01 @uc02 @visitante
  Cenário: Navegação e busca no catálogo por um visitante anônimo
    Dado que eu sou um "Visitante" navegando na loja virtual
    Quando eu busco pelo termo "Piso Porcelanato"
    E eu aplico o filtro de categoria "Pisos e Revestimentos" com faixa de preço de "50" a "150"
    Então o sistema deve exibir a listagem de produtos correspondentes
    E ao selecionar um item, a Página de Detalhes do Produto (PDP) deve ser exibida
