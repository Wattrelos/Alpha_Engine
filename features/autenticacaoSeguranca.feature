# language: pt

Funcionalidade: Autenticação, Controle de Acesso (RBAC) e Segurança do Sistema
  Como o mecanismo de segurança da plataforma Alpha Engine
  Eu quero proteger a aplicação contra ataques de força bruta, acessos não autorizados e vulnerabilidades HTTP
  Para manter a integridade das contas de usuários e dos painéis administrativos

  Contexto:
    Dado que a aplicação Alpha Engine e os middlewares de segurança estão ativos

  # ============================================================================
  # Proteção de Cabeçalhos de Segurança HTTP
  # ============================================================================
  Cenário: Aplicação de cabeçalhos de segurança HTTP em todas as respostas
    Quando uma requisição HTTP "GET" é realizada para a rota "/"
    Então os cabeçalhos de segurança "X-Frame-Options", "X-Content-Type-Options" e "Content-Security-Policy" devem estar presentes na resposta HTTP

  # ============================================================================
  # Proteção Contra Ataques de Força Bruta (Rate Limiting)
  # ============================================================================
  Cenário: Bloqueio de IP após tentativas excessivas de login malsucedidas
    Dado que o limite máximo de tentativas de login inválidas é de 3 tentativas
    Quando o IP "192.168.1.100" realiza 4 tentativas de login seguidas com credenciais incorretas
    Então o sistema deve bloquear o IP "192.168.1.100"
    E a 4ª tentativa de login deve responder com o status "429 Too Many Requests"

  # ============================================================================
  # Middleware de Sessão Administrativa e Controle RBAC
  # ============================================================================
  Cenário: Redirecionamento de usuário não autenticado ao tentar acessar área administrativa
    Dado que o usuário não possui uma sessão administrativa ativa
    Quando o usuário tenta acessar a URL "/admin/dashboard"
    Então o sistema deve interromper o acesso e redirecionar o usuário para "/admin/login"

  Cenário: Restrição de acesso por privilégios de papel (RBAC)
    Dado que o usuário está autenticado com o papel "OPERADOR_ESTOQUE"
    Quando o usuário tenta acessar a rota restrita "/admin/configuracoes-financeiras"
    Então o sistema deve rejeitar o acesso com o status "403 Forbidden"
