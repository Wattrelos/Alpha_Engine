# DP-86: Adicionar Testes BDD de Autenticação (`login.feature`)

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-08-21 23:56:11
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/86

## Descrição

# Plano de Implementação: Adicionar Testes BDD de Autenticação (`login.feature`)

Adição da especificação de testes BDD em sintaxe Gherkin no arquivo [login.feature](/features/auth/login.feature) (atualmente vazio / 0 bytes), acompanhada da criação do contexto de execução [AuthContext.php](/features/bootstrap/AuthContext.php), atualização da suíte no [behat.yml](/behat.yml) e atualização da documentação de rastreabilidade em [reame.md](/features/reame.md).

---

## Cenários Propostos para `login.feature`

O arquivo Gherkin [login.feature](/features/auth/login.feature) cobrirá os seguintes fluxos e regras de negócio:

1. **`@sucesso @login_valido`**: Login com credenciais válidas -> Geração de sessão segura, emissão de cookie `session_id` e redirecionamento para `/conta`.
2. **`@falha @credenciais_invalidas`**: Tentativa de login com senha incorreta -> Bloqueio de autenticação e retorno da mensagem de erro amigável.
3. **`@falha @usuario_inexistente`**: Tentativa de login com e-mail não cadastrado -> Rejeição e status HTTP 400.
4. **`@falha @conta_inativa`**: Tentativa de login em conta inativa/desativada -> Rejeição de login.
5. **`@redirecionamento @url_pretendida`**: Redirecionamento após login para URL pretendida (ex: parâmetro `redirect=/checkout`).
6. **`@logout @encerramento_sessao`**: Logout do cliente -> Destruição da sessão ativa, expiração de cookies e redirecionamento.

---

## Modificações Propostas

### 1. Especificação de Teste Gherkin
#### [MODIFY] [login.feature](/features/auth/login.feature)
- Implementar a funcionalidade em português (`# language: pt`), com tags `@auth @login @cliente @autenticacao @UC05 @RF014 @RN008`, contexto e todos os cenários descritos.

### 2. Contexto Behat
#### [NEW] [AuthContext.php](/features/bootstrap/AuthContext.php)
- Implementar as definições de passos (`Given`, `When`, `Then`) do módulo de autenticação.
- Integrar com `CustomerAuthService`, `LoginAction`, `LogoutAction`, `SessionManager` e asserções estáticas do PHPUnit.

### 3. Configuração Behat
#### [MODIFY] [behat.yml](/behat.yml)
- Registrar `AuthContext` na lista de contextos da suíte padrão.

### 4. Documentação Acadêmica
#### [MODIFY] [reame.md](/features/reame.md)
- Incluir o módulo `features/auth/login.feature` e o contexto `AuthContext` na tabela de rastreabilidade e na árvore de diretórios.

---

## Plano de Verificação

### Testes Automatizados
- Executar `./vendor/bin/behat --dry-run` para validar a sintaxe e o mapeamento dos novos cenários e passos.
- Executar `./vendor/bin/behat features/auth/login.feature` para verificar a execução isolada do teste de login.
- Executar `./vendor/bin/behat` para garantir que toda a suíte de testes BDD (93+ cenários) continue passando com 100% de sucesso.

# Walkthrough: Implementação do Teste BDD de Autenticação (`login.feature`)

Adição da especificação de testes BDD em sintaxe Gherkin para o fluxo de autenticação e gerenciamento de sessões, implementação do contexto Behat dedicado e integração com o ecossistema de testes do projeto.

---

## 🎯 Alterações Realizadas

### 1. Especificação Gherkin de Autenticação
- **Arquivo**: [login.feature](/features/auth/login.feature)
- Foram implementados **6 cenários completos** em português cobrindo:
  - **Login Válido (`@sucesso @login_valido`)**: Validação de credenciais de cliente, criação de sessão no Redis/PHP, cabeçalho de cookie seguro `session_id` (`HttpOnly`) e retorno de redirecionamento para `/conta`.
  - **Senha Incorreta (`@falha @credenciais_invalidas`)**: Rejeição de login com retorno da mensagem amigável de erro `"Aviso: Seu endereço de e-mail e/ou senha não coincidem."`.
  - **E-mail Inexistente (`@falha @usuario_inexistente`)**: Rejeição de credenciais não registradas com status HTTP 400.
  - **Conta Inativa (`@falha @conta_inativa`)**: Bloqueio de login para contas desativadas.
  - **Redirecionamento Inteligente (`@redirecionamento @url_pretendida`)**: Redirecionamento de volta para o parâmetro `redirect` (ex: `/checkout`).
  - **Encerramento de Sessão (`@logout @encerramento_sessao`)**: Destruição da sessão do cliente, expiração do cookie de sessão e redirecionamento (302) para a tela de login.

