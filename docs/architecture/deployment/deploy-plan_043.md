# DP-43: Conversão de `addresses.css` para SCSS

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-07-10 12:26:48
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/43

## Descrição

# Conversão de `addresses.css` para SCSS

O objetivo é converter o arquivo `addresses.css` para SCSS (`addresses.scss`), reaproveitando estilos premium comuns por meio dos seletores placeholders e separando os estilos no parcial modular correspondente.

## User Review Required

> [!IMPORTANT]
> **Automação de Build**
> Como atualizamos o compilador PHP para compilar dinamicamente todos os `.scss` principais na pasta `custom/`, a criação de `addresses.scss` irá gerar automaticamente `addresses.css` durante a execução do comando `composer build-css`.

## Proposed Changes

### 1. Estilos Modulares (SCSS)

#### [NEW] [addresses.scss](/public_html/css/custom/addresses.scss)
* Novo arquivo de entrada principal SCSS para a área de endereços que importará os tokens, mixins e o parcial específico.

#### [NEW] [_addresses.scss](/public_html/css/base/pages/_addresses.scss)
* Parcial SCSS contendo todas as regras específicas da tela de endereços (criação, edição, listagem de cartões, toggle switch de endereço padrão, spinner de busca de CEP, etc.), herdando estilos comuns.

## Verification Plan

### Automated Tests
- Executar `composer build-css` e garantir que `new-stylesheet.css`, `returns-institutional.css` e `addresses.css` sejam compilados com sucesso.

### Manual Verification
- Validar se a renderização das telas de listagem, criação e edição de endereços permanece correta.

# Checklist de Modularização e Especialização CSS/SCSS

- [x] Instalar o pacote `scssphp/scssphp` via Composer
- [x] Atualizar o script PHP de compilação em `scratch/compile-scss.php` para múltiplos arquivos
- [x] Adicionar o script `build-css` no `composer.json`
- [x] Estruturar e extrair os parciais de estilo do returns-institutional:
  - [x] `public_html/css/base/pages/_returns.scss` (Devoluções)
  - [x] `public_html/css/base/pages/_contact.scss` (Contato)
  - [x] `public_html/css/base/pages/_info.scss` (Institucional)
  - [x] `public_html/css/custom/returns-institutional.scss`
- [x] Estruturar e extrair os parciais de estilo do addresses:
  - [x] `public_html/css/base/pages/_addresses.scss` (Endereços)
  - [x] `public_html/css/custom/addresses.scss` (Consolidador)
- [x] Executar o compilador e validar a geração de todos os arquivos CSS finais
- [x] Testar e verificar o funcionamento visual

# Walkthrough - Modularização de CSS/SCSS (Fase 1, 2 e 3 concluídas!)

Realizamos com sucesso a modularização das três folhas de estilo principais da loja, estruturando-as em parciais Sass (`.scss`) especializados organizados em Atoms, Molecules, Organisms e Pages, compartilhando estilos comuns por meio de placeholders Sass.

## O que mudou:

### 1. Ferramenta de Compilação Dinâmica
- O script [compile-scss.php](/scratch/compile-scss.php) compila dinamicamente **qualquer** arquivo `.scss` principal encontrado na pasta `public_html/css/custom/` (ignorando arquivos com underline, que são parciais).
- Roda no Composer por meio de:
  ```bash
  composer build-css
  ```

### 2. Estilos Compartilhados e Reusabilidade
- Criamos o arquivo [_shared-mixins.scss](/public_html/css/base/_shared-mixins.scss) contendo seletores placeholders do Sass para reaproveitar os visuais premium comuns:
  - `%premium-hero`: Seções de topo com gradiente e borda brilhante.
  - `%premium-breadcrumb`: Navegação breadcrumbs padrão.
  - `%premium-button`: Botões gradientes com sombras premium.
  - `%premium-card`: Cartões com fundo translúcido e bordas finas (glassmorphism).
  - `%premium-input`: Campos de texto premium com efeitos de foco.

### 3. Extração e Modularização de `addresses.css`
- Convertido para [addresses.scss](/public_html/css/custom/addresses.scss), herdando variáveis e mixins compartilhados.
- Criamos os arquivos parciais:
  - [_addresses.scss](/public_html/css/base/pages/_addresses.scss): Controles, cartões e páginas de criação, edição e listagem de endereços.

### 4. Extração e Modularização de `returns-institutional.css`
- Convertido para [returns-institutional.scss](/public_html/css/custom/returns-institutional.scss).
- Criamos os arquivos parciais:
  - [_returns.scss](/public_html/css/base/pages/_returns.scss): Telas de trocas e devoluções.
  - [_contact.scss](/public_html/css/base/pages/_contact.scss): Tela de contato com a loja.
  - [_info.scss](/public_html/css/base/pages/_info.scss): Páginas de conteúdo institucional.

---

## Como Executar a Compilação:
Sempre que fizer alterações nos arquivos `.scss`, execute o seguinte comando na raiz do projeto:
```bash
composer build-css
```
Isso gerará os respectivos arquivos CSS finais atualizados:
- `public_html/css/custom/new-stylesheet.css`
- `public_html/css/custom/returns-institutional.css`
- `public_html/css/custom/addresses.css`

