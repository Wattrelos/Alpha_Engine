# DP-9: Implementation Plan - Autofill CEP on Cart Page

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-06-20 13:09:07
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/9

## Descrição

# Implementation Plan - Autofill CEP on Cart Page

Ensure the cart page automatically populates the CEP in the shipping simulator field (#shipping-cep / #btn-calculate-shipping) from the customer's default address or session data if they are logged in or have previously entered it, matching the behavior of the product details page.

User Review Required
--------------------

NOTE

We will inject CustomerAddressesRepository into ShowCartAction's constructor. The micro-container (AppContainer) automatically resolves repositories via RepositoryFactory if they are type-hinted in the constructor, so this is safe and idiomatic for this project.

## Proposed Changes
----------------

### Backend Logic

#### [MODIFY] ShowCartAction.php

-   Import Alpha\Model\Domain\Repositories\CustomerAddressesRepository.
-   Add CustomerAddressesRepository $addressRepository to the constructor.
-   Update the CEP retrieval logic around line 130-132 to first check the session's 'shipping_cep', then fall back to the logged-in customer's default address postal code (using the new repository), and finally fall back to $session->data['shipping_address']['postcode'] if available.

# Verification Plan
-----------------

### Manual Verification

-   Access the cart page as a logged-in customer with a registered address, or with a memorized address in the session.
-   Verify that the CEP input field is automatically filled with the registered/memorized CEP.
-   Verify that the shipping simulator display behaves properly (shows/calculates based on the pre-filled CEP).
-   Modify ShowCartAction.php to inject CustomerAddressesRepository and retrieve customer/session CEP

-   Verify functionality

# Walkthrough - Autofill CEP on Cart Page

We updated the cart page's shipping simulator controller logic to fetch the customer's registered default address CEP or their session-memorized address CEP. This matches the behavior of the product details page so that the customer only needs to enter their CEP once.

Changes Made
------------

### Backend

#### ShowCartAction.php

-   Imported CustomerAddressesRepository.
-   Injected CustomerAddressesRepository into the constructor.
-   Replaced the simple session checks for shipping_cep with the robust multi-step fallback checks:

    1.  Session-specific shipping_cep (if they computed it earlier this session).
    2.  Logged-in customer's default address postal code.
    3.  Session-stored shipping_address['postcode'].

Verification Results
--------------------

-   Ran php -l checks to verify syntax and ensure no errors are present.

Edited task.md
Edited task.md
Edited ShowCartAction.php
Ran command: `php -l /var/www/html/agsonhos/core/Controller/Actions/Cart/ShowCartAction.php`
Viewed composer.json:1-35
Edited task.md
Viewed test_customer_addresses.php:1-78
Edited test_show_cart_action.php
Ran command: `php /aa7809a7-cd2d-4100-87c5-7b9c9a1bdd2a/scratch/test_show_cart_action.php`
Edited test_show_cart_action.php
Ran command: `php /aa7809a7-cd2d-4100-87c5-7b9c9a1bdd2a/scratch/test_show_cart_action.php`
Edited task.md
Edited walkthrough.md
Ran command: `rm -f /aa7809a7-cd2d-4100-87c5-7b9c9a1bdd2a/scratch/test_show_cart_action.php`
I have successfully completed the tasks. Below is a summary of the changes:

1\. **Injected Repository:** Imported and injected the [CustomerAddressesRepository](/core/Model/Domain/Repositories/CustomerAddressesRepository.php) into the [ShowCartAction](/core/Controller/Actions/Cart/ShowCartAction.php) constructor.

2\. **Updated CEP Retrieval:** Replaced the simple session lookup in [ShowCartAction](/core/Controller/Actions/Cart/ShowCartAction.php) with the multi-step fallback checks matching `ShowProductAction`'s logic:

- First, checks the session `shipping_cep`.
- Second, if the customer is logged in, checks and uses their default address postal code.
- Third, checks for any session-memorized delivery postcode (`shipping_address['postcode']`).

You can review the full details in the [walkthrough.md](file:///aa7809a7-cd2d-4100-87c5-7b9c9a1bdd2a/walkthrough.md) artifact. Let me know if there's anything else you need!

