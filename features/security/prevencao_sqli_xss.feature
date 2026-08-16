# language: pt

@security @owasp @xss @sqli @sanitizacao
Funcionalidade: Prevenção de Injeção de Dados (SQL Injection & XSS)
  Como o motor de segurança e persistência da plataforma Alpha Engine
  Eu quero neutralizar scripts maliciosos e injeções de banco de dados em todos os pontos de entrada
  Para proteger os dados confidenciais dos usuários e a integridade da aplicação

  Contexto:
    Dado que a aplicação Alpha Engine e os middlewares de segurança estão ativos

  @xss @sanitizacao_comentarios
  Cenário: Sanitização de payload XSS em comentário ou avaliação de produto
    Dado que um cliente logado tenta enviar uma avaliação de produto
    Quando o cliente preenche o comentário com "<script>alert('XSS')</script>"
    Então o sistema deve sanitizar a entrada convertendo caracteres especiais em entidades HTML
    E o comentário persistido não deve conter tags executáveis de script

  @sqli @prepared_statements @pdo
  Cenário: Neutralização de tentativa de SQL Injection no campo de busca do catálogo
    Quando um usuário realiza uma busca pelo termo "' OR '1'='1' --"
    Então o repositório deve executar a consulta utilizando Prepared Statements com PDO
    E o sistema deve tratar o termo como texto literal sem alterar a lógica da consulta SQL
