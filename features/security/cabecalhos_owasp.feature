# language: pt

@security @owasp @headers
Funcionalidade: Cabeçalhos HTTP de Segurança e Mitigações OWASP
  Como a camada de segurança da aplicação Alpha Engine
  Eu quero anexar cabeçalhos HTTP defensivos em todas as respostas da aplicação
  Para mitigar vulnerabilidades de Clickjacking, MIME-Sniffing e Cross-Site Scripting (XSS)

  Contexto:
    Dado que a aplicação Alpha Engine e os middlewares de segurança estão ativos

  @headers @public
  Cenário: Aplicação de cabeçalhos de segurança em rotas públicas da loja
    Quando uma requisição HTTP "GET" é realizada para a rota "/"
    Então os cabeçalhos de segurança "X-Frame-Options", "X-Content-Type-Options" e "Content-Security-Policy" devem estar presentes na resposta HTTP
    E o cabeçalho "X-Frame-Options" deve estar configurado com "SAMEORIGIN" ou "DENY"
    E o cabeçalho "X-Content-Type-Options" deve estar configurado como "nosniff"

  @headers @admin
  Cenário: Aplicação de cabeçalhos de segurança em rotas do painel administrativo
    Quando uma requisição HTTP "GET" é realizada para a rota "/admin/login"
    Então os cabeçalhos de segurança "X-Frame-Options", "X-Content-Type-Options" e "Content-Security-Policy" devem estar presentes na resposta HTTP
    E o cabeçalho "Referrer-Policy" deve estar configurado como "strict-origin-when-cross-origin"
