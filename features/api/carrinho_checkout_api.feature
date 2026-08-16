# language: pt

@api @carrinho @checkout @json
Funcionalidade: API de Carrinho de Compras e Cálculo Assíncrono
  Como a camada de serviços RESTful da plataforma Alpha Engine
  Eu quero fornecer endpoints JSON para cálculo de subtotal, frete por CEP e sincronização de itens
  Para alimentar os componentes dinâmicos do Frontend e SPAs com respostas em tempo real

  Contexto:
    Dado que a API da plataforma Alpha Engine está operacional
    E o cabeçalho "Accept" da requisição está configurado como "application/json"

  @carrinho @dados_totais
  Cenário: Cálculo assíncrono dos totais e frete do carrinho via POST /api/carrinho/dados
    Quando o cliente consome a API "POST" em "/api/carrinho/dados" com payload JSON:
      """
      {
        "items": [
          {"product_id": 101, "quantity": 2, "price": 49.90}
        ],
        "shipping_cep": "01310-100"
      }
      """
    Então o status da resposta HTTP deve ser "200 OK"
    E a resposta deve conter o cabeçalho "Content-Type" com "application/json"
    E o corpo JSON de resposta deve conter o campo "subtotal" igual a "99.80"
    E deve conter a lista de modalidades de "shipping_options" com opções calculadas

  @carrinho @salvar_cep
  Cenário: Persistência de CEP na sessão via POST /api/carrinho/salvar-cep
    Quando o cliente consome a API "POST" em "/api/carrinho/salvar-cep" com payload JSON:
      """
      {
        "cep": "05425-070"
      }
      """
    Então o status da resposta HTTP deve ser "200 OK"
    E o campo "status" da resposta JSON deve ser "success"
    E o CEP "05425-070" deve ser armazenado na sessão do usuário

  @carrinho @sincronizacao
  Cenário: Sincronização de carrinho anônimo com a conta do cliente via POST /api/carrinho/sincronizar
    Dado que o cliente informa o payload de sincronização com "3" itens do carrinho local
    Quando o cliente consome a API "POST" em "/api/carrinho/sincronizar"
    Então o status da resposta HTTP deve ser "200 OK"
    E a resposta JSON deve conter o total consolidado de itens após a mesclagem
