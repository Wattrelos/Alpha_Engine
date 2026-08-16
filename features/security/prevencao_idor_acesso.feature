# language: pt

@security @idor @autorizacao @acesso_recursos
Funcionalidade: Prevenção de Referência Direta Insegura a Objetos (IDOR)
  Como o módulo de controle de acesso aos recursos do Alpha Engine
  Eu quero validar se o usuário autenticado é o legítimo proprietário do recurso solicitado
  Para impedir que atacantes visualizem pedidos, notas fiscais ou dados cadastrais de outros clientes

  Contexto:
    Dado que a aplicação Alpha Engine e os middlewares de segurança estão ativos
    E o cliente "joao@email.com" com ID "101" está autenticado no sistema

  @idor @pedidos_alheios
  Cenário: Tentativa de visualização de pedido de outro cliente via manipulação de ID
    Dado que o pedido "#2055" pertence exclusivamente ao cliente de ID "202"
    Quando o cliente "101" tenta acessar os detalhes do pedido "/cliente/pedidos/2055"
    Então o sistema deve verificar a posse do recurso no repositório de pedidos
    E deve rejeitar a requisição com o status HTTP "403 Forbidden" ou "404 Not Found"
    E nenhum dado confidencial do pedido "#2055" deve ser exibido

  @idor @endereco_alheio
  Cenário: Tentativa de exclusão de endereço de outro cliente
    Dado que o endereço "addr-999" pertence ao cliente com ID "202"
    Quando o cliente "101" envia a requisição "DELETE /cliente/enderecos/addr-999"
    Então o sistema deve abortar a exclusão e retornar status de erro de autorização
