# language: pt

@api @webhook @hmac @seguranca @gateways
Funcionalidade: Webhooks com Autenticação e Validação de Assinatura HMAC SHA-256
  Como o módulo de recebimento de notificações da Alpha Engine
  Eu quero validar a assinatura criptográfica HMAC em todas as notificações de gateway
  Para impedir que atacantes forjem confirmações de pagamento ou alterem status de pedidos maliciosamente

  Contexto:
    Dado que a API da plataforma Alpha Engine está operacional
    E o SignatureMiddleware está configurado com a chave secreta "whsec_test_secret_key_123"

  @webhook @assinatura_valida
  Cenário: Processamento de webhook de pagamento com assinatura HMAC SHA-256 válida
    Dado que o gateway "stripe" gera o payload de notificação com evento "payment_intent.succeeded"
    Quando o gateway envia um "POST" para "/api/webhook/stripe" com o cabeçalho "X-Signature" contendo o HMAC válido do payload
    Então o SignatureMiddleware deve autenticar a requisição com sucesso
    E o status da resposta HTTP deve ser "200 OK"
    E o payload JSON do webhook deve conter "status" igual a "webhook_processed"

  @webhook @assinatura_invalida
  Cenário: Rejeição imediata de webhook com assinatura HMAC inválida ou forjada
    Dado que uma requisição maliciosa tenta enviar um "POST" para "/api/webhook/stripe"
    Quando o cabeçalho "X-Signature" contém um hash inválido "hash_adulterado_xyz"
    Então o SignatureMiddleware deve interceptar a requisição
    E deve rejeitar imediatamente com o status "401 Unauthorized" ou "403 Forbidden"
    E nenhuma alteração de status de pedido deve ser realizada

  @webhook @assinatura_ausente
  Cenário: Bloqueio de webhook sem cabeçalho de assinatura
    Quando uma requisição chega em "/api/webhook/stripe" sem o cabeçalho de assinatura
    Então o sistema deve rejeitar o acesso com o status "400 Bad Request" ou "401 Unauthorized"
