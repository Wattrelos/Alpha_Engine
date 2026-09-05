# DP-42: Conversão de `returns-institutional.css` para SCSS e Melhorias no Compilador

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-07-10 12:22:31
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/42

## Descrição

# Conversão de `returns-institutional.css` para SCSS e Melhorias no Compilador

O objetivo é converter o arquivo `returns-institutional.css` para SCSS (`returns-institutional.scss`), permitindo reaproveitar estilos (como variáveis, botões e breadcrumbs), e atualizar o compilador PHP para compilar dinamicamente todos os arquivos principais da pasta `custom/`.

## User Review Required

> [!IMPORTANT]
> **Alterações no Script de Compilação**
> Atualizaremos o `scratch/compile-scss.php` para procurar todos os arquivos `.scss` na pasta `public_html/css/custom/` e compilá-los com o mesmo nome `.css`. Dessa forma, tanto o `new-stylesheet.css` quanto o `returns-institutional.css` serão gerados automaticamente.

## Proposed Changes

### 1. Compilador Dinâmico

#### [MODIFY] [compile-scss.php](file:///var/www/html/agsonhos/scratch/compile-scss.php)
* Modificar o script para ler todos os arquivos `.scss` que não iniciem com underscore (`_`) dentro de `public_html/css/custom/` e gerar os respectivos `.css` correspondentes.

### 2. Estilos Modulares (SCSS)

#### [NEW] [returns-institutional.scss](file:///var/www/html/agsonhos/public_html/css/custom/returns-institutional.scss)
* Novo arquivo de entrada SCSS que importará os tokens/variáveis e conterá as declarações do módulo `returns-institutional`, utilizando heranças (como os estilos de botões e breadcrumbs já definidos no sistema) para evitar repetição de código.

#### [NEW] [_returns.scss](file:///var/www/html/agsonhos/public_html/css/base/pages/_returns.scss)
* Extração dos estilos específicos de devolução (formulário, detalhes, histórico).

#### [NEW] [_contact.scss](file:///var/www/html/agsonhos/public_html/css/base/pages/_contact.scss)
* Extração dos estilos específicos da página de contato.

#### [NEW] [_info.scss](file:///var/www/html/agsonhos/public_html/css/base/pages/_info.scss)
* Extração dos estilos da página institucional.

## Verification Plan

### Automated Tests
- Executar `composer build-css` e garantir que ambos os arquivos `new-stylesheet.css` e `returns-institutional.css` sejam compilados com sucesso.

### Manual Verification
- Validar se a renderização das páginas institucionais, de contato e de devoluções permanece correta.

# Checklist de Modularização e Especialização CSS/SCSS

- [x] Instalar o pacote `scssphp/scssphp` via Composer
- [x] Atualizar o script PHP de compilação em `scratch/compile-scss.php` para múltiplos arquivos
- [x] Adicionar o script `build-css` no `composer.json`
- [x] Estruturar e extrair os novos parciais de estilo:
  - [x] `public_html/css/base/pages/_returns.scss` (Devoluções)
  - [x] `public_html/css/base/pages/_contact.scss` (Contato)
  - [x] `public_html/css/base/pages/_info.scss` (Institucional)
- [x] Criar o arquivo de consolidação `public_html/css/custom/returns-institutional.scss`
- [x] Executar o compilador e validar a geração de ambos os arquivos CSS finais
- [x] Testar e verificar o funcionamento visual

# Conversão de `returns-institutional.css` para SCSS e Melhorias no Compilador

O objetivo é converter o arquivo `returns-institutional.css` para SCSS (`returns-institutional.scss`), permitindo reaproveitar estilos (como variáveis, botões e breadcrumbs), e atualizar o compilador PHP para compilar dinamicamente todos os arquivos principais da pasta `custom/`.

## User Review Required

> [!IMPORTANT]
> **Alterações no Script de Compilação**
> Atualizaremos o `scratch/compile-scss.php` para procurar todos os arquivos `.scss` na pasta `public_html/css/custom/` e compilá-los com o mesmo nome `.css`. Dessa forma, tanto o `new-stylesheet.css` quanto o `returns-institutional.css` serão gerados automaticamente.

## Proposed Changes

### 1. Compilador Dinâmico

#### [MODIFY] [compile-scss.php](file:///var/www/html/agsonhos/scratch/compile-scss.php)
* Modificar o script para ler todos os arquivos `.scss` que não iniciem com underscore (`_`) dentro de `public_html/css/custom/` e gerar os respectivos `.css` correspondentes.

### 2. Estilos Modulares (SCSS)

#### [NEW] [returns-institutional.scss](file:///var/www/html/agsonhos/public_html/css/custom/returns-institutional.scss)
* Novo arquivo de entrada SCSS que importará os tokens/variáveis e conterá as declarações do módulo `returns-institutional`, utilizando heranças (como os estilos de botões e breadcrumbs já definidos no sistema) para evitar repetição de código.

#### [NEW] [_returns.scss](file:///var/www/html/agsonhos/public_html/css/base/pages/_returns.scss)
* Extração dos estilos específicos de devolução (formulário, detalhes, histórico).

#### [NEW] [_contact.scss](file:///var/www/html/agsonhos/public_html/css/base/pages/_contact.scss)
* Extração dos estilos específicos da página de contato.

#### [NEW] [_info.scss](file:///var/www/html/agsonhos/public_html/css/base/pages/_info.scss)
* Extração dos estilos da página institucional.

## Verification Plan

### Automated Tests
- Executar `composer build-css` e garantir que ambos os arquivos `new-stylesheet.css` e `returns-institutional.css` sejam compilados com sucesso.

### Manual Verification
- Validar se a renderização das páginas institucionais, de contato e de devoluções permanece correta.

