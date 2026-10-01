# Erro de Conexão com Banco de Dados - PHPMyAdmin

O problema foi resolvido! As senhas **estavam corretas**, o motivo da falha era um conflito de **portas internas vs. portas externas (host)**.

---

### 🔍 O que causava o erro?

No arquivo `.env`, você definiu:
```env
MARIADB_PORT=3307
```
Essa porta `3307` serve para mapear o banco para a sua **máquina host** (evitando conflito com outro MySQL local na porta 3306). 

No entanto, no [docker-compose.yml](file:///home/wattrelos/Docker/docker-compose.yml):
* O `phpmyadmin` estava configurado com `PMA_PORT: "${MARIADB_PORT:-3306}"` (ou seja, tentava conectar na porta `3307`).
* O `php` estava configurado com `DB_PORT: ${MARIADB_PORT:-3306}` (também porta `3307`).

Dentro da **rede interna do Docker**, o MariaDB sempre escuta na porta padrão **3306**. Ao tentar se conectar na porta `3307`, o phpMyAdmin recebia `Connection refused` (conexão recusada), gerando a impressão de que a senha ou o usuário estavam errados.

---

### 🛠️ O que foi corrigido

1. **Ajuste no [docker-compose.yml](file:///home/wattrelos/Docker/docker-compose.yml#L20-L57)**:
   Fixamos a comunicação interna entre os contêineres na porta padrão `3306`:
   ```yaml
   # php:
   DB_PORT: 3306

   # phpmyadmin:
   PMA_PORT: 3306
   ```
   *(A porta `3307` continua mapeada normalmente para a sua máquina host).*

2. **Recreação dos contêineres**:
   Aplicamos as novas configurações com `docker compose up -d php phpmyadmin` e reiniciamos o `nginx`.

3. **Testes de Conexão Validados**:
   * ✅ Login do `root` no phpMyAdmin: **Sucesso**
   * ✅ Login do `saas_user` no phpMyAdmin: **Sucesso**
   * ✅ Conexão vinda do host na porta `3307`: **Sucesso**
   * ✅ Aplicação web (`http://localhost:8071/`): **Sucesso (HTTP 200 em `/setup`)**

---

### 🚀 Como acessar a partir de agora

* **No phpMyAdmin** (`http://localhost:8072`):
  * **Usuário:** `root` | **Senha:** `rootpassword`
  * ou **Usuário:** `saas_user` | **Senha:** `saas_password`

* **No seu gerenciador local (DBeaver / Workbench / Terminal do Host)**:
  * **Host:** `127.0.0.1`
  * **Porta:** `3307`
  * **Usuário:** `root` (`rootpassword`) ou `saas_user` (`saas_password`)
  * **Banco:** `saas_db`

* **No Assistente de Instalação do SaaS** (`http://localhost:8071/setup`):
  * **Host do Banco:** `mariadb`
  * **Porta:** `3306`
  * **Usuário:** `saas_user` (ou `root`)
  * **Senha:** `saas_password` (ou `rootpassword`)
  * **Base de Dados:** `saas_db`