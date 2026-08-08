---
adr: 1
title: Estratégia Híbrida de Modularização e Compilação de CSS/SCSS
status: Approved
date: 2026-07-10
authors:
  - Antigravity AI
  - Josias
impacted_components:
  - directory: public_html/css/custom/
  - file: composer.json
  - file: public_html/css/custom/_shared-mixins.scss
rules:
  compilation:
    engine: "scssphp/scssphp"
    command: "composer build-css"
    no_node_production: true
  bundling:
    unified_bundle: "new-stylesheet.css"
    unified_pages:
      - Home
      - Category
      - Product Detail
      - Cart
    lazy_loaded_modules:
      - addresses.css
      - orders.css
      - returns-institutional.css
  reuse_pattern:
    method: "Sass Placeholders"
    file: "_shared-mixins.scss"
---

# ADR 001: Estratégia Híbrida de Modularização e Compilação de CSS/SCSS

## Status
Aprovado (2026-07-10)

## Contexto
O e-commerce Alpha Engine possuía um arquivo de estilo monolítico (`new-stylesheet.css`) com mais de 4500 linhas de código, dificultando a manutenibilidade, a especialização de estilos e a evolução do design. Com a introdução do Sass (SCSS) para modularizar os arquivos por componentes e páginas, surgiu a necessidade de definir como esses arquivos compilados devem ser servidos no ambiente de produção: se consolidamos tudo em um único pacote de estilos ou se fragmentamos um arquivo `.css` para cada página do site.

## Decisão
Adotamos uma **Estratégia Híbrida de Entrega de CSS**:

1.  **Bundle Principal Unificado (`new-stylesheet.css`):**
    *   Mantemos as páginas da jornada direta de vendas (Home, Categorias, Página de Produto, Carrinho) consolidadas em uma única folha de estilo principal.
    *   **Razão:** O tamanho total do CSS compilado e compactado (Gzip/Brotli) é extremamente baixo (~15-18 KB). Consolidar esses arquivos permite que o navegador faça o download apenas uma vez no primeiro acesso e aproveite o cache local em toda a jornada do cliente, evitando requisições HTTP adicionais e o risco de oscilações visuais (FOUC).

2.  **Módulos Isolados por Demanda (Estilos Especializados):**
    *   Páginas e fluxos secundários, restritos ou que ocorrem após autenticação são compilados em arquivos CSS independentes e carregados sob demanda apenas onde são utilizados.
    *   **Arquivos Criados:**
        *   `addresses.css`: Carregado apenas nas rotas de gerenciamento de endereços.
        *   `orders.css`: Carregado apenas nas rotas de histórico e detalhes de pedidos do cliente.
        *   `returns-institutional.css`: Carregado nas páginas institucionais (SAC, Quem Somos) e no formulário/histórico de trocas e devoluções.

3.  **Ferramenta de Compilação Pura PHP:**
    *   Para evitar a necessidade de Node.js/NPM no servidor de hospedagem, utilizamos o compilador nativo PHP `scssphp/scssphp`, integrado via Composer (`composer build-css`), que escaneia dinamicamente e compila todos os arquivos principais da pasta `public_html/css/custom/`.

4.  **Reutilização via Placeholders Sass:**
    *   Criamos o arquivo `_shared-mixins.scss` com seletores placeholders (`%premium-card`, `%premium-button`, `%premium-hero`, etc.) para compartilhar os padrões de design premium entre a folha de estilo principal e os módulos sob demanda, evitando a duplicação de regras CSS.

## Consequências

### Positivas (Prós)
*   **Manutenibilidade:** O código-fonte está perfeitamente organizado em pequenos arquivos SCSS especializados (Atoms, Molecules, Pages).
*   **Performance:** A jornada principal de compras se beneficia totalmente do cache do navegador.
*   **Redução de Redundância:** Estilos comuns são herdados via placeholders do Sass, garantindo um código enxuto e consistente em todas as páginas.
*   **Independência de Ambiente:** Compilação nativa em PHP sem dependência de dependências Node/npm complexas em produção.

### Negativas (Contras)
*   Necessidade de executar `composer build-css` manualmente (ou integrado a um CI/CD) sempre que houver alterações visuais nos arquivos `.scss`.
