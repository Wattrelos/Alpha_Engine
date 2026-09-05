# DP-44: Conversão de `orders.css` para SCSS

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-07-10 12:30:48
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/44

## Descrição

# Conversão de `orders.css` para SCSS

O objetivo é mudar o arquivo `orders.css` para SCSS (`orders.scss`), reaproveitando estilos premium comuns por meio dos seletores placeholders e separando os estilos no parcial modular correspondente.

## User Review Required

> [!IMPORTANT]
> **Automação de Build**
> Como configuramos o compilador dinâmico na pasta `custom/`, a criação de `orders.scss` gerará automaticamente o arquivo final `orders.css` compilado toda vez que o comando `composer build-css` for executado.

## Proposed Changes

### 1. Estilos Modulares (SCSS)

#### [NEW] [orders.scss](file:///var/www/html/agsonhos/public_html/css/custom/orders.scss)
* Novo arquivo de entrada principal SCSS para a área de pedidos que importará os tokens, mixins e o parcial específico.

#### [NEW] [_orders-page.scss](file:///var/www/html/agsonhos/public_html/css/base/pages/_orders-page.scss)
* Parcial SCSS contendo todas as regras específicas da tela de pedidos (histórico, detalhes, tabela de itens, timeline de status, badges, estatísticas rápidas, etc.), reaproveitando estilos e media-queries.

## Verification Plan

### Automated Tests
- Executar `composer build-css` e garantir que todos os arquivos CSS principais da pasta `custom/` sejam compilados com sucesso.

### Manual Verification
- Validar se a renderização das páginas de listagem e detalhes do pedido permanece correta e responsiva.

# Checklist de Modularização e Especialização CSS/SCSS

- [x] Instalar o pacote `scssphp/scssphp` via Composer
- [x] Atualizar o script PHP de compilação em `scratch/compile-scss.php` para múltiplos arquivos
- [x] Adicionar o script `build-css` no `composer.json`
- [x] Estruturar e extrair os parciais de estilo do returns-institutional
- [x] Estruturar e extrair os parciais de estilo do addresses
- [x] Estruturar e extrair os parciais de estilo do orders:
  - [x] `public_html/css/base/pages/_orders-page.scss` (Pedidos)
  - [x] `public_html/css/custom/orders.scss` (Consolidador)
- [x] Executar o compilador e validar a geração de todos os arquivos CSS finais
- [x] Testar e verificar o funcionamento visual

# Walkthrough - Modularização de CSS/SCSS (Fase 1, 2, 3 e 4 concluídas!)

Realizamos com sucesso a modularização das quatro folhas de estilo principais da loja, estruturando-as em parciais Sass (`.scss`) especializados organizados em Atoms, Molecules, Organisms e Pages, compartilhando estilos comuns por meio de placeholders Sass.

## O que mudou:

### 1. Ferramenta de Compilação Dinâmica
- O script [compile-scss.php](file:///var/www/html/agsonhos/scratch/compile-scss.php) compila dinamicamente **qualquer** arquivo `.scss` principal encontrado na pasta `public_html/css/custom/` (ignorando arquivos com underline, que são parciais).
- Roda no Composer por meio de:
  ```bash
  composer build-css
  ```

### 2. Estilos Compartilhados e Reusabilidade
- Criamos o arquivo [_shared-mixins.scss](file:///var/www/html/agsonhos/public_html/css/base/_shared-mixins.scss) contendo seletores placeholders do Sass para reaproveitar os visuais premium comuns:
  - `%premium-hero`: Seções de topo com gradiente e borda brilhante.
  - `%premium-breadcrumb`: Navegação breadcrumbs padrão.
  - `%premium-button`: Botões gradientes com sombras premium.
  - `%premium-card`: Cartões com fundo translúcido e bordas finas (glassmorphism).
  - `%premium-input`: Campos de texto premium com efeitos de foco.

### 3. Extração e Modularização de `orders.css`
- Convertido para [orders.scss](file:///var/www/html/agsonhos/public_html/css/custom/orders.scss), herdando variáveis e mixins compartilhados.
- Criamos os arquivos parciais:
  - [_orders-page.scss](file:///var/www/html/agsonhos/public_html/css/base/pages/_orders-page.scss): Detalhes, tabelas, chips de status, timeline de rastreamento do pedido, paginação e visualização geral de pedidos.

### 4. Extração e Modularização de `addresses.css`
- Convertido para [addresses.scss](file:///var/www/html/agsonhos/public_html/css/custom/addresses.scss).
- Criamos os arquivos parciais:
  - [_addresses.scss](file:///var/www/html/agsonhos/public_html/css/base/pages/_addresses.scss): Listagem de endereços, cartões, formulários e o toggle switch para endereço padrão.

### 5. Extração e Modularização de `returns-institutional.css`
- Convertido para [returns-institutional.scss](file:///var/www/html/agsonhos/public_html/css/custom/returns-institutional.scss).
- Criamos os arquivos parciais:
  - [_returns.scss](file:///var/www/html/agsonhos/public_html/css/base/pages/_returns.scss): Formulário e histórico de trocas e devoluções.
  - [_contact.scss](file:///var/www/html/agsonhos/public_html/css/base/pages/_contact.scss): Tela de contato com formulário premium.
  - [_info.scss](file:///var/www/html/agsonhos/public_html/css/base/pages/_info.scss): Páginas institucionais e sitemap.

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
- `public_html/css/custom/orders.css`

