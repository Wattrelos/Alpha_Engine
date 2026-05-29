# Refatoração do Fluxo de Autenticação e Cadastro (Auth) - Alpha Engine

Este documento registra o avanço na reestruturação e desacoplamento do módulo de Autenticação (Login e Cadastro), operando de forma 100% autônoma sob a **Alpha Engine** rodando no Slim standalone, sem dependência do framework legado do OpenCart.

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
- **Resiliência do CustomerRepository contra Registry Nulo**:
  - Implementação dos métodos utilitários privados `getConfigValue` e `getTranslation` que fornecem caminhos alternativos de injeção (usando `SettingRepository` e arquivos físicos locais PHP de idioma) quando serviços clássicos do OpenCart como `config` e `language` não estão presentes no `Registry` da aplicação standalone.
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
