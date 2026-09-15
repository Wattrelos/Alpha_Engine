# DP-20: Hydration of Twig Variables with Selected Language in Admin Dashboard

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-06-22 23:10:34
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/20

## Descrição

# Hydration of Twig Variables with Selected Language in Admin Dashboard

This plan details how to dynamically load and merge page-specific translations in the administrative middleware and hydrate the Supplier CRUD Twig views with the selected language variables.

## User Review Required

> [!NOTE]
> We will map Slim routes using a new class constant `ROUTE_NAMESPACE_MAP` in `AdminLanguageMiddleware` to map administrative pages to translation files (e.g. `admin.supplier.*` to `admin/supplier`). This provides a scalable structure for translating other dashboard pages in the future.

## Open Questions

None. The requested changes directly extend the dynamic translation system using existing patterns.

## Proposed Changes

### Core & Middleware

#### [MODIFY] [AdminLanguageMiddleware.php](/core/Auth/Middleware/AdminLanguageMiddleware.php)
- Define a `ROUTE_NAMESPACE_MAP` class constant mapping supplier routes to the `admin/supplier` translation namespace.
- Extract the route name from `RouteContext` safely within a try-catch block.
- Load page-specific translations if a route name mapping exists.
- Merge page-specific translations with `admin/common` recursively using `array_replace_recursive` and expose them under the `AdminLang` Twig global.

### Translations

#### [MODIFY] [pt-br.admin.supplier.json](/Locales/pt-br/pt-br.admin.supplier.json)
- Add missing translation keys for the creation/editing subtitles, placeholders, alert texts, and selection options.

#### [MODIFY] [en-gb.admin.supplier.json](/Locales/en-gb/en-gb.admin.supplier.json)
- Add the corresponding English translation keys.

#### [MODIFY] [fr-fr.admin.supplier.json](/Locales/fr-fr/fr-fr.admin.supplier.json)
- Add the corresponding French translation keys.

### Twig Views

#### [MODIFY] [index.html.twig](/resources/views/admin/catalog/supplier/index.html.twig)
- Replace all hardcoded strings with Twig variables inside the `AdminLang` structure using fallback default values.

#### [MODIFY] [create.html.twig](/resources/views/admin/catalog/supplier/create.html.twig)
- Replace hardcoded fields, placeholders, labels, and JavaScript alerts/selection helper options with the corresponding `AdminLang` values.

#### [MODIFY] [edit.html.twig](/resources/views/admin/catalog/supplier/edit.html.twig)
- Apply the same dynamic translations to edit fields, titles, buttons, and contact grid fields.

### Verification & Tests

#### [MODIFY] [TestAdminLanguage.php](/tests/TestAdminLanguage.php)
- Add mock route for supplier listing (`/test-supplier-list`) under `AdminLanguageMiddleware`.
- Assert that supplier listing header texts change dynamically based on the chosen locale (Portuguese, English, French).

---

## Verification Plan

### Automated Tests
- Run `php tests/TestAdminLanguage.php` to verify layout and supplier-specific translation switching.

### Manual Verification
- Access the supplier list, creation, and edit pages in the dashboard.
- Toggle between Portuguese, English, and French.
- Verify that page titles, placeholders, buttons, alert dialogs, and table columns update instantly.

- [x] Modify `AdminLanguageMiddleware.php` to load route-based translations and merge them into `AdminLang`
- [x] Update translation files (`pt-br.admin.supplier.json`, `en-gb.admin.supplier.json`, `fr-fr.admin.supplier.json`) with new keys
- [x] Update `resources/views/admin/catalog/supplier/index.html.twig` to use `AdminLang` variables
- [x] Update `resources/views/admin/catalog/supplier/create.html.twig` to use `AdminLang` variables
- [x] Update `resources/views/admin/catalog/supplier/edit.html.twig` to use `AdminLang` variables
- [x] Expand integration test `tests/TestAdminLanguage.php` to assert supplier view localization
- [x] Run verification tests

# Comprehensive Implementation Walkthrough

