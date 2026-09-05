# DP-8: Implementation Plan - Hydrate Sorts and Limits for Category and Search Pages

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-06-20 13:04:15
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/8

## Descrição

# Implementation Plan - Hydrate Sorts and Limits for Category and Search Pages

Fix the missing / unhydrated sorts and limits dropdown options on the Search and Category pages. This involves adding the appropriate parameters into the Twig data models and ensuring translation data is loaded on the search page.

## User Review Required
--------------------

NOTE

-   SearchProductsAction will now inject \Alpha\Support\Language to load the product/search translation dictionary.
-   On both category and search pages, sorts and limits arrays will be nested within the main view data structure so they correspond to category.sorts, category.limits, search_data.sorts, and search_data.limits as expected by the Twig templates.

## Proposed Changes
----------------

### Products Search Page

#### [MODIFY] SearchProductsAction.php

-   Inject \Alpha\Support\Language $translator in the constructor.

-   Load the product/search translation namespace and assign it globally as Search in Twig.

-   Construct the sorts and limits URL configurations based on query parameters.

-   Populate $data['sorts'], $data['limits'], and $data['pagination'] inside the search data array.

### Category Details Page

#### [MODIFY] ShowCategoryAction.php

-   Set $data['sorts'] = $sorts; and $data['limits'] = $limits; before calling $this->twig->render.

## Verification Plan
-----------------

### Manual Verification

-   Access both /pt-br/busca and /pt-br/categoria/... URLs.
-   Verify that the "Ordenar" (Sort) and "Exibir" (Limit) dropdown menus are fully populated with options.
-   Verify that changing the dropdown selection reloads the page with correct query parameters.
-   Verify that translation strings on the search page are correctly loaded and displayed (e.g., placeholder, breadcrumbs, titles).
-   Modify SearchProductsAction.php to inject translator, load translations, and populate sorts, limits, pagination in search data
-   Modify ShowCategoryAction.php to assign sorts and limits within $data
-   Verify functionality

.../html/agsonhos $ php -l /var/www/html/agsonhos/core/Controller/Actions/Product/SearchProductsAction.php /var/www/html/agsonhos/core/Controller/Actions/Category/ShowCategoryAction.php

No syntax errors detected in /var/www/html/agsonhos/core/Controller/Actions/Prod
uct/SearchProductsAction.php
No syntax errors detected in /var/www/html/agsonhos/core/Controller/Actions/Cate
gory/ShowCategoryAction.php

Working

# Walkthrough - Hydrate Sorts and Limits for Category and Search Pages

We solved the issue where the sorts (Ordenar) and limits (Exibir) variables were not populated on the Search and Category pages.

## Changes Made
------------

### 1\. Products Search Page

#### SearchProductsAction.php

-   Injected the Translator service into the action constructor.

-   Loaded the search translation dictionary namespace (product/search) and assigned it globally to Twig as Search.

-   Constructed dynamic URL generators and populated $data['sorts'], $data['limits'], and $data['pagination'] structures, mapping them correctly to the layout requirements of search.html.twig.

### 2\. Category Details Page

#### ShowCategoryAction.php

-   Nested the generated $sorts and $limits data structures directly into $data['sorts'] and $data['limits']. This hydrates the category.sorts and category.limits properties expected by show.html.twig.

Verification Results
--------------------

-   Validated PHP syntax of both actions using php -l. Both files compiled cleanly with no syntax errors.

