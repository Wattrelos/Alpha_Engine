# language: pt

@checkout @visitante @guest @UC09 @RF018
Funcionalidade: Checkout Rápido para Comprador Visitante (Guest Checkout)
  Como um visitante não cadastrado na plataforma
  Eu quero concluir minha compra fornecendo apenas os dados essenciais de entrega e pagamento
  Para que eu realize o pedido de forma ágil sem a obrigatoriedade de criar uma senha antecipadamente

  Contexto:
    Dado que a loja virtual Alpha Engine está operacional
    E o visitante anônimo possui produtos no carrinho totalizando "R$ 189,00"
    E a funcionalidade "Comprar como Visitante" está habilitada nas configurações

  @guest @sucesso
  Cenário: Finalização bem-sucedida de pedido por comprador visitante
    Dado que o visitante optou por "Comprar como Visitante"
    Quando o visitante preenche os seguintes dados de identificação e entrega:
      | Campo             | Valor                    |
      | Nome Completo     | Mariana Fernandes        |
      | E-mail            | mariana.guest@email.com  |
      | CPF               | 123.456.789-00           |
      | Telefone          | (11) 98765-4321          |
      | CEP               | 04012-000                |
      | Endereço          | Rua Domingos de Morais   |
      | Número            | 500                      |
      | Bairro            | Vila Mariana             |
      | Cidade / UF       | São Paulo / SP           |
    E seleciona o pagamento via "PIX"
    E submete o pedido para fechamento
    Então o sistema deve criar o pedido associado ao cliente visitante
    E deve retornar status de sucesso com o código identificador do pedido gerado
    E deve disparar a confirmação da compra e chave de pagamento para o e-mail "mariana.guest@email.com"

  @validacao @campos_obrigatorios
  Cenário: Validação de campos obrigatórios incompletos no checkout de visitante
    Dado que o visitante está no formulário de checkout visitante
    Quando o visitante tenta submeter o pedido sem preencher o campo "E-mail" e "CPF"
    Então o sistema deve bloquear o envio do formulário
    E deve sinalizar os campos inválidos com a mensagem "Preenchimento obrigatório para emissão da nota fiscal e envio do comprovante"
    E nenhuma ordem de pedido deve ser criada no banco de dados

  @validacao @cpf_invalido
  Cenário: Rejeição de documento CPF inválido no checkout visitante
    Dado que o visitante preenche o formulário com o CPF inválido "111.222.333-00"
    Quando o visitante tenta avançar para o pagamento
    Então o sistema deve validar o algoritmo do CPF e acusar "O CPF informado é inválido"
    E o checkout deve impedir a continuidade do processamento