We have successfully implemented:
1. Category assignment to product CRUD.
2. Storefront out-of-stock product and variation hiding.
3. Supplier Contacts CRUD (admin forms and transactional database mapping).
4. Core transactional database bug fix in base mapper.
5. Dynamic Dashboard Language Switcher with cookie-based persistence and Twig layout translation.
6. Dynamic hydration of page-specific variables using the selected language (starting with Supplier CRUD pages).

---

## 1. Category Integration in Product Admin Dashboard

### Changes Implemented

#### Controllers
- **[CreateProductAction.php](/core/Admin/Controllers/Actions/Catalog/Product/CreateProductAction.php)**: Queries all available categories and passes them to the template on GET. Added request-body parsing for category IDs and logic to store them in `product_to_category` within the transaction on POST.
- **[EditProductAction.php](/core/Admin/Controllers/Actions/Catalog/Product/EditProductAction.php)**: Queried all categories and current categories associated with the product to pass to Twig view.
- **[UpdateProductAction.php](/core/Admin/Controllers/Actions/Catalog/Product/UpdateProductAction.php)**: Added category ID parsing from POST. Integrated transaction logic to delete old category relations and insert updated ones.

#### Templates
- **[create.html.twig](/resources/views/admin/pages/products/create.html.twig)**: Added a premium, scrollable grid checkbox UI for selecting product categories.
- **[edit.html.twig](/resources/views/admin/pages/products/edit.html.twig)**: Added the same grid checkbox UI under the General tab, with pre-selected categories for existing product records.

#### Tests
- **[TestCreateProduct.php](/tests/TestCreateProduct.php)**: Enhanced the mock payload to include categories, asserted proper DB persistence and relation cleanup.

---

## 2. Hide Out-of-Stock Products & Variations (stock_status_id = 5)

### Changes Implemented

#### Storefront Mappers
- **[ProductMapper.php](/core/Mappers/EntityMappers/ProductMapper.php)**:
  - Replaced hardcoded `p.quantity > 0` checks with `NOT (p.quantity <= 0 AND p.stock_status_id = 5)` across all storefront queries (`getProduct`, `getProducts`, `getProductsByIds`, `getTotalProducts`, `getRelated`).
  - Added `AND NOT (pv.quantity <= 0 AND pv.stock_status_id = 5)` to the subqueries calculating variant min/max price, name, and image so out-of-stock variants with status ID 5 are correctly ignored.
- **[ManufacturerMapper.php](/core/Mappers/EntityMappers/ManufacturerMapper.php)**: Updated `getManufacturersByCategory` to replace the `p.quantity > 0` condition with `NOT (p.quantity <= 0 AND p.stock_status_id = 5)`.

#### Storefront Controllers
- **[ShowProductAction.php](/core/Controller/Actions/Product/ShowProductAction.php)**: Filtered out any variation whose quantity is <= 0 and stock_status_id is 5 in the variation loop for storefront rendering.

#### Tests
- **[TestStockStatusHiding.php](/tests/TestStockStatusHiding.php)**: Created a new integration test script to verify that products are hidden/visible depending on their stock status ID and quantity.

---

## 3. Supplier Contacts Integration (Admin CRUD & Database)

### Changes Implemented

#### Domain Entities & Repositories
- **[Supplier.php](/core/Model/Domain/Entities/Supplier/Supplier.php)**: Added `$contacts` array property with getter and setter to aggregate contact details within the Supplier entity.
- **[SupplierRepository.php](/core/Model/Domain/Repositories/SupplierRepository.php)**:
  - **`find(int $id)`**: Queries contacts associated with the supplier from the `agsc_contact` table via the `agsc_supplier_contact_manufacturer` pivot table and populates the entity.
  - **`save(Supplier $supplier)`**: Manually saves/updates contacts, updates pivot records, and performs orphan contact cleanup inside a database transaction.
  - **`delete(int $id)`**: Retrieves all associated contacts and deletes them from the contacts table to maintain DB integrity.

