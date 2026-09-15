# DP-19: Dashboard Language Selection Implementation Plan

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-06-22 22:58:34
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/19

## Descrição

# Dashboard Language Selection Implementation Plan

This plan details how to implement language switching and translation support in the administrative dashboard, leveraging the existing translation loading engine (`Alpha\Support\Language`) and localizations (`pt-br`, `en-gb`, `fr-fr`).

## User Review Required

> [!IMPORTANT]
> The admin views currently have hardcoded text in Portuguese. Fully translating all admin views is a huge scope. As part of this implementation, we will translate the **common navigation layout (sidebar, header, titles, actions)** as a working proof-of-concept, and define a clear template pattern so any future dashboard pages can easily be internationalized.

## Open Questions

- **Language List**: Do you want the language switcher to read active languages dynamically from the database (`agsc_language` table) or use a static list of the three default available locales (`pt-br`, `en-gb`, `fr-fr`)?
  * *Recommendation*: Dynamically fetch from the database via `LanguageRepository` so that adding/disabling languages in settings automatically updates the dashboard switcher.

---

## Proposed Changes

### Core & Middleware

#### [NEW] [AdminLanguageMiddleware.php](/core/Auth/Middleware/AdminLanguageMiddleware.php)
- Check for `admin_language` cookie.
- Fallback to the configured default catalog language if cookie is absent.
- Set the language code on the container's translator: `$translator->setCode($langCode)`.
- Query active languages using `LanguageRepository` and inject them as a Twig global (`languages`) for rendering the dropdown.
- Load the common admin translations (`admin/common`) and register them in Twig (`AdminLang`).

---

### Dashboard Controllers & Routes

#### [NEW] [SwitchAdminLanguageAction.php](/core/Admin/Controllers/Actions/Common/SwitchAdminLanguageAction.php)
- Handle POST requests at `/LPDHED2dC7Gjrg2b/idioma`.
- Parse and validate `language_code` parameter.
- Set `admin_language` cookie (expiring in 30 days).
- Redirect back to the HTTP referer (to keep the user on their current page).

#### [MODIFY] [Routes.php](/Config/Routes.php)
- Register the post route `/idioma` pointing to `SwitchAdminLanguageAction` inside the admin route group.
- Append `AdminLanguageMiddleware` to the admin route group.

---

### Translations

#### [NEW] [pt-br.admin.common.json](/Locales/pt-br/pt-br.admin.common.json)
- Common translated labels for Portuguese (e.g. Navigation titles, Logout, Profile, Language Selector).

#### [NEW] [en-gb.admin.common.json](/Locales/en-gb/en-gb.admin.common.json)
- Common translated labels for English.

#### [NEW] [fr-fr.admin.common.json](/Locales/fr-fr/fr-fr.admin.common.json)
- Common translated labels for French.

---

### Layouts & UI

#### [MODIFY] [base.html.twig](/resources/views/admin/layouts/base.html.twig)
- In the top header/navbar, add a premium language selection dropdown showing flags and language names (e.g. Português, English, Français).
- Translate the sidebar navigation menu, header user details, and logout buttons using the `AdminLang` Twig global variables.

---

### Test Suite

#### [NEW] [TestAdminLanguage.php](/tests/TestAdminLanguage.php)
- Simulate GET request to dashboard and assert language translations are present.
- Simulate POST request to switch language, check cookie headers, and assert that subsequent dashboard calls load the new translation locale.

---

## Verification Plan

### Automated Tests
- Run `php tests/TestAdminLanguage.php`.

### Manual Verification
- Log in to the admin dashboard.
- Verify the language dropdown is visible in the top header.
- Select "English" or "Français". The page should reload and update the sidebar navigation/header labels to the selected language.

- [x] Create translation files: `pt-br.admin.common.json`, `en-gb.admin.common.json`, `fr-fr.admin.common.json` under `Locales/`
- [x] Create middleware `AdminLanguageMiddleware.php` in `core/Auth/Middleware/`
- [x] Create action `SwitchAdminLanguageAction.php` in `core/Admin/Controllers/Actions/Common/`
- [x] Register switcher route and middleware in `Config/Routes.php`
- [x] Update admin base layout `resources/views/admin/layouts/base.html.twig` to support translations and include switcher UI
- [x] Create integration test `tests/TestAdminLanguage.php` and verify it passes successfully

# Comprehensive Implementation Walkthrough

We have successfully implemented:
1. Category assignment to product CRUD.
2. Storefront out-of-stock product and variation hiding.
3. Supplier Contacts CRUD (admin forms and transactional database mapping).
4. Core transactional database bug fix in base mapper.
5. Dynamic Dashboard Language Switcher with cookie-based persistence and Twig layout translation.

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

## 5. Verification Results

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

4. **Admin Dashboard Language Switcher Test**:
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

   === ALL ADMIN LANGUAGE TESTS PASSED SUCCESSFULLY! ===
   ```

