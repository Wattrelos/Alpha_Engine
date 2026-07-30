# Refatoração do Fluxo de Autenticação e Cadastro (Auth) - Alpha Engine

Este documento registra o avanço na reestruturação e desacoplamento do módulo de Autenticação (Login e Cadastro), operando de forma 100% autônoma sob a **Alpha Engine** rodando no Slim standalone, sem dependência do framework legadodo código legado.

## O que foi implementado:

### 1. Camada de Apresentação e Componentização Visual (Twig & Atomic Design)
- **Componentização baseada em Atomic Design**: Substituição da função legada fictícia `component()` pelo uso nativo de `{% include %}` com passagem explícita de variáveis nas views.
- **Páginas de Apresentação**:
  - [login.twig](file:///var/www/html/agsonhos/resources/views/pages/users/login.twig): Interface refinada estendendo o layout base e injetando estados/erros do controlador de forma dinâmica.
  - [register.twig](file:///var/www/html/agsonhos/resources/views/pages/users/register.twig): Formulário completo de cadastro, aplicando o filtro `|raw` no contrato e formatando inputs dinamicamente (incluindo CPF/CNPJ e campos customizados).
- **Tradução Automática**: Renderização hidratada de idiomas a partir do arquivo físico local de translations.

### 2. Controladores e Ações Standalone (Actions Slim)
- **ShowLoginFormAction & LoginAction**:
  - `GET /login`: Renderiza o formulário de login.
  - `POST /login`: Valida credenciais com `AuthService` e `CustomerRepository`, define cookies assinados da sessão e retorna redirecionamento AJAX compatível com `data-oc-toggle="ajax"`.
- **ShowRegistrationFormAction & RegisterAction**:
  - `GET /cadastro`: Renderiza o formulário de cadastro hidratado com traduções e dados estáticos.
  - `POST /cadastro`: Intercepta a criação de conta do cliente, delega a lógica de negócio para o `CustomerRepository` e retorna a resposta AJAX em JSON estruturado com status adequados de redirecionamento ou erros de validação por campo.

### 3. Serviços e Segurança
- **AuthService.php**: Refatorado para usar injeção de dependência via constructor de forma segura, delegando validações e hashing ao `CustomerRepository`.
- **Middlewares de Segurança**:
  - `SessionMiddleware` e `SignatureMiddleware` ajustados para operar de forma encriptada consumindo a chave e dados de conexão do Redis vindos diretamente do `.env`.

### 4. Correção de Integridade e Fallbacks (Esta Rodada)
- **Resiliência e Resolução Standalone no CustomerRepository**:
  - Implementação dos métodos utilitários privados `getConfigValue` e `getTranslation` que fornecem resolução desacoplada via `SettingRepository`, `AbstractRepository::getStoreId()` e arquivos físicos locais PHP de idioma quando os serviços não chegam pré-injetados pelo container PSR-11 `AppContainer`.
  - Saneamento de chamadas diretas que disparavam `Call to a member function get() on null` nos fluxos de validação de CPF/CNPJ, campos customizados e validações de tamanho de senha.
- **Bootstrap da Aplicação**:
  - Inclusão dos helpers nativos de validação (`general.php`, `filter.php`, `validation.php`) no bootstrap `public_html/index.php`.
  - Inicialização global da fábrica de repositórios `RepositoryFactory` no contêiner Slim standalone.
- **Redis Failsafe e Resiliência (Fallback Automático)**:
  - Adicionado suporte a fallback automático para sessões PHP nativas (`$_SESSION`) no `AuthService` e `SessionMiddleware` caso o Redis esteja inacessível (ex: ambiente de desenvolvimento local).
  - Conexão lazy testada no construtor com timeout agressivo de 1.0s para evitar travamento da requisição inicial, definindo transparentemente a persistência em `$_SESSION`.
- **Integração com o AlphaSessionHandler**:
  - Alinhamos o nome do cookie de persistência local para `session_id` (`session_name('session_id')`) nos fluxos de autenticação do `AuthService` e do `SessionMiddleware`, dispensando cookies paralelos.
  - O estado do usuário autenticado é armazenado diretamente em `$_SESSION['logged_user']` no login e restaurado no middleware. O `AlphaSessionHandler` intercepta a escrita e salva serializado diretamente na tabela de banco `session`.
  - O logout executa a destruição nativa (`session_destroy()`), o que remove fisicamente a sessão do banco.
- **Estabilização do Fluxo de Logout**:
  - Finalizada a `LogoutAction` e seu registro no bootstrap `public_html/index.php` na rota `/logout`.
  - Remoção de cookies via cabeçalho HTTP de resposta PSR-7 (`Set-Cookie`) e exclusão de arquivo duplicado legado (`core/Services/Auth/AuthService.php`).
- **Substituição de Helpers por AlphaString**:
  - Implementação da classe `AlphaString` (`core/Support/AlphaString.php`) contendo métodos estáticos modernos e standalone de validação de comprimentos, e-mails, expressões regulares, IPs e URLs.
  - Portabilidade completa das validações de dados de cadastro e edição do `CustomerRepository.php` para utilizar os métodos modernos da classe `AlphaString`, removendo a necessidade das funções procedurais herdadas.
- **Validação de Formulários via Eventos (form-validator.js)**:
  - Criação da biblioteca `form-validator.js` (`public_html/js/custom/form-validator.js`) para gerenciar máscaras em tempo real para CPF/CNPJ e Telefone, verificação de compatibilidade de senhas e submissões genéricas via AJAX (`data-oc-toggle="ajax"`).
  - A biblioteca foi integrada ao layout global (`layouts/base.html.twig`) e o arquivo órfão `verifica-formulario-cadastro-cliente.js` foi deletado.
- **Implementação e Correção da Rota de Minha Conta (Account)**:
  - Correção do namespace de [AccountAction.php](file:///var/www/html/agsonhos/core/Controller/Actions/Customer/Auth/AccountAction.php) para `Alpha\Controller\Actions\Customer\Auth`.
  - Refatoração completa da action para renderizar dinamicamente a view Twig de conta [account.twig](file:///var/www/html/agsonhos/resources/views/pages/users/accounts/account.twig), definindo todas as traduções e links de rotas necessárias em pt-br.
  - Registro da rota `/account` no bootstrap [index.php](file:///var/www/html/agsonhos/public_html/index.php) associada à `SessionMiddleware` para proteção automática de autenticação.
  - Adição de redirecionamento de compatibilidade de `/account` para a versão com idioma `/pt-br/account`.
- **Implementação e Correção da Rota de Meus Pedidos (Orders)**:
  - Correção do namespace de [OrdersAction.php](file:///var/www/html/agsonhos/core/Controller/Actions/Customer/Auth/OrdersAction.php) para `Alpha\Controller\Actions\Customer\Auth`.
  - Refatoração completa da action para buscar os pedidos do cliente, obter a quantidade de itens por pedido, formatar os valores monetários e datas, e renderizar a view [orders.twig](file:///var/www/html/agsonhos/resources/views/pages/users/accounts/orders.twig).
  - Registro da rota `/account/orders` no bootstrap [index.php](file:///var/www/html/agsonhos/public_html/index.php) associada à `SessionMiddleware` para proteção automática de autenticação.
  - Adição de redirecionamento de compatibilidade de `/account/orders` para a versão com idioma `/pt-br/account/orders`.

### 5. Abstração e Especialização de Serviços de Autenticação (Alpha Engine)
- **Interface e Abstração Comum (`AuthServiceInterface` e `AbstractAuthService`)**:
  - Extração da lógica de conexões Redis e fallback transparente para `$_SESSION` de forma parametrizada e reutilizável.
- **Especialização do Escopo de Sessões (`CustomerAuthService` & `AdminAuthService`)**:
  - Separação de namespaces, prefixos de chaves do Redis e cookies de sessão (`session_id` para clientes e `admin_session_id` para administradores).
- **Segurança Dinâmica no Backoffice (Admin Login & Logout)**:
  - Eliminação de credenciais estáticas hardcoded no controlador administrativo [LoginAction.php](file:///var/www/html/agsonhos/core/Admin/Controllers/Actions/Auth/LoginAction.php), integrando a verificação de credenciais no banco de dados via [UserRepository](file:///var/www/html/agsonhos/core/Model/Domain/Repositories/UserRepository.php) com validação de status de atividade do administrador.
  - Implementação da `LogoutAction` de admin para encerramento completo da sessão.
- **Bloqueio de Brute-Force Administrativo**:
  - Recriação da tabela `tbkk_user_login` equivalente à de clientes (`customer_login`) e adição de métodos de controle de tentativas no `UserMapper` e `UserRepository`. O `AdminAuthService` bloqueia acessos caso ocorram mais de 5 tentativas consecutivas na última hora.
  - O login malsucedido calcula tentativas restantes e exibe a nova tela dedicada de segurança [error.html.twig](file:///var/www/html/agsonhos/resources/views/admin/auth/error.html.twig) com contagem regressiva e redirecionamento.
- **Proteção de Rotas com `AdminSessionMiddleware`**:
  - Middleware desenvolvido para validar acessos restritos do backoffice.
  - Correção de erro 404 nas rotas do painel via criação de arquivo de reescrita local `.htaccess` no diretório administrativo.

### 6. Estabilização e Alinhamento de Segurança do Login de Clientes
- **Bloqueio de Brute-Force e Verificação de Status**:
  - Refatoração da autenticação em [CustomerAuthService.php](file:///var/www/html/agsonhos/core/Auth/Services/CustomerAuthService.php) para alinhar-se à arquitetura do painel de administração.
  - O login de clientes agora valida se a conta está temporariamente bloqueada por excesso de tentativas (`isLockedOut` limitando a 5 tentativas).
  - Adicionada a validação do status ativo (`isStatus()`) da entidade cliente.
  - Integrada a contagem falha (`addLoginAttempt`) e a limpeza das tentativas (`resetLoginAttempts`) na persistência do banco após login bem-sucedido.