#### Framework Core (Bug Fix)
- **[DataAccessObject.php](/core/Model/DataAccessObject/DataAccessObject.php)**: Fixed a critical bug in the core `delete()` method which was unconditionally beginning and committing database transactions. It now correctly checks if a transaction is already active using `!$conn->inTransaction()`, preventing nested transaction collisions when deleting dependent objects.

#### Dashboard Controllers
- **[CreateSupplierAction.php](/core/Admin/Controllers/Actions/Procurement/Supplier/CreateSupplierAction.php)**: Fetches and binds `manufacturers` to the creation view.
- **[EditSupplierAction.php](/core/Admin/Controllers/Actions/Procurement/Supplier/EditSupplierAction.php)**: Fetches and binds `manufacturers` to the editing view.
- **[StoreSupplierAction.php](/core/Admin/Controllers/Actions/Procurement/Supplier/StoreSupplierAction.php)**: Parses contacts from the POST body, assigns them to the supplier entity, and retrieves manufacturers on validation errors or exceptions.
- **[UpdateSupplierAction.php](/core/Admin/Controllers/Actions/Procurement/Supplier/UpdateSupplierAction.php)**: Handles contact parsing and saving on update, and returns the manufacturer list on validation errors.

#### UI Views
- **[create.html.twig](/resources/views/admin/catalog/supplier/create.html.twig)** and **[edit.html.twig](/resources/views/admin/catalog/supplier/edit.html.twig)**: Added a premium, dynamic contacts management table. The table supports adding/removing rows (with micro-transitions and hover states) and binds all inputs (Name, Email, Phone, Position, Manufacturer represented, and Status) directly to the submission payload.

#### Tests
- **[TestSupplierContacts.php](/tests/TestSupplierContacts.php)**: Added a comprehensive integration test checking database state transitions (supplier creation, contact queries, modifying values, deleting orphans, and cascading deletion).

---

## 4. Dashboard Language Selection

### Changes Implemented

#### Middleware
- **[AdminLanguageMiddleware.php](/core/Auth/Middleware/AdminLanguageMiddleware.php)**: Resolves the active language from the `admin_language` cookie, maps the db code (e.g. `'fr'` to `'fr-fr'` directories), configures the container translator, retrieves all active languages from the DB, and passes `languages` list and translated navigation strings (`AdminLang`) globally to Twig.

#### Controllers
- **[SwitchAdminLanguageAction.php](/core/Admin/Controllers/Actions/Common/SwitchAdminLanguageAction.php)**: Receives the language change POST request, sets the `admin_language` cookie (persisted for 30 days), and redirects the administrator back to their referer URL.

#### Routes Configuration
- **[Routes.php](/Config/Routes.php)**: Registered the `/idioma` route in the admin route group and added the `AdminLanguageMiddleware` to apply language mapping and translations dynamically on all admin pages.

#### Translations
- Added layout translations under `Locales/`:
  - **[pt-br.admin.common.json](/Locales/pt-br/pt-br.admin.common.json)**
  - **[en-gb.admin.common.json](/Locales/en-gb/en-gb.admin.common.json)**
  - **[fr-fr.admin.common.json](/Locales/fr-fr/fr-fr.admin.common.json)**

#### Layouts
- **[base.html.twig](/resources/views/admin/layouts/base.html.twig)**:
  - Replaced hardcoded text in the sidebar navigation and header elements with the dynamic `AdminLang` variables.
  - Added a premium, seamless language switcher dropdown in the header that automatically submits and updates the admin's locale instantly.

#### Tests
- **[TestAdminLanguage.php](/tests/TestAdminLanguage.php)**: Integration test simulating dashboard calls, switching languages, and verifying that the layout correctly loads translated values for Portuguese, English, and French.

---

## 5. Hydration of Supplier Views with Selected Language

### Changes Implemented

