## [Bug/Refactor] Implementar fallback gracioso para anti-CSRF sem cache Redis## Descrição
Atualmente, o sistema anti-CSRF depende obrigatoriamente do cache Redis para validar os tokens. Quando o Redis está indisponível ou não configurado no ambiente, o sistema lança um erro fatal (crash), interrompendo o fluxo do usuário. Precisamos implementar um mecanismo de fallback gracioso para que o sistema continue funcionando de forma segura, mesmo sem o Redis.
## Comportamento Atual

* Sistema anti-CSRF tenta conectar ao Redis.
* Se o Redis falhar ou estiver ausente, o sistema gera uma exceção não tratada (Exception).
* A requisição do usuário falha com erro 400 - Requisição Rejeitada (CSRF).

## Comportamento Esperado (Solução)

* O sistema deve detectar a ausência do Redis. De preferência, logo no início do carregamento do sistema (Controller Base). 
* Ativar um fallback temporário (ex: persistência em sessão baseada em arquivos ou memória de curto prazo, se seguro).
* Caso o fallback também não seja possível, falhar de forma graciosa exibindo uma mensagem amigável ao usuário, em vez de derrubar a aplicação.
* Registrar um log de erro detalhado (Warning/Error) sobre a ausência do Redis. De preferência em um log separado do log de erros comuns.

## Passos para Reproduzir

   1. Desligue o serviço do Redis no ambiente local.
   2. Tente submeter qualquer formulário que exija validação de token CSRF.
   3. Observe o erro 400 - Requisição Rejeitada (CSRF) na resposta.

## Critérios de Aceite

* A aplicação não deve quebrar (erro 400 - Requisição Rejeitada (CSRF)) se o Redis estiver fora do ar.
* O fallback deve manter a segurança mínima contra ataques CSRF.
* Logs apropriados devem ser gerados quando o fallback for ativado.
