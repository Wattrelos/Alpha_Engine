# DP-41: Modularização e Especialização de CSS/SCSS

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-07-10 12:01:53
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/41

## Descrição

# Modularização e Especialização de CSS/SCSS

O objetivo é dividir a folha de estilo gigante `new-stylesheet.css` (com mais de 4500 linhas) em múltiplos arquivos Sass (`.scss`) especializados (seguindo a metodologia de Design Atômico: Atoms, Molecules, Organisms, Pages).

Para viabilizar a compilação no ambiente atual (sem Node.js/npm), utilizaremos o **`scssphp/scssphp`**, um compilador Sass escrito inteiramente em PHP e instalado via Composer.

## User Review Required

> [!IMPORTANT]
> **Estratégia de Compilação com PHP**
> 1. Instalaremos o pacote `scssphp/scssphp` via Composer.
> 2. Criaremos um script de compilação em `/var/www/html/agsonhos/scratch/compile-scss.php`.
> 3. Adicionaremos um comando personalizado ao `composer.json` (`composer build-css`) para rodar essa compilação sempre que necessário.
> 4. O arquivo principal `public_html/css/custom/new-stylesheet.css` passará a ser gerado automaticamente a partir do arquivo SCSS de entrada.

## Proposed Changes

Propomos modularizar o `new-stylesheet.css` na seguinte estrutura dentro de `public_html/css/`:

```
public_html/css/
├── base/
│   ├── _variables.css
│   ├── atoms/
│   │   ├── _buttons.scss
│   │   └── _inputs.scss
│   ├── molecules/
│   │   └── _search-bar.scss
│   ├── organisms/
│   │   └── _header.scss
│   └── pages/
│       ├── _login.scss
│       ├── _register.scss
│       ├── _cart.scss
│       ├── _category.scss
│       └── _product.scss
└── custom/
    ├── new-stylesheet.scss (arquivo principal que importa todos os parciais)
    └── new-stylesheet.css (arquivo final gerado/compilado)
```

### 1. Dependências e Scripts de Compilação

#### [MODIFY] [composer.json](file:///var/www/html/agsonhos/composer.json)
* Adicionar dependência de `scssphp/scssphp`.
* Adicionar script `"build-css": "php scratch/compile-scss.php"` em `"scripts"`.

#### [NEW] [compile-scss.php](file:///var/www/html/agsonhos/scratch/compile-scss.php)
* Script PHP que lê `public_html/css/custom/new-stylesheet.scss`, compila usando `ScssPhp\ScssPhp\Compiler` e salva em `public_html/css/custom/new-stylesheet.css`.

### 2. Arquivos de Estilos (SCSS)

#### [NEW] [new-stylesheet.scss](file:///var/www/html/agsonhos/public_html/css/custom/new-stylesheet.scss)
* Consolidação de todas as importações SCSS.

#### [NEW] [_header.scss](file:///var/www/html/agsonhos/public_html/css/base/organisms/_header.scss)
* Extração dos estilos de cabeçalho do `new-stylesheet.css`.

#### [NEW] [_search-bar.scss](file:///var/www/html/agsonhos/public_html/css/base/molecules/_search-bar.scss)
* Extração dos estilos da barra de pesquisa.

#### [NEW] [_login.scss](file:///var/www/html/agsonhos/public_html/css/base/pages/_login.scss)
* Extração dos estilos de login (Login Hero, Login Grid, Login Card).

#### [NEW] [_register.scss](file:///var/www/html/agsonhos/public_html/css/base/pages/_register.scss)
* Extração dos estilos da página de cadastro.

#### [NEW] [_cart.scss](file:///var/www/html/agsonhos/public_html/css/base/pages/_cart.scss)
* Extração dos estilos do carrinho de compras.

#### [NEW] [_category.scss](file:///var/www/html/agsonhos/public_html/css/base/pages/_category.scss)
* Extração dos estilos da página de categorias e da grade de produtos.

#### [NEW] [_product.scss](file:///var/www/html/agsonhos/public_html/css/base/pages/_product.scss)
* Extração dos estilos da página interna de produto.

## Verification Plan

### Automated Tests
- Executar `composer build-css` e verificar se o compilador gera com sucesso o arquivo `new-stylesheet.css` sem erros de sintaxe ou importação.

### Manual Verification
- Validar se a renderização de todas as páginas da loja (Login, Cadastro, Carrinho, Categoria, Produto) permanece idêntica após a aplicação dos estilos compilados.

# Checklist de Modularização e Especialização CSS/SCSS

