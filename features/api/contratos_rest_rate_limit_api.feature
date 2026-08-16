# language: pt

@api @rest @rate_limit @rfc7807 @contratos
Funcionalidade: Contratos RESTful, Padronização de Respostas e Rate Limiting da API
  Como a arquitetura de APIs da Alpha Engine
  Eu quero padronizar o formato de respostas de erro (RFC 7807) e proteger endpoints via Rate Limit (60 req/min)
  Para garantir estabilidade, segurança e previsibilidade a todos os integradores

  Contexto:
    Dado que a API da plataforma Alpha Engine está operacional
    E o middleware de Rate Limit da API está configurado com limite de 60 requisições por minuto

  @api @rate_limit @bloqueio
  Cenário: Aplicação do limite de taxa de 60 requisições por minuto na API
    Quando um cliente realiza 61 requisições seguidas para "/api/carrinho/dados" em menos de 60 segundos
    Então a 61ª requisição deve responder com o status "429 Too Many Requests"
    E a resposta deve conter o cabeçalho "Retry-After"
    E o corpo da resposta deve conter o código de erro "RATE_LIMIT_EXCEEDED"

  @api @rfc7807 @erro_padronizado
  Cenário: Retorno de erro no padrão RFC 7807 para payload JSON malformado
    Quando o cliente consome a API "POST" em "/api/carrinho/dados" com corpo JSON inválido ou corrompido
    Então o status da resposta HTTP deve ser "400 Bad Request" ou "422 Unprocessable Entity"
    E a resposta deve conter os campos estruturados "type", "title", "status" e "detail"
