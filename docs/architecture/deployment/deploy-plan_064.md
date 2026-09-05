# DP-64: On-Premise Tenant Provisioning & Setup Wizard

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-08-02 13:50:54
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/64

## Descrição

# Plano de Implementação - On-Premise Tenant Provisioning & Setup Wizard

Desenvolvimento do pipeline de instalação inicial e onboarding de tenants para a **Alpha Engine**, permitindo versionamento seguro no GitHub (`.env.example` zerado) e provisionamento automatizado de novos ambientes/bancos de dados.

---

## Estratégia e Arquitetura do Setup

### 1. Gestão de Estado via `.env` (`APP_INSTALLED`)
- **Estado Não Instalado (`APP_INSTALLED=false`)**:
  - Interceptação por middleware global logo na entrada da aplicação (`public_html/index.php`).
  - Bloqueio da inicialização do repositório/banco de dados em `AppBootstrap` (evitando exceções de conexão indisponível).
  - Redirecionamento automático de qualquer rota pública para `/setup`.
- **Estado Instalado (`APP_INSTALLED=true`)**:
  - Inicialização normal da aplicação.
  - Bloqueio de acesso às rotas `/setup` (retornando `403 Forbidden`).

### 2. Escrita Atômica do Arquivo `.env` (`EnvironmentManager`)
Criação da classe utilitária `Alpha\Support\EnvironmentManager` para:
- Leitura e parsing de pares chave-valor do `.env`.
- Escrita atômica utilizando arquivo temporário (`.env.tmp`), trava de arquivo (`LOCK_EX`) e substituição segura (`rename`).
- Escape e tratamento de caracteres especiais para evitar corrupção de variáveis.
- Geração automática de chaves secretas para `JWT_SECRET_KEY` e `API_SIGNATURE_SECRET` usando `random_bytes()`.
- Definição de permissões restritas de arquivo (`0600` / `0640`).

---

## Open Questions

> [!NOTE]
> 1. **Criação do Banco de Dados**: Se a base MySQL especificada no formulário não existir, o assistente tentará executar `CREATE DATABASE IF NOT EXISTS` com charset `utf8mb4_unicode_ci`.
> 2. **Esquema Inicial (DDL e Seeds)**: O instalador utilizará um arquivo de esquema completo (`resources/schema/install.sql`) que constrói todas as tabelas e dados fundamentais de inicialização (Idiomas, Status de Pedidos, Configurações Base, Zonas/Países).

---

## Proposed Changes

### Core Infrastructure & Environment

#### [MODIFY] [.env.example](file:///var/www/html/agsonhos/.env.example)
- Adicionar todas as variáveis de ambiente em branco com comentários explicativos (`APP_INSTALLED=false`, `DB_HOSTNAME=`, `DB_USERNAME=`, `DB_PASSWORD=`, `DB_DATABASE=`, `DB_PREFIX=agsc_`, etc.).

#### [NEW] [EnvironmentManager.php](file:///var/www/html/agsonhos/core/Support/EnvironmentManager.php)
- Gerenciador de leitura, validação e persistência atômica do arquivo `.env`.

#### [MODIFY] [config.php](file:///var/www/html/agsonhos/config.php)
- Refatorar a definição das constantes `DB_*` para consumir dinamicamente `$_ENV` ou `getenv()`.

---

### Middleware & Entrypoint Pipeline

#### [NEW] [InstallationCheckMiddleware.php](file:///var/www/html/agsonhos/core/Auth/Middleware/InstallationCheckMiddleware.php)
- Middleware responsável por checar o estado `APP_INSTALLED`.
- Redireciona requisições para `/setup` quando `APP_INSTALLED=false`.
- Retorna `403 Forbidden` se um usuário tentar acessar `/setup` quando `APP_INSTALLED=true`.

#### [MODIFY] [public_html/index.php](file:///var/www/html/agsonhos/public_html/index.php)
- Integrar a verificação de `APP_INSTALLED` antes do boot completo de `AppBootstrap` e banco de dados.

---

### Setup Actions & Wizard View

#### [NEW] [ShowSetupAction.php](file:///var/www/html/agsonhos/core/Controller/Actions/Setup/ShowSetupAction.php)
- Exibe o assistente visual de instalação em Twig.

#### [NEW] [TestDatabaseConnectionAction.php](file:///var/www/html/agsonhos/core/Controller/Actions/Setup/TestDatabaseConnectionAction.php)
- Action AJAX para validação instantânea de credenciais MySQL.

