# language: pt

@security @csrf @checkout @formularios
Funcionalidade: Prevenção de Forjamento de Requisição entre Sites (CSRF)
  Como o módulo de proteção contra CSRF da aplicação Alpha Engine
  Eu quero gerar e exigir tokens criptográficos descartáveis em todas as submissões POST/PUT/DELETE
  Para evitar que sites maliciosos forjem compras ou alterações de dados em nome do usuário

  Contexto:
    Dado que a middleware de proteção CSRF está ativa no checkout
    E a sessão de usuário está inicializada

  @csrf @geracao
  Cenário: Geração automática de pares de tokens CSRF na renderização do checkout
    Quando o cliente envia uma requisição "GET" para "/pt-br/checkout"
    Então o sistema deve responder com os atributos de token CSRF no formato JSON
    E o campo "nameKey" e "valueKey" não devem estar vazios

  @csrf @sucesso
  Cenário: Submissão de pedido autorizada com token CSRF válido
    Dado que o cliente obteve tokens CSRF válidos para a sessão
    Quando o cliente envia uma requisição "POST" para "/pt-br/checkout" com payload JSON contendo o token CSRF e "payment_firstname" igual a "João"
    Então o status da resposta deve ser "200"
    E a resposta JSON deve conter "success" igual a verdadeiro
    E o nome do comprador na resposta deve ser "João"

  @csrf @rejeicao
  Cenário: Rejeição imediata de submissão com token CSRF inválido ou falsificado
    Dado que o cliente possui um token CSRF inválido "token_falso_123"
    Quando o cliente tenta enviar uma requisição "POST" para "/pt-br/checkout" com o token CSRF inválido
    Então a requisição deve ser rejeitada com código HTTP de erro ou exceção CSRF
