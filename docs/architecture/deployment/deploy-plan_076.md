# DP-76: Ajuste no Mecanismo de Implantação (Deploy & Setup Wizard)

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-08-13 14:41:46
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/76

## Descrição

# Plano de Ajuste no Mecanismo de Implantação (Deploy & Setup Wizard)

Ajuste do assistente de primeira instalação para criar o diretório e atalho do Dashboard Administrativo diretamente na pasta `public_html/` da raiz do host (e não em `backend/public_html/`), movendo o arquivo `index.php` da pasta modelo `LPDHED2dC7Gjrg2b` para a nova pasta administrativa definida pelo usuário durante o provisionamento.

## Análise de Segurança da Pasta Pública do Admin

> [!IMPORTANT]
> **Arquitetura Defensiva (Isolamento de Código-Fonte)**
> A pasta do Dashboard em `public_html/$adminDir` contém **exclusivamente** o ponteiro `index.php` e a configuração de reescrita `.htaccess`. Todo o código-fonte da aplicação (`core/`), configurações (`config.php`), segredos e credenciais (`.env`), bibliotecas (`vendor/`) e templates (`resources/views`) permanecem protegidos dentro da pasta `backend/`, fora do Document Root público do servidor web.

### Medidas de Segurança Adotadas no Provisionamento:
1. **Eliminação da Pasta Padrão (`LPDHED2dC7Gjrg2b`)**: Durante a instalação, se o usuário escolher um nome personalizado para a pasta admin, a pasta modelo `LPDHED2dC7Gjrg2b` é **renomeada ou excluída** para evitar que atalhos residuais fiquem expostos à internet.
2. **Mascaramento de URL (Security through Obscurity)**: A URL do admin é customizada ou gerada aleatoriamente (ex: `adm_a8f9c10b`), inviabilizando varreduras automatizadas por robôs em caminhos padrões como `/admin` ou `/dashboard`.
3. **Bloqueio de Listagem de Diretório**: A pasta do admin recebe um arquivo `.htaccess` com `Options -Indexes`, impedindo que navegadores listem arquivos do diretório.

---

## Alterações Propostas

### 1. Backend Core Setup Action

#### [MODIFY] [ProcessInstallationAction.php](file:///var/www/html/agsonhos/backend/core/Controller/Actions/Setup/ProcessInstallationAction.php)

- **Correção de caminho do `public_html`**:
  Ajustar o cálculo de `$publicHtmlDir` no passo 6 da ação de instalação. Atualmente o código usava `__DIR__ . '/../../../../public_html'`, o que apontava para `backend/public_html`. O caminho correto sobe 5 níveis a partir de `backend/core/Controller/Actions/Setup` até a raiz da hospedagem e entra em `public_html/`.
  `$publicHtmlDir = realpath(__DIR__ . '/../../../../../public_html') ?: (dirname(__DIR__, 5) . '/public_html');`

- **Movimentação do `index.php` e Limpeza da Pasta `LPDHED2dC7Gjrg2b`**:
  No passo 6:
  - Localizar a pasta de modelo `LPDHED2dC7Gjrg2b` (ou qualquer diretório administrativo anterior em `public_html/`).
  - Se a nova pasta `$adminDir` for diferente de `LPDHED2dC7Gjrg2b`, renomear/mover o diretório `LPDHED2dC7Gjrg2b` para `$targetAdminDir` (`public_html/$adminDir`), migrando seu `index.php` e `.htaccess` para a nova pasta.
  - Caso a pasta destino já exista por algum motivo, mover os arquivos `index.php` e `.htaccess` e remover a pasta `LPDHED2dC7Gjrg2b` para não deixar rastros públicos.
  - Se `index.php` não estiver presente em `$targetAdminDir`, criar um `index.php` seguro configurado para carregar o bootstrap em `../../backend/`.

- **Correção de Bug Sintático (PHPStan)**:
  Corrigir a linha 50 substituindo a concatenação com `+` por `.` (`'adm_' . EnvironmentManager::generateRandomKey(12)`).

---

## Plano de Verificação

### Testes Automatizados e Estáticos
- Executar a análise estática com PHPStan sobre a classe `ProcessInstallationAction`:
  `./vendor/bin/phpstan analyse core/Controller/Actions/Setup/ProcessInstallationAction.php`

### Teste de Execução do Fluxo de Instalação / Movimentação
- Executar script de validação de caminho simulando o comportamento de `ProcessInstallationAction.php` para verificar se:
  1. A pasta `public_html/$adminDir` é criada corretamente em `/var/www/html/agsonhos/public_html/`.
  2. O arquivo `index.php` é movido da pasta `LPDHED2dC7Gjrg2b` para a nova pasta e a pasta modelo `LPDHED2dC7Gjrg2b` é completamente removida.

# Tarefas de Execução: Ajuste no Mecanismo de Implantação