#### [NEW] [ProcessInstallationAction.php](file:///var/www/html/agsonhos/core/Controller/Actions/Setup/ProcessInstallationAction.php)
- Executa o pipeline de provisionamento:
  1. Teste e criação do Banco de Dados.
  2. Execução da Migration (DDL) e Carga Inicial (Seeds).
  3. Criação do Super Admin com hash `PASSWORD_ARGON2ID`.
  4. Gravação atômica do `.env` com `APP_INSTALLED=true`.

#### [NEW] [install.sql](file:///var/www/html/agsonhos/resources/schema/install.sql)
- Script SQL consolidador de esquema DDL e Seeds de onboarding.

#### [NEW] [installer.html.twig](file:///var/www/html/agsonhos/resources/views/setup/installer.html.twig)
- Interface visual moderna, fluida e responsiva (Dark mode, glassmorphism, etapas progressivas).

---

### Documentação & Especificações

#### [MODIFY] [instalation.md](file:///var/www/html/agsonhos/docs/instalation/instalation.md)
- Atualizar a especificação técnica com o protocolo moderno de provisionamento, fluxo Mermaid e contrato de variáveis.

---

## Verification Plan

### Automated Tests / Test Scripts
- Criar script de teste `tests/test_tenant_provisioning.php` para validar:
  1. O parsing e escrita atômica do `EnvironmentManager`.
  2. A criação de chave hash `ARGON2ID` para o Super Admin.
  3. A alternância do flag `APP_INSTALLED` e proteção contra re-instalação.

### Manual Verification
- Testar o assistente de instalação no navegador simulando um ambiente zerado (`APP_INSTALLED=false`), navegando pelas etapas, testando o banco e confirmando a transição de estado e acesso ao painel admin.

# Tarefas: On-Premise Tenant Provisioning & Setup Wizard

- [x] Atualizar `.env.example` com variáveis em branco e `APP_INSTALLED=false` <!-- id: 0 -->
- [x] Criar `Alpha\Support\EnvironmentManager.php` para leitura e escrita atômica do `.env` <!-- id: 1 -->
- [x] Refatorar `config.php` para ler variáveis de banco do ambiente (`$_ENV`) <!-- id: 2 -->
- [x] Criar `InstallationCheckMiddleware.php` para interceptar requisições baseadas em `APP_INSTALLED` <!-- id: 3 -->
- [x] Atualizar `public_html/index.php` integrando a verificação de instalação antes do boot completo de banco <!-- id: 4 -->
- [x] Criar esquema SQL inicial consolidado em `resources/schema/install.sql` <!-- id: 5 -->
- [x] Criar as Actions do Setup (`ShowSetupAction`, `TestDatabaseConnectionAction`, `ProcessInstallationAction`) <!-- id: 6 -->
- [x] Registrar rotas `/setup`, `/setup/test-db`, `/setup/process` em `Config/Routes.php` <!-- id: 7 -->
- [x] Criar o template Twig visual do instalador `resources/views/setup/installer.html.twig` <!-- id: 8 -->
- [x] Atualizar a documentação técnica `docs/instalation/instalation.md` <!-- id: 9 -->
- [x] Criar e executar script de teste `tests/test_tenant_provisioning.php` para validação automatizada <!-- id: 10 -->

# Walkthrough - On-Premise Tenant Provisioning & Setup Wizard

Implementação completa da funcionalidade de **Assistente de Instalação (Setup Wizard)** e **Onboarding de Tenants (On-Premise)** na **Alpha Engine**. O sistema permite versionamento seguro no GitHub (via `.env.example` zerado) e realiza o provisionamento completo de novos bancos de dados, criação do Super Admin com hash `PASSWORD_ARGON2ID` e atualização atômica da flag `APP_INSTALLED=true` no `.env`.

---

## Alterações Realizadas

