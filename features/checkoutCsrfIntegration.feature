# language: pt

Funcionalidade: Integração e Validação de Proteção CSRF no Checkout
  Como um sistema de e-commerce seguro (Alpha Engine)
  Eu quero validar os tokens CSRF em todas as requisições de checkout
  Para evitar ataques de Forjamento de Requisição entre Sites (CSRF)

  Contexto:
    Dado que a middleware de proteção CSRF está ativa no checkout
    E a sessão de usuário está inicializada

  Cenário: Geração automática de tokens CSRF ao acessar a página de checkout
    Quando o cliente envia uma requisição "GET" para "/pt-br/checkout"
    Então o sistema deve responder com os atributos de token CSRF no formato JSON
    E o campo "nameKey" e "valueKey" não devem estar vazios

  Cenário: Submissão bem-sucedida de checkout com token CSRF válido
    Dado que o cliente obteve tokens CSRF válidos para a sessão
    Quando o cliente envia uma requisição "POST" para "/pt-br/checkout" com payload JSON contendo o token CSRF e "payment_firstname" igual a "João"
    Então o status da resposta deve ser "200"
    E a resposta JSON deve conter "success" igual a verdadeiro
    E o nome do comprador na resposta deve ser "João"

  Cenário: Bloqueio de submissão de checkout com token CSRF ausente ou inválido
    Dado que o cliente possui um token CSRF inválido "token_falso_123"
    Quando o cliente tenta enviar uma requisição "POST" para "/pt-br/checkout" com o token CSRF inválido
    Então a requisição deve ser rejeitada com código HTTP de erro ou exceção CSRF