---

### 2. Contexto Behat (Step Definitions)
- **Arquivo**: [AuthContext.php](/features/bootstrap/AuthContext.php)
- Implementa todas as etapas (`Given`, `When`, `Then`) do módulo de autenticação:
  - Integração com `CustomerAuthService`, `CustomerRepository`, `LoginAction` e `LogoutAction`.
  - Simulação de requisições PSR-7 (`ServerRequestFactory`, `Response`) e roteamento Slim (`RouteContext`, `RouteParserInterface`).
  - Asserções estáticas nativas com `PHPUnit\Framework\Assert`.

---

### 3. Configurações e Documentação
- **[behat.yml](/behat.yml)**: `AuthContext` registrado na suíte de testes padrão.
- **[reame.md](/features/reame.md)**: Atualizada a tabela de rastreabilidade de requisitos e árvore de diretórios dos testes.

---

## 🧪 Resultados dos Testes

### Execução Isolada (`features/auth/login.feature`)
```bash
./vendor/bin/behat features/auth/login.feature
```
```
6 cenários (6 passaram)
38 definições (38 passaram)
0m0.31s (19.40Mb)
```

### Execução Completa da Suíte BDD
```bash
composer test:behat
```
```
99 cenários (99 passaram)
647 definições (647 passaram)
0m2.10s (19.83Mb)
```
# Walkthrough: Teste BDD de Autenticação com Persistência no Banco de Dados MySQL

Atualização da suíte de testes BDD de autenticação em [login.feature](/features/auth/login.feature) e [AuthContext.php](/features/bootstrap/AuthContext.php) para **persistir, consultar e validar diretamente no banco de dados real MySQL (`agsc_customer`)** com limpeza automatizada por hooks.

---

## 🛠️ O que foi implementado

### 1. Persistência e Consulta Real no MySQL ([AuthContext.php](/features/bootstrap/AuthContext.php))
- **Inicialização Real do Backend**: Inicializa o `AppBootstrap::boot()` da aplicação, obtém o repositório de clientes real ([CustomerRepository](/backend/core/Model/Domain/Repositories/CustomerRepository.php)) a partir do `RepositoryFactory` e conecta diretamente com o MySQL via `ConnectionDB`.
- **Cadastro Simulado com Hash de Senha**: No passo `Dado que existe um cliente cadastrado...`, o cliente é inserido fisicamente na tabela `agsc_customer` com a senha criptografada via `password_hash($password, PASSWORD_DEFAULT)`.
- **Autenticação Real**: A `LoginAction` aciona o `CustomerAuthService`, que executa a query SQL real `findByEmail()` e a validação nativa `password_verify()` contra o hash gravado no banco de dados.
- **Contas Inativas e Usuários Inexistentes**: Usuários desativados (`status = 0`) ou não cadastrados no MySQL são devidamente validados e rejeitados pela camada de domínio.
- **Hooks Automatizados de Limpeza (`@BeforeScenario` e `@AfterScenario`)**: Todos os clientes e tentativas de login gerados durante a execução dos cenários são automaticamente excluídos do banco de dados MySQL para garantir idempotência e banco limpo.

---

## 🧪 Resultados dos Testes

### Execução do Módulo de Autenticação com BD Real
```bash
./vendor/bin/behat features/auth/login.feature
```
```
6 cenários (6 passaram)
38 definições (38 passaram)
0m4.82s (21.68Mb)
```

### Execução da Bateria Completa BDD
```bash
composer test:behat
```
```
99 cenários (99 passaram)
647 definições (647 passaram)
0m6.15s (22.43Mb)
```

