# language: pt

@frontend @pdp @estoque @avise_me @ADR0008 @UC_CLI_003 @UC_CLI_004
Funcionalidade: Alerta de Reposição de Estoque ("Avise-me quando chegar")
  Como um cliente navegando na loja virtual AG Sonhos
  Eu quero me cadastrar para receber um aviso por e-mail ou WhatsApp quando um produto ou variante esgotado voltar ao estoque
  Para que eu não perca a oportunidade de compra e possa adquirir o produto assim que ele for reabastecido

  Contexto:
    Dado que a loja virtual Alpha Engine está operacional
    E o catálogo possui produtos cadastrados com controle de estoque ativo

  @ui @pdp @esgotado
  Cenário: Visualização de produto simples com estoque zerado na PDP
    Dado que o produto "Formigres BIANCO GLOSS CZ BRI RT 60" possui saldo de estoque igual a 0
    Quando o usuário acessa a página do produto "/pt-br/produto/1"
    Então o botão padrão "Adicionar ao Carrinho" deve ser ocultado
    E o componente de aviso "Avise-me quando chegar" deve ser exibido com destaque
    E o badge de disponibilidade deve indicar indisponibilidade temporária

  @ui @variantes @UC_CLI_004
  Cenário: Seleção de variação esgotada altera dinamicamente o botão de compra para aviso de estoque
    Dado que um produto possui as variações "110V" com estoque e "220V" com saldo zerado
    Quando o usuário está na página do produto e clica na variação "220V"
    Então a opção "220V" deve receber a marcação visual "Esgotado"
    E o botão de compra deve ser imediatamente substituído pelo botão "Avise-me quando chegar"
    E o identificador da variação selecionada deve ser vinculado ao formulário de aviso

  @modal @captura_lead @LGPD
  Cenário: Abertura do modal e inscrição bem-sucedida com consentimento LGPD
    Dado que o usuário está visualizando um produto esgotado
    Quando o usuário clica no botão "Avise-me quando chegar"
    Então o modal de alerta de estoque deve ser exibido na tela
    E deve apresentar os campos "Seu Nome", "Seu E-mail", "WhatsApp (Opcional)" e o aceite de privacidade LGPD
    Quando o usuário preenche:
      | Campo            | Valor                        |
      | Nome             | Carlos Eduardo               |
      | E-mail           | carlos.eduardo@exemplo.com   |
      | WhatsApp         | (11) 98765-4321              |
      | Termos de LGPD   | Aceito                       |
    E clica em "Quero ser avisado!"
    Então o sistema deve registrar a intenção no banco relacional com status "pending" e data em UTC
    E uma mensagem de confirmação "Excelente! Assim que o estoque estiver disponível, nós avisaremos você por e-mail." deve ser exibida

  @seguranca @anti_spam @honeypot
  Cenário: Descarte silencioso de requisições maliciosas enviadas por robôs (Honeypot)
    Dado que um robô preenche o formulário com o campo oculto "form_check_company"
    Quando a requisição POST é enviada para "/pt-br/catalog/stock-alert/subscribe"
    Então o sistema deve responder com sucesso simulado HTTP 200
    Mas nenhum registro deve ser gravado na tabela "agsc_product_stock_alert"

  @validacao @regras_negocio
  Cenário: Rejeição de inscrição sem preenchimento de e-mail válido ou sem aceite da LGPD
    Dado que o modal de alerta de estoque está aberto
    Quando o usuário tenta submeter o formulário sem informar um e-mail válido
    Então o sistema deve exibir mensagem de erro "Por favor, informe um endereço de e-mail válido."
    E a requisição deve ser rejeitada com código HTTP 422
    E o botão de submissão não deve processar a gravação

  @mensageria @rabbitmq @cota_anti_frustracao @ADR0008
  Cenário: Processamento assíncrono de reposição com cota proporcional ao novo estoque
    Dado que existem 10 clientes cadastrados na fila de espera de um produto
    Quando o almoxarifado registra a entrada de 2 unidades físicas do produto no sistema
    Então um evento "StockReplenishedEvent" deve ser publicado na fila "notification.stock_alert" do RabbitMQ
    E o worker de background deve calcular a cota de notificações (máximo de 6 alertas prioritários)
    E deve selecionar os clientes em ordem estrita de chegada (FIFO)
    E deve disparar os e-mails transacionais e atualizar o status dos registros selecionados para "sent"

  @opt_out @lgpd_direito_esquecimento
  Cenário: Cancelamento da inscrição com um clique (Opt-Out) via link com token
    Dado que o cliente recebeu uma notificação com o token único de cancelamento
    Quando o usuário acessa o link "/pt-br/catalog/stock-alert/unsubscribe?token={token_valido}"
    Então o sistema deve marcar o alerta como "cancelled"
    E deve exibir uma tela de confirmação com a mensagem "Alerta Cancelado com Sucesso"
