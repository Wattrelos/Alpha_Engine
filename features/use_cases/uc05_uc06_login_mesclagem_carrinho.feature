# language: pt

@use_cases @uc05 @uc06 @autenticacao @sync_carrinho
Funcionalidade: Casos de Uso UC05 e UC06 - Autenticação e Mesclagem Automática do Carrinho
  Como um visitante com itens adicionados no carrinho
  Eu quero me autenticar na minha conta
  Para que meu carrinho anônimo seja integrado automaticamente ao meu perfil de cliente sem perda de produtos

  Contexto:
    Dado que o sistema "Alpha Engine - Loja Virtual" está ativo e operacional

  @uc05 @uc06 @include @sync
  Cenário: Autenticação de cliente e mesclagem automática do carrinho visitante
    Dado que eu sou um "Visitante" e possuo "2" itens no meu carrinho anônimo da sessão
    Quando eu realizo o login com minhas credenciais válidas "cliente@email.com" e "senha123"
    Então minha sessão deve ser convertida para "Cliente Logado"
    E o sistema deve mesclar automaticamente os "2" itens do carrinho visitante com o carrinho persistido da minha conta
    E o meu carrinho atualizado deve conter a união de todos os itens ativos
