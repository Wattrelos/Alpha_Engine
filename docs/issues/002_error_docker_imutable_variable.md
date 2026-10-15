
# 002_error_docker_imutable_variable.md

### 🔍 O que causou o erro `Table 'saas_db.agsc_setting' doesn't exist`?

1. **A instalação ocorreu com sucesso em `alpha_docker`**:
   O Setup Wizard criou todas as 124 tabelas da loja dentro do banco `alpha_docker` e registrou `DB_DATABASE=alpha_docker` no arquivo [backend/.env](file:///home/wattrelos/Docker/Beta_engine_SaaS/backend/.env#L13).
2. **O Docker estava forçando `saas_db`**:
   No [docker-compose.yml](file:///home/wattrelos/Docker/docker-compose.yml), o contêiner `php` estava recebendo a variável de ambiente `DB_DATABASE: ${MYSQL_DATABASE}` (que no `.env` do Docker é `saas_db`).
3. **Imutabilidade do Dotenv**:
   A aplicação PHP utilizava `Dotenv\Dotenv::createImmutable(...)`. Na biblioteca `vlucas/phpdotenv`, o modo **imutável** nunca sobrescreve variáveis que já foram previamente injetadas pelo sistema operacional ou pelo Docker. 
   Por isso, o PHP ignorava o `alpha_docker` do `.env` do SaaS e tentava buscar as configurações no `saas_db` (que é um banco vazio), disparando o erro de tabela ausente.

---

### 🛠️ O que foi corrigido

1. **Tornamos o carregamento do `.env` mutável**:
   Nos arquivos [backend/config.php](file:///home/wattrelos/Docker/Beta_engine_SaaS/backend/config.php#L12), [adm_fIJGFvXKtvL7/index.php](file:///home/wattrelos/Docker/Beta_engine_SaaS/public_html/adm_fIJGFvXKtvL7/index.php#L13), [public_html/index.php](file:///home/wattrelos/Docker/Beta_engine_SaaS/public_html/index.php#L68) e no template do gerador de instalações [ProcessInstallationAction.php](file:///home/wattrelos/Docker/Beta_engine_SaaS/backend/core/Controller/Actions/Setup/ProcessInstallationAction.php#L198), alteramos para:
   ```php
   $dotenv = Dotenv\Dotenv::createUnsafeMutable(...);
   $dotenv->safeLoad();
   ```
   Isso assegura que o arquivo de configuração do SaaS (`backend/.env`) tenha prioridade total sobre as variáveis herdadas do Docker.

2. **Remoção de variáveis de banco fixas no serviço PHP do [docker-compose.yml](file:///home/wattrelos/Docker/docker-compose.yml#L18-L23)**:
   Removemos `DB_DATABASE`, `DB_USERNAME` e `DB_PASSWORD` do contêiner `php` para evitar novos conflitos entre contêineres e a aplicação.

3. **Recreação do contêiner**:
   Reiniciamos o contêiner com `docker compose up -d php`.

---

### ✅ Status dos Acessos

* 🔐 **Painel Administrativo**:
  👉 **`http://localhost:8071/adm_fIJGFvXKtvL7/`**
  *(Retornando **HTTP 200** - Tela de login exibida com sucesso).*

* 🛒 **Loja Pública**:
  👉 **`http://localhost:8071/`** (redireciona para `/pt-br`)
  *(Retornando **HTTP 200** - Catálogo e páginas iniciais carregando normalmente).*

---