#### Route Namespace Map inside Middleware
- **[AdminLanguageMiddleware.php](/core/Auth/Middleware/AdminLanguageMiddleware.php)**: Added a class constant `ROUTE_NAMESPACE_MAP` mapping route names (e.g. `admin.supplier.list`, `admin.supplier.edit`) to their translation namespace (`admin/supplier`). Added logic to extract the route name from `RouteContext` and load/merge the namespace dictionary directly into `AdminLang`.

#### Enhanced Translation files
- Added extra translation keys (page headings, placeholders, dropdown items, error and ViaCEP search states) to:
  - **[pt-br.admin.supplier.json](/Locales/pt-br/pt-br.admin.supplier.json)**
  - **[en-gb.admin.supplier.json](/Locales/en-gb/en-gb.admin.supplier.json)**
  - **[fr-fr.admin.supplier.json](/Locales/fr-fr/fr-fr.admin.supplier.json)**

#### View Updates
- **[index.html.twig](/resources/views/admin/catalog/supplier/index.html.twig)**: Substituted list titles, filters, labels, columns, and empty state strings with their dynamic translated counterparts inside `AdminLang` with fallbacks.
- **[create.html.twig](/resources/views/admin/catalog/supplier/create.html.twig)**: Substituted form fields, placeholders, section headers, select inputs, and JavaScript alerts with corresponding `AdminLang` values.
- **[edit.html.twig](/resources/views/admin/catalog/supplier/edit.html.twig)**: Refactored edit view fields, subtitles, address selectors, contact list rows, and labels to use dynamic translated properties.

#### Expanded Test Coverage
- **[TestAdminLanguage.php](/tests/TestAdminLanguage.php)**: Registered a mock `/test-supplier-list` endpoint named `admin.supplier.list` and wrote dynamic assertions for Portuguese, English, and French translations.

---

## 6. Verification Results

### Automated Tests
All test suites execute successfully:

1. **Product Creation Test**:
   ```bash
   php tests/TestCreateProduct.php
   ```
   *Output*: `ALL TESTS PASSED SUCCESSFULLY!`

2. **Stock Hiding Test**:
   ```bash
   php tests/TestStockStatusHiding.php
   ```
   *Output*: `ALL STOCK STATUS HIDING TESTS PASSED SUCCESSFULLY!`

3. **Supplier Contacts Test**:
   ```bash
   php tests/TestSupplierContacts.php
   ```
   *Output*: `ALL TESTS PASSED SUCCESSFULLY!`

4. **Admin Dashboard Language Switcher & Hydration Test**:
   ```bash
   php tests/TestAdminLanguage.php
   ```
   *Output*:
   ```
   === STARTING ADMIN LANGUAGE INTEGRATION TEST ===

   === 1. Testing Default Language (Portuguese) ===
   Status: 200
   Assertion PASSED: Default language is Portuguese (found 'Painel Inicial', 'Sair do Painel').

   === 2. Testing Language Switch Request (Switch to English) ===
   Status: 302
   Set-Cookie Header: admin_language=en-gb; Max-Age=2592000; Path=/; HttpOnly; SameSite=Lax
   Assertion PASSED: Set-Cookie header contains 'admin_language=en-gb'.

   === 3. Testing English Translation Load via Cookie ===
   Assertion PASSED: English translation successfully loaded (found 'Dashboard', 'Logout').

   === 4. Testing French Translation Load via Cookie ===
   Assertion PASSED: French translation successfully loaded (found 'Tableau de Bord', 'Se Déconnecter').

   === 5. Testing Supplier List Translation Load (Portuguese) ===
   Assertion PASSED: Supplier list default language loaded Portuguese correctly (found 'Gerenciamento de Fornecedores', 'Razão Social').

   === 6. Testing Supplier List Translation Load (English) ===
   Assertion PASSED: Supplier list language loaded English correctly (found 'Supplier Management', 'Company Name').

   === 7. Testing Supplier List Translation Load (French) ===
   Assertion PASSED: Supplier list language loaded French correctly (found 'Gestion des Fournisseurs', 'Raison Sociale').

   === ALL ADMIN LANGUAGE TESTS PASSED SUCCESSFULLY! ===
   ```

