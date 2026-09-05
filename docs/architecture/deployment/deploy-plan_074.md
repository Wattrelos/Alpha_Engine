# DP-74: Reestruturação da Arquitetura: Isolamento da Pasta `backend/`

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-08-12 18:15:07
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/74

## Descrição

# Plano de Reestruturação da Arquitetura: Isolamento da Pasta `backend/`

Este plano detalha a reorganização da estrutura do projeto **AgSonhos (Alpha Engine)** para isolar o código-fonte, dependências, configurações e recursos na diretório `backend/`, mantendo a raiz pública `public_html/` isolada como o único ponto de entrada HTTP exposto ao servidor web (Cenário 1 de hospedagem para Hostinger / cPanel).

## User Review Required

> [!IMPORTANT]
> **Mudança Estrutural Relevante**
> - Todos os módulos do sistema (ex: `core/`, `Containers/`, `Config/`, `resources/`, `storage/`, `vendor/`, `.env`, `config.php`) serão movidos para dentro da pasta `backend/`.
> - A pasta `public_html/` permanecerá na raiz do repositório.
> - O arquivo [public_html/index.php](file:///var/www/html/agsonhos/public_html/index.php) e o painel administrativo em [public_html/LPDHED2dC7Gjrg2b/index.php](file:///var/www/html/agsonhos/public_html/LPDHED2dC7Gjrg2b/index.php) serão atualizados para buscar os scripts do autoload, views, rotas e `.env` a partir de `../backend/` e `../../backend/`.

## Estrutura Alvo do Repositório

```text
agsonhos/ (raiz do repositório)
├── backend/                             [NOVA PASTA]
│   ├── BusinessKnowledgeBase/
│   ├── Config/
│   ├── Containers/
│   ├── Locales/
│   ├── LogsPersonalizados/
│   ├── changelog/
│   ├── core/
│   ├── dist/
│   ├── docs/
│   ├── resources/
│   ├── scratch/
│   ├── scripts/
│   ├── storage/
│   ├── tests/
│   ├── vendor/
│   ├── .env
│   ├── .env.example
│   ├── AUTHORS.md
│   ├── LICENSE.md
│   ├── README.md
│   ├── clean_twig_cache.php
│   ├── composer.json
│   ├── composer.lock
│   ├── config.php
│   ├── error.html
│   ├── php.ini
│   ├── phpstan.neon
│   ├── phpstan-baseline.neon
│   └── phpunit.xml
├── public_html/                         [PERMANECE NA RAIZ]
│   ├── index.php
│   ├── .htaccess
│   ├── LPDHED2dC7Gjrg2b/
│   │   └── index.php
│   ├── css/
│   ├── js/
│   ├── image/
│   └── favicon.ico
├── .git/
├── .gitignore
├── .agents/
└── .gemini/
```

---

## Open Questions

Nenhuma pergunta pendente neste momento. Caso deseje incluir ou manter algum arquivo específico fora do `backend/`, informe na aprovação.

---

## Proposed Changes

### 1. Reorganização dos Diretórios e Arquivos

#### [NEW] [backend/](file:///var/www/html/agsonhos/backend)
- Criar diretório `backend/` e mover todas as pastas e arquivos de runtime, dependências e configurações para o seu interior.

---

### 2. Atualização dos Entry Points Públicos

#### [MODIFY] [public_html/index.php](file:///var/www/html/agsonhos/public_html/index.php)
- Atualizar caminhos relativos de `__DIR__ . '/../'` para `__DIR__ . '/../backend/'`:
  - Autoload do Composer: `__DIR__ . '/../backend/vendor/autoload.php'`
  - Arquivo `.env`: `__DIR__ . '/../backend/'` e `__DIR__ . '/../backend/.env'`
  - Arquivo de configuração: `__DIR__ . '/../backend/config.php'`
  - Cache e views Twig: `__DIR__ . '/../backend/storage/cache/...'` e `__DIR__ . '/../backend/resources/views'`
  - Rotas Slim: `__DIR__ . '/../backend/Config/Routes.php'`

#### [MODIFY] [public_html/LPDHED2dC7Gjrg2b/index.php](file:///var/www/html/agsonhos/public_html/LPDHED2dC7Gjrg2b/index.php)
- Atualizar caminhos relativos de `__DIR__ . '/../../'` para `__DIR__ . '/../../backend/'`:
  - Autoload do Composer: `__DIR__ . '/../../backend/vendor/autoload.php'`
  - Arquivo `.env`: `__DIR__ . '/../../backend/'`
  - Arquivo de configuração: `__DIR__ . '/../../backend/config.php'`
  - Cache e views Twig: `__DIR__ . '/../../backend/storage/cache/...'` e `__DIR__ . '/../../backend/resources/views'`
  - Rotas Slim: `__DIR__ . '/../../backend/Config/Routes.php'`

---

### 3. Ajustes de Configuração Interna

#### [MODIFY] [backend/config.php](file:///var/www/html/agsonhos/config.php)
- Atualizar a constante `DIR_IMAGE` para buscar `public_html` um nível acima da pasta `backend/`:
  - `define('DIR_IMAGE', realpath(__DIR__ . '/../public_html') . '/');`

#### [MODIFY] [backend/scripts/build-release.php](file:///var/www/html/agsonhos/scripts/build-release.php)
- Ajustar script de geração de release para reconhecer a nova estrutura em `backend/`.

---

## Verification Plan

### Automated Tests
1. Executar os testes PHPUnit na pasta `backend/`:
   ```bash
   cd /var/www/html/agsonhos/backend && vendor/bin/phpunit
   ```
2. Executar análise estática PHPStan:
   ```bash
   cd /var/www/html/agsonhos/backend && vendor/bin/phpstan analyse --no-progress
   ```

### Manual Verification
1. Testar via CLI se a aplicação inicia o bootstrap do Slim Framework sem erros de arquivo não encontrado (`file_exists` e `require_once`).
2. Verificar se as diretivas de upload, Twig template rendering e carregamento de variáveis do `.env` permanecem operacionais.

# Lista de Tarefas - Migração para `backend/`

- [x] Criar estrutura e mover arquivos para `backend/`
  - [x] Criar diretório `/var/www/html/agsonhos/backend`
  - [x] Mover diretórios e arquivos de backend para `/var/www/html/agsonhos/backend/`
- [x] Atualizar pontos de entrada em `public_html/`
  - [x] Atualizar `public_html/index.php` para referenciar `../backend/`
  - [x] Atualizar `public_html/LPDHED2dC7Gjrg2b/index.php` para referenciar `../../backend/`
- [x] Ajustar configurações e scripts auxiliares
  - [x] Atualizar `backend/config.php` (`DIR_IMAGE`)
  - [x] Atualizar `backend/scripts/build-release.php`
- [x] Executar Verificação e Testes
  - [x] Rodar PHPUnit em `backend/` (60 testes validados)
  - [x] Rodar PHPStan em `backend/` ([OK] No errors)
  - [x] Testar requisições HTTP e roteamento do Slim

# Walkthrough: Isolamento da Arquitetura na Pasta `backend/`

Concluímos com sucesso a reorganização da estrutura do projeto **AgSonhos (Alpha Engine)**. O código-fonte, módulos, dependências e arquivos de configuração foram isolados dentro de `backend/`, mantendo a pasta pública `public_html/` limpa na raiz do repositório.

## Resumo das Alterações Realizadas

### 1. Reorganização dos Diretórios e Arquivos
- **Criado:** Diretório [backend/](file:///var/www/html/agsonhos/backend).
- **Movidos para `backend/`:** `core/`, `Containers/`, `Config/`, `resources/`, `storage/`, `vendor/`, `tests/`, `scripts/`, `dist/`, `docs/`, `Locales/`, `BusinessKnowledgeBase/`, `LogsPersonalizados/`, `changelog/`, `scratch/`, `.env`, `.env.example`, `config.php`, `composer.json`, `composer.lock`, `clean_twig_cache.php`, `error.html`, `php.ini`, `phpstan.neon`, `phpstan-baseline.neon`, `phpunit.xml`, `README.md`, `LICENSE.md`, `AUTHORS.md`, `.phpunit.cache` e `.reports`.
- **Mantidos na raiz:** `public_html/`, `backend/`, `.git/`, `.gitignore`, `.agents/`, `.antigravityrc`, `.aiexclude` e `.gemini/`.

### 2. Atualização dos Pontos de Entrada Públicos
- **[public_html/index.php](file:///var/www/html/agsonhos/public_html/index.php):**
  - Autoload do Composer atualizado para `__DIR__ . '/../backend/vendor/autoload.php'`.
  - Leitura do `.env` atualizada para `__DIR__ . '/../backend/'` e `__DIR__ . '/../backend/.env'`.
  - Inclusão do arquivo de configuração atualizada para `__DIR__ . '/../backend/config.php'`.
  - Diretórios de cache e views Twig direcionados para `__DIR__ . '/../backend/storage/cache/...'` e `__DIR__ . '/../backend/resources/views'`.
  - Carregamento de rotas Slim direcionado para `__DIR__ . '/../backend/Config/Routes.php'`.
- **[public_html/LPDHED2dC7Gjrg2b/index.php](file:///var/www/html/agsonhos/public_html/LPDHED2dC7Gjrg2b/index.php):**
  - Autoload, `.env`, `config.php`, Twig e rotas atualizados com o prefixo `../../backend/`.

### 3. Ajustes de Configuração Interna
- **[backend/config.php](file:///var/www/html/agsonhos/backend/config.php):**
  - Atualizada a constante `DIR_IMAGE` para mapear dinamicamente a pasta pública no nível acima:
    `define('DIR_IMAGE', (realpath(DIR_ROOT . '../public_html') ?: (DIR_ROOT . '../public_html')) . '/');`
- **[backend/core/Support/Language.php](file:///var/www/html/agsonhos/backend/core/Support/Language.php):**
  - Removido caminho absoluto hardcoded e atualizado cálculo dinâmico da pasta `Locales/`.
- **[backend/scripts/build-release.php](file:///var/www/html/agsonhos/backend/scripts/build-release.php):**
  - Atualizada a rotina de empacotamento para obter `public_html` a partir da raiz externa.

---

## Verificação e Resultados

### Automated Tests
1. **PHPUnit:**
   - Comando: `cd backend && vendor/bin/phpunit`
   - Resultado: **OK (60 testes executados, 163 asserções, 0 falhas)**.
2. **PHPStan:**
   - Comando: `cd backend && composer stan`
   - Resultado: **[OK] No errors**.

### Bootstrap & CLI Runtime Test
- Execução direta dos pontos de entrada `public_html/index.php` e `public_html/LPDHED2dC7Gjrg2b/index.php` confirmou carregamento limpo da Alpha Engine e do Slim Framework.

