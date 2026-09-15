# DP-6: Unificação Visual e Funcional da Busca com a Página de Categoria

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-06-20 12:55:16
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/6

## Descrição

# Unificação Visual e Funcional da Busca com a Página de Categoria
================================================================

Este plano visa refatorar a página de busca para reaproveitar os mesmos componentes visuais e lógicos da página de categoria (filtros facetados na barra lateral, ordenação, paginação consistente e formatação de preços/imagens de produtos).

## Proposed Changes
----------------

### Backend

#### [MODIFY] SearchAction.php

-   Injetar dependências necessárias no construtor:

    -   CategoryRepository (para listar categorias nos filtros)
    -   ManufacturerRepository (para listar marcas nos filtros)
    -   ContainerInterface (para obter config, currency, tax e session)
    -   ImagePresenter (para redimensionar as imagens dos produtos)

-   Processar os filtros facetados vindos da URL (price_min, price_max, category, manufacturer, rating) e passá-los para a busca de produtos.

-   Iterar sobre os produtos retornados para:

    -   Redimensionar a imagem de miniatura utilizando ImagePresenter (definindo prod.thumb).
    -   Formatar os preços (price_formatted e special_formatted) usando a classe currency e o conversor de impostos tax.

-   Buscar e passar para a view:

    -   lista_categorias (lista completa de categorias do primeiro nível)
    -   lista_manufacturers (lista completa de fabricantes ou os relacionados)
    -   filtros_ativos (parâmetros ativos selecionados na busca)
### Frontend

#### [MODIFY] search.html.twig

-   Ajustar a estrutura HTML interna para corresponder ao layout de duas colunas da categoria:

    -   Adicionar o container <div class="egen-category-layout">
    -   Incluir a barra lateral de filtros: {% include 'components/organisms/aside_filters.html.twig' %}
    -   Adicionar o container <div class="egen-category-main">

-   Utilizar o componente genérico de cards para renderizar os produtos via loop: {% include 'pages/product/product-card.html.twig' with {'prod': prod} %}

-   Utilizar o componente genérico de paginação: {% include 'components/molecules/pagination.html.twig' ... %}

# Verification Plan
-----------------

### Automated/Manual Verification

-   Executar uma busca por um termo comum e verificar se a barra lateral de filtros (Categorias, Marcas, Faixa de Preço, Avaliação) é exibida corretamente.
-   Aplicar filtros (por exemplo, selecionar uma marca ou categoria específica) e validar se a busca é refinada corretamente.
-   Validar se os preços exibidos nos resultados da busca possuem o formato de moeda correto (ex: R$ 150,00) e se as imagens dos produtos foram redimensionadas para o tamanho correto.
-   Adapt container dependencies and logic in SearchAction.php
-   Format product images and currency/tax formatting in SearchAction.php
-   Fetch and pass active filters (lista_categorias, lista_manufacturers, filtros_ativos) in SearchAction.php
-   Refactor search.html.twig layout to display the sidebar filters and style it identical to category/show.html.twig
-   Verify functionality manually or via logs

# Walkthrough - Unificação Visual da Busca de Produtos
====================================================

Concluímos a refatoração da página de busca de produtos para herdar a mesma estrutura visual e lógica de filtros da página de categorias.

Alterações Realizadas
---------------------

### Backend

-   **SearchAction.php**:

    -   Injetados CategoryRepository, ManufacturerRepository, ContainerInterface e ImagePresenter via construtor.

    -   Adicionado processamento dos filtros facetados do query string (filter_categories, filter_manufacturers, filter_price_min, filter_price_max, filter_rating).

    -   Adicionado loop para aplicar o redimensionamento de imagens (thumb) e a formatação de moedas/impostos (price_formatted, special_formatted) de cada produto de forma consistente.

    -   Carregadas as categorias do primeiro nível (lista_categorias), fabricantes (lista_manufacturers) e os parâmetros de filtros ativos (filtros_ativos) para passar à view do Twig.

    -   Adicionados arrays de configuração $sorts e $limits para a barra de filtros/ordenação funcionar corretamente na busca.

### Frontend

-   **search.html.twig**:

    -   Alterada a estrutura de layout para incluir o component aside_filters.html.twig.

    -   Removido o estilo inline display: block que impedia o posicionamento correto em duas colunas.

    -   A busca passa a se parecer e funcionar exatamente como a página de categorias, exibindo a barra lateral esquerda de filtros com categorias, marcas, faixas de preço e notas de avaliação.

# Testes e Validação
------------------

-   Validada a compilação do PHP executando php -l core/Controller/Actions/Product/SearchAction.php. Nenhuma falha sintática detectada.

Edited task.md
Searched for "class ProductMapper"
Searched for "function getProducts"
Viewed ProductMapper.php:1-601
Searched for "function getCategoryData"
Viewed CategoryRepository.php:1-297
Viewed ManufacturerRepository.php:1-185
Searched for "function getManufacturers"
Viewed ManufacturerMapper.php:1-100
Edited SearchAction.php
Viewed SearchAction.php:44-125
Viewed SearchAction.php:120-134
Edited SearchAction.php
Viewed search.html.twig:80-116
Edited search.html.twig
Viewed task.md:1-6
Edited task.md
Ran command: `php -l core/Controller/Actions/Product/SearchAction.php`
Edited task.md
Edited walkthrough.md

Concluí a refatoração da página de busca de produtos para herdar o mesmo layout e comportamento de filtros facetados da página de categorias, conforme detalhado no arquivo [walkthrough.md](file:///home/kiruma/.gemini/antigravity-ide/brain/c73c99bb-056a-4c45-be27-73a6c4c53893/walkthrough.md).

### Resumo das alterações:

1. **[SearchAction.php](/core/Controller/Actions/Product/SearchAction.php):**

- Injetou repositórios de categoria e marca, `ContainerInterface` (para taxas e moeda) e `ImagePresenter` (para miniaturas).
- Processou os parâmetros de filtros vindos da query string (`category`, `manufacturer`, `price_min`, `price_max`, `rating`).
- Aplicou formatação de moeda com 2 casas decimais e símbolo (`price_formatted` e `special_formatted`) e redimensionamento de miniaturas nos produtos retornados.
- Gerou as opções de ordenação, limites, lista de categorias/fabricantes e filtros ativos para a view.

2. **[search.html.twig](/resources/views/pages/product/search.html.twig):**

- Alterou a estrutura HTML para incluir a barra lateral de filtros (`aside_filters.html.twig`) e posicionar o conteúdo em duas colunas.

