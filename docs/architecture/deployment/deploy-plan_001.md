# DP-1: Implementation Plan - Product Registration in Admin Dashboard

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-06-18 20:18:04
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/1

## Descrição

# Implementation Plan - Product Registration in Admin Dashboard

Implement product creation functionality in the administrative dashboard. This will enable administrators to register new products directly through a premium UI, supporting file uploads (product images) and relational associations (manufacturers, stock statuses).

## User Review Required

> [!NOTE]
> This change introduces a new endpoint `/LPDHED2dC7Gjrg2b/produtos/criar` handling both GET and POST requests for product registration.

## Proposed Changes

### Routing Configuration

#### [MODIFY] [Routes.php](file:///var/www/html/agsonhos/Config/Routes.php)
- Add route mapping for `['GET', 'POST']` on `/produtos/criar` pointing to `CreateProductAction::class`.

### Controller Actions

#### [NEW] [CreateProductAction.php](file:///var/www/html/agsonhos/core/Admin/Controllers/Actions/Catalog/Product/CreateProductAction.php)
- Implement `CreateProductAction` to handle both GET and POST requests:
  - **GET**: Load list of manufacturers and stock statuses, and render the creation form.
  - **POST**:
    1. Parse request body parameters: `name`, `model`, `price`, `quantity`, `status`, `description`, `ean`, `stock_status_id`, `manufacturer_id`, `date_available`.
    2. Handle file upload for the product image (safely storing it inside `image/product/` with a unique filename).
    3. Insert the product into the `product` table.
    4. Insert language description into `product_description` for all languages.
    5. Insert store mapping into `product_to_store` for `store_id = 1`.
    6. Clear catalog and product cache to ensure the new product appears in lists.
    7. Redirect back to the products list.

### View Templates

#### [NEW] [create.html.twig](file:///var/www/html/agsonhos/resources/views/admin/pages/products/create.html.twig)
- Create a premium product registration form template matching the aesthetic design of the editing view.

#### [MODIFY] [list.html.twig](file:///var/www/html/agsonhos/resources/views/admin/pages/products/list.html.twig)
- Update the "Adicionar Produto" button link to point to `/LPDHED2dC7Gjrg2b/produtos/criar`.

## Verification Plan

### Manual Verification
- We will verify that clicking the "Adicionar Produto" button redirects to the registration form.
- We will test submitting the registration form with validation checks (e.g. empty names) and confirm it successfully stores the product in the database.
- We will test uploading a product image during creation and verify that it renders correctly on the product list.

- [x] Implement product registration in admin dashboard
  - [x] Add route to Config/Routes.php
  - [x] Complete CreateProductAction.php
  - [x] Create resources/views/admin/pages/products/create.html.twig
  - [x] Update resources/views/admin/pages/products/list.html.twig link
- [x] Verify product creation manually or via test script
- [x] Create walkthrough.md

# Walkthrough - Alpha Engine Enhancements

This document details two major updates completed for the Alpha Engine ecosystem:
1. Hydrating `ManyToMany` relationships in the `DataAccessObject`.
2. Implementing the Product Registration (Creation) feature in the Admin Dashboard.

---

## 1. ManyToMany Hydration in DataAccessObject

We completed the implementation of the `ManyToMany` loading/hydration logic in `DataAccessObject.php`.

### Changes Made

#### [DataAccessObject.php](file:///var/www/html/agsonhos/core/Model/DataAccessObject/DataAccessObject.php)
- Updated attribute argument parsing within `processAssociations()` to support positional arguments `args[0]` as a fallback to named arguments for `targetEntity`.
- Replaced the placeholder comment inside the `elseif ($isManyToMany)` block with a complete implementation that:
  1. Dynamically constructs the pivot table name (`tableLink`) and foreign key column names (`fkParent` and `fkChild`) using the naming conventions of the Alpha Engine.
  2. Queries the pivot table to fetch associated child IDs.
  3. Looks up the parent entity's setter method.
  4. Wraps the loading logic in a `LazyCollection` closure if `shouldLazyLoad()` is true, providing transparent Lazy Loading.
  5. Performs a batch query utilizing `readByIds()` to fetch and set target entities immediately for Eager Loading.
- Fixed Intelephense false positives (`Undefined method 'getVersion'`) on `$entity` by substituting indirect boolean checks with direct `instanceof \Alpha\Model\Domain\VersionedEntityInterface` checks, allowing type refinement to work seamlessly in IDEs.

### Verification & Test Results
Verified using integration tests simulating both eager and lazy loading configurations. All tests passed successfully.

---

## 2. Product Registration in Admin Dashboard

We implemented the complete product registration (creation) flow in the administrative panel.

### Changes Made

#### [Routes.php](file:///var/www/html/agsonhos/Config/Routes.php)
- Added route mapping for `['GET', 'POST']` on `/produtos/criar` directing requests to `CreateProductAction`.

#### [CreateProductAction.php](file:///var/www/html/agsonhos/core/Admin/Controllers/Actions/Catalog/Product/CreateProductAction.php)
- Developed the controller class to handle both GET and POST requests:
  - **GET**: Queries the database to list manufacturers and stock statuses, then renders the creation view template.
  - **POST**:
    - Sanitizes and validates the product name.
    - Handles secure image file uploads, storing files with unique names in `image/product/`.
    - Inserts records into the `product` table with default fields (like `master_id = 0`, `shipping = 1`, etc.).
    - Inserts multi-lingual records into the `product_description` table.
    - Links the product to store ID `1` in `product_to_store`.
    - Purges the cache corresponding to the newly added product.
    - Redirects back to the products list view.

#### [create.html.twig](file:///var/www/html/agsonhos/resources/views/admin/pages/products/create.html.twig)
- Created the creation form template matching the premium design system and style patterns of the edit view.

#### [list.html.twig](file:///var/www/html/agsonhos/resources/views/admin/pages/products/list.html.twig)
- Updated the "Adicionar Produto" button to point directly to `/LPDHED2dC7Gjrg2b/produtos/criar`.

### Verification & Test Results
Created an integration test script `tests/TestCreateProduct.php` to simulate form rendering, invalid payload validation, and valid payload execution:
- Verified form gets rendered correctly with input controls (Status 200).
- Verified validation error triggers a Status 400 when name is empty.
- Verified valid POST successfully inserts product into `product`, description in `product_description`, store links in `product_to_store`, and issues a Status 302 redirect.
- Verified Composer autoloader was refreshed to register the new class (`composer dump-autoload`).
- All validation assertions passed.

