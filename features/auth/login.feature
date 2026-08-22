# language: pt

@auth @login @cliente @autenticacao @UC05 @RF014 @RN008
Funcionalidade: Autenticação de Usuários e Gestão de Sessão de Login
  Como um cliente ou visitante da plataforma Alpha Engine
  Eu quero me autenticar com minhas credenciais (e-mail e senha) no sistema
  Para acessar minha conta, acompanhar pedidos e realizar compras com segurança

  Contexto:
    Dado que o sistema "Alpha Engine - Loja Virtual" está ativo e operacional
    E que o serviço de autenticação e repositório de clientes estão disponíveis

  @sucesso @login_valido @RF014
  Cenário: Login efetuado com sucesso com credenciais válidas
    Dado que existe um cliente cadastrado com e-mail "cliente.teste@alphaengine.com.br" e senha "SenhaSegura@123"
    Quando o usuário submete o formulário de login com e-mail "cliente.teste@alphaengine.com.br" e senha "SenhaSegura@123"
    Então o sistema deve autenticar o usuário com sucesso
    E deve criar uma sessão autenticada para o cliente
    E deve definir o cookie de sessão seguro "session_id"
    E a resposta deve indicar o redirecionamento para a página "/conta"

  @falha @credenciais_invalidas
  Cenário: Tentativa de login com senha incorreta
    Dado que existe um cliente cadastrado com e-mail "cliente.teste@alphaengine.com.br" e senha "SenhaSegura@123"
    Quando o usuário submete o formulário de login com e-mail "cliente.teste@alphaengine.com.br" e senha "SenhaIncorreta"
    Então o sistema deve recusar a autenticação
    E deve retornar uma mensagem de erro "Aviso: Seu endereço de e-mail e/ou senha não coincidem."
    E nenhuma sessão autenticada deve ser criada

  @falha @usuario_inexistente
  Cenário: Tentativa de login com e-mail não cadastrado
    Quando o usuário submete o formulário de login com e-mail "inexistente@dominio.com.br" e senha "Senha123"
    Então o sistema deve recusar a autenticação
    E deve retornar o status de erro HTTP 400

  @falha @conta_inativa
  Cenário: Tentativa de login em conta desativada ou inativa
    Dado que existe um cliente cadastrado com e-mail "inativo@alphaengine.com.br" com o status inativo
    Quando o usuário submete o formulário de login com e-mail "inativo@alphaengine.com.br" e senha "Senha@123"
    Então o sistema deve recusar a autenticação
    E não deve permitir o acesso ao painel do cliente

  @redirecionamento @url_pretendida
  Cenário: Redirecionamento após login para a URL pretendida original
    Dado que o usuário tentou acessar a página restrita "/checkout"
    Quando o usuário submete o formulário de login com credenciais válidas e parâmetro redirect "/checkout"
    Então o sistema deve autenticar o usuário com sucesso
    E a resposta deve indicar o redirecionamento para a página "/checkout"

  @logout @encerramento_sessao
  Cenário: Encerramento de sessão (Logout) do cliente logado
    Dado que o cliente "cliente.teste@alphaengine.com.br" possui uma sessão ativa
    Quando o cliente solicita o encerramento da sessão através da rota de logout
    Então o sistema deve invalidar a sessão do cliente
    E deve expirar o cookie de autenticação