- [x] Corrigir bug de concatenação de string (`+` por `.`) em `ProcessInstallationAction.php`
- [x] Atualizar o caminho `$publicHtmlDir` no passo 6 do `ProcessInstallationAction.php` para a pasta `public_html/` na raiz do host
- [x] Implementar a renomeação/movimentação e limpeza da pasta modelo `LPDHED2dC7Gjrg2b` para a nova pasta criada pelo usuário
- [x] Garantir o fallback de criação do `index.php` e `.htaccess` no diretório de destino
- [x] Validar alterações com PHPStan e teste funcional simulado

# Walkthrough - Ajustes no Mecanismo de Implantação e Deploy

O mecanismo de primeira instalação (Setup Wizard) foi ajustado para que o diretório do Dashboard Administrativo seja criado e configurado diretamente na pasta `public_html/` da raiz da hospedagem (em vez de `backend/public_html/`), e para que o arquivo `index.php` da pasta modelo `LPDHED2dC7Gjrg2b` seja movido para o diretório final do admin definido pelo usuário.

## 🛠️ Alterações Realizadas

### [ProcessInstallationAction.php](file:///var/www/html/agsonhos/backend/core/Controller/Actions/Setup/ProcessInstallationAction.php)

1. **Ajuste na Resolução da `public_html/`**:
   - Atualizado o cálculo do diretório público para subir 5 níveis a partir da action `Setup`:
     ```php
     $publicHtmlDir = realpath(__DIR__ . '/../../../../../public_html') ?: (dirname(__DIR__, 5) . '/public_html');
     ```
   - Isso garante que a pasta do Dashboard seja provisionada em `/var/www/html/agsonhos/public_html/$adminDir`.

2. **Movimentação do `index.php` e Limpeza da Pasta `LPDHED2dC7Gjrg2b`**:
   - O mecanismo busca por diretórios administrativos existentes na `public_html/` (incluindo a pasta modelo `LPDHED2dC7Gjrg2b`).
   - Se o usuário especificar um novo nome para a pasta do Dashboard (ex: `adm_987654321`), o sistema renomeia a pasta `LPDHED2dC7Gjrg2b` para a nova pasta.
   - Caso a pasta destino já exista, os arquivos `index.php` e `.htaccess` são movidos e a pasta `LPDHED2dC7Gjrg2b` é completamente removida para não expor caminhos modelo públicos.
   - Adicionado fallback para geração automática do `index.php` e `.htaccess` caso não estejam presentes na pasta de destino.

3. **Correção de Bug Sintático**:
   - Corrigido o bug na linha 50 onde o operador `+` era utilizado em vez de `.` para concatenação de string.

---

## 🧪 Validações Efetuadas

- **PHPStan (Análise Estática)**:
  `./vendor/bin/phpstan analyse core/Controller/Actions/Setup/ProcessInstallationAction.php`
  *Resultado*: `[OK] No errors`

- **Teste de Resolução de Caminhos**:
  Executado script de teste para validar a resolução do caminho da `public_html/` e verificar a presença da pasta modelo e seu `index.php`.
  *Resultado*: Caminho resolvido com sucesso para `/var/www/html/agsonhos/public_html`.

# Extra:
Criamos o documento de especificação e o script de diagnóstico automatizado para o terminal:

---

### 1. Documento de Requisitos Criado
📄 **[docs/instalation/requirements.md](file:///var/www/html/agsonhos/docs/instalation/requirements.md)**
* **Tabela de Compatibilidade:** Versões mínimas e recomendadas de PHP ($\ge 8.1$), MariaDB/MySQL, Composer e Servidor Web.
* **Matriz de Extensões do PHP:** Detalhamento do propósito de cada uma (10 obrigatórias e 4 recomendadas).
* **Configuração de Servidor Web:** Instruções para Apache (`mod_rewrite`) e Nginx (`try_files`).
* **Permissões de Escrita:** Especificação exata das pastas necessárias (`backend/storage/` e `.env`).

---

### 2. Script de Pré-Checagem Criado (Terminal CLI)
🛠️ **[scripts/check_requirements.sh](file:///var/www/html/agsonhos/scripts/check_requirements.sh)**

Você pode rodar esse script a qualquer momento no terminal para validar se o ambiente do servidor está pronto antes de abrir o navegador:

```bash
./scripts/check_requirements.sh
```

#### Resultado do teste executado no seu ambiente agora:
* ✅ **PHP:** v8.4.24 detectado e compatível (`/usr/bin/php`).
* ✅ **Extensões detectadas:** `pdo`, `pdo_mysql`, `mbstring`, `gd`, `xml`, `curl`, `openssl`, `fileinfo`, `json`.
* ✅ **Composer:** v2.10.2 instalado e pasta `backend/vendor/autoload.php` gerada com sucesso!
* ✅ **Permissões:** Diretório `storage/` e permissão para o `.env` válidas.
* ⚠️ **Ajuste pendente:** Instalar a extensão `php-zip` (e o MariaDB quando for configurar o banco):
  ```bash
  sudo apt install php-zip php-intl php-bcmath mariadb-server
  ```

