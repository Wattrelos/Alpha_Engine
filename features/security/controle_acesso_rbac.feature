# language: pt

@security @rbac @autorizacao @admin_session
Funcionalidade: Controle de Acesso Baseado em Papéis (RBAC) e Sessão Administrativa
  Como o motor de autorização do Alpha Engine
  Eu quero restringir o acesso aos módulos administrativos de acordo com a sessão e os privilégios do usuário
  Para garantir que operadores não acessem dados sensíveis ou executem ações não autorizadas

  Contexto:
    Dado que a aplicação Alpha Engine e os middlewares de segurança estão ativos

  @sessao @redirecionamento
  Cenário: Bloqueio e redirecionamento de usuário não autenticado tentando acessar o painel
    Dado que o usuário não possui uma sessão administrativa ativa
    Quando o usuário tenta acessar a URL "/admin/dashboard"
    Então o sistema deve interromper o acesso e redirecionar o usuário para "/admin/login"

  @rbac @bloqueio_privilegio
  Cenário: Bloqueio de acesso para usuário autenticado sem permissão para módulo financeiro
    Dado que o usuário está autenticado com o papel "OPERADOR_ESTOQUE"
    Quando o usuário tenta acessar a rota restrita "/admin/configuracoes-financeiras"
    Então o sistema deve rejeitar o acesso com o status "403 Forbidden"

  @rbac @acesso_concedido
  Cenário: Permissão de acesso total para administrador com papel de gestão máxima
    Dado que o usuário está autenticado com o papel "ADMIN_GERAL"
    Quando o usuário acessa a rota administrativa "/admin/configuracoes-financeiras"
    Então a requisição deve ser autorizada com o status "200 OK"
    E a página administrativa solicitada deve ser carregada