### 1. Arquivo de Exemplo `.env.example`
- **Arquivo**: [.env.example](file:///var/www/html/agsonhos/.env.example)
- Adicionadas todas as variáveis padrão zeradas e configurada a flag `APP_INSTALLED=false`.

### 2. Gerenciador de Ambiente (`EnvironmentManager`)
- **Arquivo**: [EnvironmentManager.php](file:///var/www/html/agsonhos/core/Support/EnvironmentManager.php)
- Implementada a leitura, parsing e gravação atômica (`.env.tmp` + `rename` + `LOCK_EX`) com permissões `0640`, sanitização de caracteres especiais e geração de chaves secretas criptográficas (`JWT_SECRET_KEY`, `API_SIGNATURE_SECRET`).

### 3. Middleware de Controle de Instalação (`InstallationCheckMiddleware`)
- **Arquivo**: [InstallationCheckMiddleware.php](file:///var/www/html/agsonhos/core/Auth/Middleware/InstallationCheckMiddleware.php)
- Interceptação global de rotas baseada em `APP_INSTALLED`:
  - `APP_INSTALLED=false`: Redireciona qualquer requisição pública para `/setup` (status 302).
  - `APP_INSTALLED=true`: Bloqueia tentativas de acesso ao `/setup` com status `403 Forbidden`.

### 4. Ponto de Entrada (`public_html/index.php`) & Configuração
- **Arquivos**: [index.php](file:///var/www/html/agsonhos/public_html/index.php) e [config.php](file:///var/www/html/agsonhos/config.php)
- Desvio do fluxo de boot do banco de dados quando `APP_INSTALLED=false`, evitando erros de conexão indisponível durante o primeiro acesso.
- Refatoradas as constantes `DB_*` para consumir dinamicamente `$_ENV` / `getenv()`.

### 5. Actions do Setup & Rotas Slim
- **Arquivos**:
  - [ShowSetupAction.php](file:///var/www/html/agsonhos/core/Controller/Actions/Setup/ShowSetupAction.php): Checa requisitos do servidor (PHP $\ge 8.1$, extensões, permissões) e renderiza a view.
  - [TestDatabaseConnectionAction.php](file:///var/www/html/agsonhos/core/Controller/Actions/Setup/TestDatabaseConnectionAction.php): Rota AJAX `/setup/test-db` para teste instantâneo de conexão MySQL.
  - [ProcessInstallationAction.php](file:///var/www/html/agsonhos/core/Controller/Actions/Setup/ProcessInstallationAction.php): Rota `/setup/process` que executa `CREATE DATABASE IF NOT EXISTS`, importa `install.sql`, grava configurações da loja, cria o Super Admin (`PASSWORD_ARGON2ID`) e atualiza o `.env` atômico.
  - [Routes.php](file:///var/www/html/agsonhos/Config/Routes.php): Registradas as rotas do setup.

### 6. Interface Visual (Twig Template) & Arquivo SQL
- **Arquivos**:
  - [installer.html.twig](file:///var/www/html/agsonhos/resources/views/setup/installer.html.twig): Interface em 4 etapas (Requisitos, Banco de Dados, Loja/Admin, Conclusão) com design dark mode, glassmorphism e animações.
  - [install.sql](file:///var/www/html/agsonhos/resources/schema/install.sql): Esquema DDL consolidado e seeds baseline do sistema.

### 7. Documentação Técnica
- **Arquivo**: [instalation.md](file:///var/www/html/agsonhos/docs/instalation/instalation.md)
- Atualizada a especificação com o resumo técnico, YAML de automação e o diagrama de sequência em Mermaid.

---

## Resultados da Verificação Automatizada

- **Script de Teste**: [test_tenant_provisioning.php](file:///var/www/html/agsonhos/tests/test_tenant_provisioning.php)

Resultados da execução da suíte de testes:
```text
=== INICIANDO SUÍTE DE TESTES: TENANT PROVISIONING & SETUP ===

[TEST 1] EnvironmentManager - Leitura e Parsing do .env.example / .env
  isInstalled() inicial: false
  [PASS] isInstalled() respondeu corretamente para estado uninstalled.

[TEST 2] EnvironmentManager - Escrita Atômica e Geração de Chaves
  APP_INSTALLED lido do .env: true
  JWT_SECRET_KEY gerada: 2aa9770aa1...
  [PASS] Escrita atômica do .env funcionou com sucesso.

[TEST 3] InstallationCheckMiddleware - Bloqueio de Rota /setup quando APP_INSTALLED=true
  Status retornado para GET /setup com APP_INSTALLED=true: 403
  [PASS] Middleware bloqueou acesso ao /setup com status 403 Forbidden.

[TEST 4] InstallationCheckMiddleware - Redirecionamento 302 quando APP_INSTALLED=false
  Status retornado para GET / com APP_INSTALLED=false: 302
  Header Location: /setup
  [PASS] Redirecionamento 302 para /setup funcionou perfeitamente.

=== TODOS OS TESTES DE TENANT PROVISIONING PASSARAM COM SUCESSO! ===
```

