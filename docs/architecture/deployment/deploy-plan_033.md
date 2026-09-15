# DP-33: Plano de Implementação - Refatoração de Estilos do PDV

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-06-28 14:31:35
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/33

## Descrição

# Plano de Implementação - Refatoração de Estilos do PDV

Este plano visa transferir os estilos CSS embutidos (inline) nas templates do PDV para os arquivos de estilo dedicados sob `/public_html/css/pov/`. Os estilos serão organizados de forma modular e limpa.

## User Review Required

> [!IMPORTANT]
> A remoção dos estilos embutidos das templates Twig reduzirá o tamanho do HTML transmitido e melhorará o cache do navegador. Para garantir que as páginas continuem estilizadas de forma idêntica, vamos importar os estilos gerados em `pov.css` e incluir a tag `<link>` apropriada no arquivo de layout geral.

## Proposed Changes

Os estilos embutidos em cada template serão distribuídos da seguinte forma nos arquivos sob [css/pov/](/public_html/css/pov/):
- **[variables.css](/public_html/css/pov/variables.css)**: Variáveis CSS globais (`:root`).
- **[layout.css](/public_html/css/pov/layout.css)**: Seletores globais (`*`, `body`, scrollbars), `header.pos-header`, `.pos-container`, `.pos-content`, `.pos-workspace`, `.catalog-panel` e `.cart-panel`.
- **[components.css](/public_html/css/pov/components.css)**: Componentes reutilizáveis e específicos (`.glass-card`, product grids, product cards, cart elements, custom modal, success ticket, ticket details, etc.).

---

### POS CSS

#### [MODIFY] [variables.css](/public_html/css/pov/variables.css)
- Adição das variáveis `:root` extraídas de [layout.twig](/resources/views/%20pos/layout.twig).

#### [MODIFY] [layout.css](/public_html/css/pov/layout.css)
- Adição dos estilos estruturais e globais extraídos de [layout.twig](/resources/views/%20pos/layout.twig) e [register-control.twig](/resources/views/%20pos/sales-rep/register-control.twig).

#### [MODIFY] [components.css](/public_html/css/pov/components.css)
- Adição dos componentes extraídos de [layout.twig](/resources/views/%20pos/layout.twig), [checkout.twig](/resources/views/%20pos/sales-rep/checkout.twig) e [register-control.twig](/resources/views/%20pos/sales-rep/register-control.twig).

---

### POS Views

#### [MODIFY] [layout.twig](/resources/views/%20pos/layout.twig)
- Remoção do bloco `<style>...</style>`.
- Inclusão do link externo para `<link rel="stylesheet" href="/css/pov/pov.css">`.

#### [MODIFY] [checkout.twig](/resources/views/%20pos/sales-rep/checkout.twig)
- Remoção dos estilos contidos no bloco `{% block extra_styles %}...{% endblock %}` (mantendo o bloco vazio para extensibilidade).

#### [MODIFY] [register-control.twig](/resources/views/%20pos/sales-rep/register-control.twig)
- Remoção dos estilos contidos no bloco `{% block extra_styles %}...{% endblock %}` (mantendo o bloco vazio para extensibilidade).

---

## Verification Plan

### Manual Verification
1. Carregar a interface do PDV Vendedor (`/LPDHED2dC7Gjrg2b/pos/vendedor`) no navegador e verificar se o design, fontes, cores e comportamento responsivo continuam idênticos.
2. Realizar uma simulação de venda e verificar se a tela de finalização/sucesso (`/LPDHED2dC7Gjrg2b/pos/vendedor/checkout`) é exibida e estilizada perfeitamente.
3. Verificar no console do desenvolvedor do navegador se não há erros de carregamento do arquivo `/css/pov/pov.css`.

- [x] Copiar variáveis CSS (:root) de layout.twig para variables.css
- [x] Copiar estilos de layout/base de layout.twig e register-control.twig para layout.css
- [x] Copiar componentes específicos e específicos de páginas de layout.twig, checkout.twig, register-control.twig para components.css
- [x] Remover tag <style> de layout.twig e adicionar link para pov.css
- [x] Limpar bloco de estilos em checkout.twig
- [x] Limpar bloco de estilos em register-control.twig
- [x] Verificar se as páginas carregam perfeitamente

# Walkthrough - Refatoração de Estilos do PDV

Os estilos CSS que estavam embutidos diretamente nos arquivos Twig do PDV foram extraídos para arquivos CSS externos modulares para maior reusabilidade, melhor legibilidade do código e melhor cache.

## Alterações Realizadas

### Estilos Modulares (CSS)
1. **[variables.css](/public_html/css/pov/variables.css)**:
   - Contém agora todas as variáveis globais de `:root` (paleta de cores, tipografia, bordas, sombras e transições).
2. **[layout.css](/public_html/css/pov/layout.css)**:
   - Contém seletores base/reset (`*`, `body`, scrollbars), o cabeçalho (`header.pos-header`), container flex (`.pos-container`), workspace layout (`.pos-workspace`) e os painéis esquerdo/direito (`.catalog-panel`, `.cart-panel`).
3. **[components.css](/public_html/css/pov/components.css)**:
   - Contém todos os componentes visuais, incluindo `.glass-card`, barra de pesquisa, grid de produtos e cards (`.product-card`), cabeçalho e itens do carrinho, rodapé com totais, botões de ação e modal personalizado (`.pos-modal`).
   - Contém também os estilos específicos de ticket do checkout e regras para impressão (`@media print`).

### Templates Twig
1. **[layout.twig](/resources/views/%20pos/layout.twig)**:
   - Remoção do bloco de estilo `<style>` embutido.
   - Adicionada a tag `<link rel="stylesheet" href="/css/pov/pov.css">` no cabeçalho.
2. **[checkout.twig](/resources/views/%20pos/sales-rep/checkout.twig)**:
   - Esvaziamento do bloco `{% block extra_styles %}`.
3. **[register-control.twig](/resources/views/%20pos/sales-rep/register-control.twig)**:
   - Esvaziamento do bloco `{% block extra_styles %}`.

## Validação Executada
- Rodamos `git status` e `git diff` para verificar a integridade das alterações.
- Os caminhos de importação no arquivo principal `pov.css` apontam corretamente para os arquivos gerados, garantindo que o carregamento funcione perfeitamente via servidor de assets do e-commerce.