- [x] Instalar o pacote `scssphp/scssphp` via Composer
- [x] Criar o script PHP de compilação em `scratch/compile-scss.php`
- [x] Adicionar o script `build-css` no `composer.json`
- [x] Estruturar e extrair os parciais de estilo:
  - [x] `public_html/css/base/organisms/_header.scss` (Cabeçalho)
  - [x] `public_html/css/base/molecules/_search-bar.scss` (Barra de Pesquisa)
  - [x] `public_html/css/base/pages/_login.scss` (Login e Registro no login)
  - [x] `public_html/css/base/pages/_register.scss` (Página de Registro principal)
  - [x] `public_html/css/base/pages/_cart.scss` (Carrinho de compras)
  - [x] `public_html/css/base/pages/_category.scss` (Categorias e Grade de Produtos)
  - [x] `public_html/css/base/pages/_product.scss` (Interna de Produtos)
- [x] Criar o arquivo de consolidação `public_html/css/custom/new-stylesheet.scss`
- [x] Executar o compilador e validar a geração do CSS final `new-stylesheet.css`
- [x] Testar e verificar o funcionamento visual

# Walkthrough - Modularização de CSS/SCSS concluída!

Realizamos com sucesso a modularização do arquivo CSS principal da loja, estruturando-o em parciais Sass (`.scss`) especializados organizados em Atoms, Molecules, Organisms e Pages. Além disso, integramos uma ferramenta de compilação SCSS via PHP.

## O que mudou:

1. **Instalação do Compilador PHP Sass (`scssphp/scssphp`):**
   - Adicionado como dependência no `composer.json` e instalado com sucesso.

2. **Criação do Script de Build (`scratch/compile-scss.php`):**
   - Script PHP criado para compilar o arquivo principal e escrever o arquivo final `new-stylesheet.css`.
   - Vinculado ao Composer através do comando `composer build-css`.

3. **Extração das Folhas de Estilo Modulares:**
   - [_utilities.scss](file:///var/www/html/agsonhos/public_html/css/base/_utilities.scss): Utilitários e classes de grid base.
   - [_breadcrumbs.scss](file:///var/www/html/agsonhos/public_html/css/base/molecules/_breadcrumbs.scss): Estilos para trilhas de navegação.
   - [_forms-premium.scss](file:///var/www/html/agsonhos/public_html/css/base/molecules/_forms-premium.scss): Controles de formulário premium reutilizáveis.
   - [_login.scss](file:///var/www/html/agsonhos/public_html/css/base/pages/_login.scss): Página de login e seus cartões/campos.
   - [_register.scss](file:///var/www/html/agsonhos/public_html/css/base/pages/_register.scss): Página de registro principal.
   - [_cart.scss](file:///var/www/html/agsonhos/public_html/css/base/pages/_cart.scss): Estilos do carrinho de compras.
   - [_category.scss](file:///var/www/html/agsonhos/public_html/css/base/pages/_category.scss): Visual de páginas de categorias, filtros e cards de produtos.
   - [_product.scss](file:///var/www/html/agsonhos/public_html/css/base/pages/_product.scss): Interna do produto (galeria, abas, frete, etc.).
   - [_checkout.scss](file:///var/www/html/agsonhos/public_html/css/base/pages/_checkout.scss): Visual do checkout de compra e opções de pagamento.
   - [_sitemap.scss](file:///var/www/html/agsonhos/public_html/css/base/pages/_sitemap.scss): Estilos do mapa do site.
   - [_home.scss](file:///var/www/html/agsonhos/public_html/css/base/pages/_home.scss): Visual da página inicial.
   - [_wishlist.scss](file:///var/www/html/agsonhos/public_html/css/base/pages/_wishlist.scss): Lista de desejos.
   - [_account.scss](file:///var/www/html/agsonhos/public_html/css/base/pages/_account.scss): Painel e páginas internas da conta do cliente.
   - [_orders.scss](file:///var/www/html/agsonhos/public_html/css/base/pages/_orders.scss): Histórico e detalhes de pedidos.

4. **Entrada Principal:**
   - [new-stylesheet.scss](file:///var/www/html/agsonhos/public_html/css/custom/new-stylesheet.scss): Arquivo consolidador que importa todos os parciais acima.

## Como Executar a Compilação:
Sempre que fizer alterações nos arquivos `.scss`, basta rodar o comando abaixo no terminal da raiz do projeto:
```bash
composer build-css
```
Isso gerará automaticamente a versão atualizada de `public_html/css/custom/new-stylesheet.css`.

