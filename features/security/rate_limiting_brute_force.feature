# language: pt

@security @rate_limit @brute_force
Funcionalidade: Proteção Contra Ataques de Força Bruta (Rate Limiting)
  Como o sistema de defesa da plataforma Alpha Engine
  Eu quero limitar a taxa de requisições de login por endereço IP
  Para impedir ataques automatizados de força bruta e sequestro de credenciais

  Contexto:
    Dado que a aplicação Alpha Engine e os middlewares de segurança estão ativos
    E que o limite máximo de tentativas de login inválidas é de 3 tentativas

  @rate_limit @login_bloqueio
  Cenário: Bloqueio temporário de IP após 3 tentativas consecutivas de senha incorreta
    Quando o IP "192.168.1.100" realiza 4 tentativas de login seguidas com credenciais incorretas
    Então o sistema deve bloquear o IP "192.168.1.100"
    E a 4ª tentativa de login deve responder com o status "429 Too Many Requests"

  @rate_limit @recuperacao_senha
  Cenário: Limitação de taxa em requisições de recuperação de senha
    Quando o IP "203.0.113.45" envia "10" solicitações consecutivas para "/recuperar-senha" em menos de 1 minuto
    Então o middleware de Rate Limit deve intervir a partir da "5ª" requisição
    E deve retornar o cabeçalho "Retry-After" indicando o tempo de espera em segundos
