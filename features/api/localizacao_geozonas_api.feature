# language: pt

@api @geo @localizacao @paises @estados @cidades
Funcionalidade: API de Localização, Países, Estados (Zonas) e Cidades
  Como um cliente da API da loja Alpha Engine
  Eu quero consultar a divisão territorial em cascata (País -> Estados -> Cidades)
  Para autopreencher endereços de entrega e calcular tributações regionais com precisão

  Contexto:
    Dado que a API da plataforma Alpha Engine está operacional
    E o banco de dados geográfico possui países, zonas e cidades carregados

  @geo @estados
  Cenário: Listagem de estados para o Brasil (country_id = 30) via GET /api/geo/paises/30/estados
    Quando o cliente consome a API "GET" em "/api/geo/paises/30/estados"
    Então o status da resposta HTTP deve ser "200 OK"
    E a resposta JSON deve ser uma lista contendo 27 unidades federativas
    E o estado com sigla "SP" e nome "São Paulo" deve estar presente na lista

  @geo @cidades
  Cenário: Listagem de cidades para o estado de São Paulo via GET /api/geo/estados/464/cidades
    Quando o cliente consome a API "GET" em "/api/geo/estados/464/cidades"
    Então o status da resposta HTTP deve ser "200 OK"
    E a resposta JSON deve conter municípios correspondentes ao estado
    E o município "São Paulo" com código de zona deve estar presente

  @geo @pais_invalido
  Cenário: Consulta de estados para país inexistente
    Quando o cliente consome a API "GET" em "/api/geo/paises/99999/estados"
    Então a resposta JSON deve ser uma lista vazia "[]" ou retornar HTTP "404 Not Found"
