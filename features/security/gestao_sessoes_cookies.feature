# language: pt

@security @sessoes @cookies @session_fixation
Funcionalidade: Gestão Segura de Sessões e Políticas de Cookies
  Como a arquitetura de autenticação da aplicação Alpha Engine
  Eu quero gerenciar o ciclo de vida das sessões com flags seguras e regeneração de identificadores
  Para prevenir ataques de Sequestro de Sessão (Session Hijacking) e Fixação de Sessão (Session Fixation)

  Contexto:
    Dado que a aplicação Alpha Engine e o serviço de sessões distribuídas no Redis estão ativos

  @session_fixation @regenerate_id
  Cenário: Regeneração de ID de sessão após autenticação com sucesso
    Dado que o visitante possui o ID de sessão anônimo "sess_anonima_123"
    Quando o visitante efetua login com credenciais válidas "marcos@email.com" e "Senha@123"
    Então o sistema deve regenerar o identificador de sessão para um novo ID seguro
    E o identificador anterior "sess_anonima_123" deve ser invalidado no Redis

  @cookies @httponly @secure @samesite
  Cenário: Configuração de cookies de sessão com atributos de proteção
    Quando uma sessão autenticada é inicializada
    Então o cookie "ALPHA_SESSION" deve conter as flags "HttpOnly", "Secure" e "SameSite=Lax"
    E o cookie não deve ser acessível via scripts JavaScript no navegador
