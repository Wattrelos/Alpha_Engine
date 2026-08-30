# Pastas úteis para instalação
Para o funcionamento da **Alpha Engine (AgSonhos)** em um ambiente de produção ou nova instalação, a arquitetura é estruturada de forma desacoplada entre **`backend/`** (lógica de negócio protegida fora do acesso web) e **`public_html/`** (DocumentRoot acessível via navegador).

Dentro dessa estrutura, as **pastas e subdiretórios estritamente essenciais** são:

---

### 1. Estrutura Essencial dentro de `backend/`

| Diretório | Função / Por que é obrigatório? |
| :--- | :--- |
| **`backend/core/`** | **O Motor da Aplicação**: Contém todas as Entidades de Domínio, Repositórios, Data Mappers, Services, Controllers (Actions), Middlewares de Segurança e ViewRenderer. |
| **`backend/Containers/`** | **Injeção de Dependências**: Contém o `AppContainer.php` (PSR-11) e `AppBootstrap.php` que orquestram a inicialização de todos os serviços. |
| **`backend/Config/`** | **Roteamento**: Contém o `Routes.php` com a definição de todas as rotas da loja e do painel administrativo. |
| **`backend/resources/`** | **Templates e Banco**: Contém `resources/views/` (todos os templates Twig da loja, admin e setup) e `resources/schema/` (o arquivo `install.sql` usado no provisionamento). |
| **`backend/Locales/`** | **Internacionalização**: Contém os arquivos JSON de tradução (`pt-br/`, `en-gb/`, `fr-fr/`). Sem eles, os textos da loja e do admin não carregam. |
| **`backend/storage/`** *(Gravação)* | **Armazenamento Dinâmico**: **Exige permissão de escrita (`chmod -R 775` ou `777`)**. Subpastas vitais:<br>• `storage/cache/` (cache do Twig e consultas)<br>• `storage/logs/` (logs de erro e auditoria)<br>• `storage/session/` (sessões de usuários)<br>• `storage/upload/` e `storage/download/` |
| **`backend/vendor/`** | **Dependências do Composer**: Autoload PSR-4 e bibliotecas externas de produção (`slim`, `twig`, `predis`, etc.). Gerada via `composer install --no-dev`. |

---

### 2. Estrutura Essencial dentro de `public_html/`

| Diretório / Arquivo | Função / Por que é obrigatório? |
| :--- | :--- |
| **`public_html/index.php`** | Ponto de entrada (*Front Controller*) de toda a loja pública e do instalador `/setup`. |
| **`public_html/{ADMIN_DIR}/`** *(ex: `adm_m7WbbFOqfoRo/`)* | Pasta com nome ofuscado contendo o `index.php` do **Painel Administrativo**. |
| **`public_html/css/`** | Folhas de estilo compiladas para loja e painel admin. |
| **`public_html/js/`** | Scripts JavaScript e comportamentos dinâmicos de interface. |
| **`public_html/image/`** (ou `img/`) | Catálogo de imagens de produtos, banners e logos. *(Também precisa de permissão de escrita para upload de novas fotos de produtos)*. |
| **`public_html/fonts/`** | Fontes e ícones (FontAwesome, etc.). |
| **`public_html/.htaccess`** | Regras de reescrita de URL (*URL Rewrite*) para roteamento amigável do Apache/LiteSpeed. |

---

### 3. Pasta Auxiliar Útil durante a Instalação

* **`scripts/`**: Contém o script [`scripts/check_requirements.sh`](file:///var/www/html/agsonhos/scripts/check_requirements.sh), que você pode rodar no terminal para validar automaticamente se a versão do PHP, extensões do sistema e permissões de escrita das pastas estão 100% corretas antes de rodar o assistente visual.

---

### 🚫 O que **NÃO** precisa ir para o servidor de produção:

Estas pastas são exclusivas para desenvolvimento, testes e documentação:
* `tests/` (Testes unitários PHPUnit)
* `features/` (Testes BDD Behat)
* `e2e/`, `playwright-report/`, `node_modules/` (Testes ponta a ponta Playwright)
* `docs/` e `tutorials/` (Documentação e diagramas de arquitetura)
* `.git/` e `.github/`