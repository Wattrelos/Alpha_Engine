# language: pt

@api @catalogo @busca @autocomplete @produtos @json
Funcionalidade: API de Catálogo, Busca Preditiva e Consulta de Estoque
  Como o frontend dinâmico e aplicativos móveis da Alpha Engine
  Eu quero consultar sugestões de busca em tempo real e checar estoque por variação
  Para oferecer uma experiência de compra instantânea e precisa aos usuários

  Contexto:
    Dado que a API da plataforma Alpha Engine está operacional
    E o catálogo possui produtos indexados para busca rápida

  @busca @autocomplete
  Cenário: Autocomplete em tempo real na barra de busca com retorno JSON estruturado
    Quando o frontend consome a API "GET" em "/api/busca/autocomplete?q=porce"
    Então o status da resposta HTTP deve ser "200 OK"
    E a resposta JSON deve retornar uma lista de até 5 sugestões de produtos
    E cada item sugerido deve conter os atributos "id", "nome", "slug", "preco" e "thumbnail"

  @catalogo @estoque_variacao
  Cenário: Consulta em tempo real da disponibilidade de estoque por variação de SKU
    Quando o cliente consome a API "GET" em "/api/produtos/101/variantes/SKU-220V-PRETO/estoque"
    Então o status da resposta HTTP deve ser "200 OK"
    E o corpo JSON deve informar "disponivel" igual a verdadeiro
    E deve retornar a quantidade em estoque "quantidade" maior que zero
