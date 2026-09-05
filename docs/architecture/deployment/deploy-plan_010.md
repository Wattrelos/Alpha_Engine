# DP-10: Plan: Populate permissions and implement login logging

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-06-20 13:14:31
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/10

## Descrição

# Plan: Populate permissions and implement login logging

Populate user group permissions according to the administrative panel scenario and implement a robust logging mechanism for admin logins.

## Database Configurations

### Populate user group permissions

Run SQL statements on `agsc_user_group` to populate permissions:

-   **Administrator**: Access and modify on all resources (common/dashboard, catalog/product, catalog/category, catalog/manufacturer, procurement/supplier, customer/customer, setting/setting, sale/order).

-   **Demonstration**: Access on all resources; no modify permissions.
-   **Marketing**: Access on common/dashboard, catalog/product, catalog/category, catalog/manufacturer, customer/customer. Modify on catalog/manufacturer.
-   **Product Data Entry**: Access and modify on catalog/product, catalog/category, catalog/manufacturer, procurement/supplier. Access on common/dashboard.
-   **Order Processing**: Access and modify on sale/order. Access on common/dashboard, catalog/product.
-   **Accounting**: Access on common/dashboard, sale/order, setting/setting. Modify on sale/order.
-   **Customer Service**: Access on common/dashboard, customer/customer, sale/order. Modify on customer/customer.
-   **Analysis**: Access on common/dashboard, sale/order, customer/customer. No modify.
-   **Content Writing**: Access and modify on catalog/product, catalog/category. Access on common/dashboard.

### Auth & Security Services

#### [MODIFY] AdminAuthService.php

-   Implement login logging. In authenticate, log successful logins and failed logins (including brute-force lockouts) to DIR_LOGS . 'admin_login.log'.
-   Retrieve User-Agent from $_SERVER['HTTP_USER_AGENT'] to add context to logs.

## Verification Plan

### Automated Tests

-   Run validation queries and view the written log file entries after performing test logins.

 Populate user group permissions in database.

-   Implement login logging in AdminAuthService.php.

-   Verify functionality.

# Walkthrough: Database Population and Login Logging Implementation

Populated permissions for the administrative user groups based on the actual panel routes and implemented a secure login logging mechanism.

## Changes Made

### Database Population

-   Executed updates on the agsc_user_group table to map detailed access and modify privileges for all predefined administrative user groups:

    1.  **Administrator**: Full privileges.

    2.  **Demonstration**: Read-only access on all resources; no modifying permissions.
    3.  **Marketing**: Access to dashboard, products, categories, manufacturers, and customers. Modifying privileges on manufacturers.
    4.  **Product Data Entry**: Access to catalog and suppliers. Modifying privileges on products, categories, manufacturers, and suppliers.
    5.  **Order Processing**: Access to dashboard, products, and orders. Modifying privileges on orders.
    6.  **Accounting**: Access to dashboard, orders, and settings. Modifying privileges on orders (updating status).
    7.  **Customer Service**: Access to dashboard, customers, and orders. Modifying privileges on customers.
    8.  **Analysis**: Access to dashboard, orders, and customers. No modifying privileges.
    9.  **Content Writing**: Access and modify privileges for products and categories.

### Auth & Security Services

-   **AdminAuthService.php**:

    -   Created a private logAdminLogin(string $status, string $username, string $ip) helper method.

    -   Added logging calls for:

        -   SUCCESS logins.

        -   FAILURE logins.

        -   LOCKED_OUT attempts.

    -   Saved log entries to storage/logs/admin_login.log including timestamp, action status, username, IP address, and User-Agent headers.

## Verification Results
--------------------

-   Ran a simulation script successfully capturing a failed login attempt for a non-existent user, generating the log entry:

[2026-06-07 20:28:24] [FAILURE] User: 'nonexistent_user' | IP: '192.168.0.1' | UA: 'Unknown User-Agent'

Edited task.md

Ran command: `php -r '

Edited task.md
Edited AdminAuthService.php
Edited task.md
Edited test_logging.php
Ran command: `php /6cfd5000-84ea-44fa-8036-ab851e741e57/scratch/test_logging.php`
Edited task.md
Edited walkthrough.md

Colei as permissões nos perfis administrativos no banco de dados e implementei o sistema de log de logins para o painel.

### Resumo do Trabalho Realizado:

1\. **População da Tabela `agsc_user_group`**:

- Defini as permissões exatas de visualização (`access`) e edição (`modify`) no banco de dados para todos os 9 perfis existentes (Administrador, Demonstration, Marketing, Product Data Entry, Order Processing, Accounting, Customer Service, Analysis e Content Writing) baseadas nas rotas atuais do painel.

- Exemplo: O perfil de **Demonstration (ID 2)** possui acesso total de visualização a todas as abas mas tem a lista de modificação (`modify`) totalmente vazia, protegendo os dados. O perfil de **Product Data Entry (ID 4)** possui acesso e modificação apenas nas abas de produtos, categorias, marcas e fornecedores.

2\. **Logs de Autenticação**:

- Atualizado o método `authenticate` em [AdminAuthService.php](file:///var/www/html/agsonhos/core/Auth/Services/AdminAuthService.php) para interceptar os status de logins: `SUCCESS` (sucesso), `FAILURE` (falha de credenciais) e `LOCKED_OUT` (usuário temporariamente bloqueado por força bruta).

- O log é gravado no arquivo físico `storage/logs/admin_login.log`, contendo data/hora, status, usuário, IP e o cabeçalho User-Agent para segurança adicional.

3\. **Verificação**:

- Rodado o script de simulação que registrou com sucesso a tentativa malsucedida no arquivo de log da aplicação.

