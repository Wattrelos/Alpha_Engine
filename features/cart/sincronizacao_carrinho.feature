# language: pt

@cart @sincronizacao @sessao @UC06 @RF014
Funcionalidade: Sincronização e Mesclagem do Carrinho de Compras
  Como um cliente cadastrado que iniciou a navegação de forma anônima
  Eu quero que os itens adicionados durante a sessão de visitante sejam mesclados ao meu carrinho permanente após o login
  Para que eu não perca os produtos selecionados e possa concluir minha compra com praticidade

  Contexto:
    Dado que a loja virtual Alpha Engine está operacional
    E o gerenciador de sessões e autenticação de clientes está ativo

  @merge @sucesso
  Cenário: Mesclagem automática do carrinho visitante com o carrinho persistido da conta
    Dado que o visitante anônimo adicionou os seguintes itens ao carrinho da sessão:
      | Produto                     | Quantidade | Preço Unitário |
      | Disco de Corte Diamantado   | 2          | R$ 35,00       |
      | Trena Emborrachada 5m       | 1          | R$ 25,00       |
    E a conta do cliente "cliente@email.com" já possuía previamente em seu carrinho salvo:
      | Produto                     | Quantidade | Preço Unitário |
      | Caixa de Parafusos Phillips | 1          | R$ 18,00       |
    Quando o cliente realiza login com o e-mail "cliente@email.com" e senha "senha123"
    Então o sistema deve acionar o serviço de sincronização do carrinho (SyncCartAction)
    E o carrinho unificado do cliente autenticado deve conter os "3" produtos distintos
    E o subtotal do carrinho deve ser recalculado para a soma total de "R$ 113,00"
    E todos os itens devem estar persistidos na tabela do banco de dados vinculados ao ID do cliente

  @merge @itens_duplicados
  Cenário: Mesclagem com soma de quantidades para produtos idênticos
    Dado que o visitante possui no carrinho de sessão "2" unidades de "Silicone Selante Transparente"
    E a conta salva do cliente já possuía "3" unidades do mesmo item "Silicone Selante Transparente"
    Quando o cliente efetua a autenticação na plataforma
    Então o sistema deve consolidar o produto em uma única linha no carrinho
    E a quantidade total acumulada do item deve ser de "5" unidades
    E o sistema deve verificar a disponibilidade de estoque para a quantidade somada

  @logout @persistencia
  Cenário: Preservação do carrinho salvo após logout e novo acesso
    Dado que o cliente logado possui "4" itens em seu carrinho persistido
    Quando o cliente efetua logout da sua conta
    Então a sessão de visitante anônimo é limpa
    E quando o cliente realizar um novo login em qualquer dispositivo
    Então os "4" itens previamente salvos devem ser restaurados com os valores e configurações originais
