# DP-73: Reorganizar e Consolidar Testes em `tests/Validation

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-08-11 00:00:52
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/73

## Descrição

# Implementation Plan - Reorganizar e Consolidar Testes em `tests/Validation/`

Este plano descreve a reorganização completa da estrutura de testes do projeto. Atualmente, existem diversos scripts procedurais espalhados na raiz de `tests/` e em `tests/security_tests/` que utilizam `echo` e `exit(1)`, enquanto `tests/Validation/` utiliza o padrão **PHPUnit** (`TestCase`).

O objetivo é migrar/consolidar todas as asserções e rotinas de validação para a pasta `tests/Validation/` em classes formais do PHPUnit, permitindo a execução em lote via `vendor/bin/phpunit` e eliminando arquivos e testes redundantes.

---

## User Review Required

> [!IMPORTANT]
> **Consolidação e Remoção de Scripts Legados:**
> - Todos os scripts procedurais soltos na pasta `tests/` (`Test*.php` e `test_*.php`) serão convertidos em classes PHPUnit e movidos para `tests/Validation/`.
> - A pasta `tests/security_tests/` continha scripts manuais que duplicavam testes já presentes em `tests/Validation/` (como CSRF, Tenant Isolation e Header Security). Os scripts desta pasta serão integrados/eliminados.
> - O comando `vendor/bin/phpunit` passará a executar **100% dos testes da aplicação** de forma unificada.

---

## Proposed Changes

### Estrutura de Testes (`tests/Validation/`)

Agrupamento das suítes de testes em classes PHPUnit padronizadas sob o namespace `Tests\Validation`:

#### [NEW] [AdminLanguageValidationTest.php](file:///var/www/html/agsonhos/tests/Validation/AdminLanguageValidationTest.php)
- Consolida as validações de `tests/TestAdminLanguage.php` e `tests/test_translation_loading.php`.
- Testas carregamento de traduções (pt-br, en-gb, fr-fr), troca de idioma via cookie/header, fallback DRY e chaves legadas.

#### [NEW] [ProductValidationTest.php](file:///var/www/html/agsonhos/tests/Validation/ProductValidationTest.php)
- Consolida `tests/TestCreateProduct.php` e `tests/TestStockStatusHiding.php`.
- Valida formulário GET de criação de produto, validação de payload inválido, persistência completa no banco (produto, loja, categoria, descrição) e regras de exibição/ocultação por status de estoque (out-of-stock hiding).

#### [NEW] [PosCashierValidationTest.php](file:///var/www/html/agsonhos/tests/Validation/PosCashierValidationTest.php)
- Consolida `tests/TestPOSCashier.php` e `tests/TestPOSPreOrder.php`.
- Valida o fluxo de Caixa PDV: existência de Actions, criação de pré-pedido com reserva de estoque, transição de status (1 - Pendente para 5 - Concluído) e integridade de estoque (prevenção de dupla dedução).

#### [NEW] [ReturnProductValidationTest.php](file:///var/www/html/agsonhos/tests/Validation/ReturnProductValidationTest.php)
- Consolida `tests/TestReturnProducts.php` e `tests/TestReturnProductRepository.php`.
- Valida o fluxo de solicitações de devolução de produtos pelo cliente e consultas via repositório.

#### [NEW] [SupplierContactValidationTest.php](file:///var/www/html/agsonhos/tests/Validation/SupplierContactValidationTest.php)
- Consolida `tests/TestSupplierContacts.php`.
- Valida CRUD de contatos de fornecedores e suas associações com fabricantes/marcas.

#### [NEW] [LoggingValidationTest.php](file:///var/www/html/agsonhos/tests/Validation/LoggingValidationTest.php)
- Consolida `tests/TestLogging.php`.
- Valida gravação de logs de auditoria em tentativas frustradas de autenticação.

#### [NEW] [TenantProvisioningValidationTest.php](file:///var/www/html/agsonhos/tests/Validation/TenantProvisioningValidationTest.php)
- Consolida `tests/TestOOBE.php` e `tests/test_tenant_provisioning.php`.
- Valida provisionamento inicial de lojas (OOBE - Out of Box Experience) e isolamento multi-tenant de domínios/lojas.

#### [NEW] [OrderStatusValidationTest.php](file:///var/www/html/agsonhos/tests/Validation/OrderStatusValidationTest.php)
- Consolida `tests/TestOrderStatus1.php`.
- Valida criação, atualização e ciclo de vida dos status de pedido.

#### [NEW] [CustomerAddressValidationTest.php](file:///var/www/html/agsonhos/tests/Validation/CustomerAddressValidationTest.php)
- Consolida `tests/test_customer_addresses.php` e `tests/test_order_addresses.php`.
- Valida associação de múltiplos endereços de clientes e mapeamento de endereços de cobrança/entrega nos pedidos.

#### [NEW] [FulltextSearchValidationTest.php](file:///var/www/html/agsonhos/tests/Validation/FulltextSearchValidationTest.php)
- Consolida `tests/test_fulltext_search.php`.
- Valida busca textual completa no catálogo de produtos.

#### [NEW] [ExtendedSecurityValidationTest.php](file:///var/www/html/agsonhos/tests/Validation/ExtendedSecurityValidationTest.php)
- Consolida verificações de segurança remanescentes de `tests/security_tests/` (`teste_debug_mode.php`, `teste_lgpd_sanitizer.php`, `teste_secure_cookie.php`, `teste_user_management.php`, `test_user_group_i18n.php`).

---

### Limpeza de Arquivos Redundantes e Legados

#### [DELETE] Scripts na Raiz de `tests/`
- [DELETE] [TestAdminLanguage.php](file:///var/www/html/agsonhos/tests/TestAdminLanguage.php)
- [DELETE] [TestCreateProduct.php](file:///var/www/html/agsonhos/tests/TestCreateProduct.php)
- [DELETE] [TestLogging.php](file:///var/www/html/agsonhos/tests/TestLogging.php)
- [DELETE] [TestOOBE.php](file:///var/www/html/agsonhos/tests/TestOOBE.php)
- [DELETE] [TestOrderStatus1.php](file:///var/www/html/agsonhos/tests/TestOrderStatus1.php)
- [DELETE] [TestPOSCashier.php](file:///var/www/html/agsonhos/tests/TestPOSCashier.php)
- [DELETE] [TestPOSPreOrder.php](file:///var/www/html/agsonhos/tests/TestPOSPreOrder.php)
- [DELETE] [TestReturnProductRepository.php](file:///var/www/html/agsonhos/tests/TestReturnProductRepository.php)
- [DELETE] [TestReturnProducts.php](file:///var/www/html/agsonhos/tests/TestReturnProducts.php)
- [DELETE] [TestStockStatusHiding.php](file:///var/www/html/agsonhos/tests/TestStockStatusHiding.php)
- [DELETE] [TestSupplierContacts.php](file:///var/www/html/agsonhos/tests/TestSupplierContacts.php)
- [DELETE] [test_customer_addresses.php](file:///var/www/html/agsonhos/tests/test_customer_addresses.php)
- [DELETE] [test_fulltext_search.php](file:///var/www/html/agsonhos/tests/test_fulltext_search.php)
- [DELETE] [test_order_addresses.php](file:///var/www/html/agsonhos/tests/test_order_addresses.php)
- [DELETE] [test_tenant_provisioning.php](file:///var/www/html/agsonhos/tests/test_tenant_provisioning.php)
- [DELETE] [test_translation_loading.php](file:///var/www/html/agsonhos/tests/test_translation_loading.php)

#### [DELETE] Diretório `tests/security_tests/`
- [DELETE] `tests/security_tests/` (10 scripts procedurais integrados/substituídos por `tests/Validation/`).

---

### Configuração do PHPUnit (`phpunit.xml`)

#### [MODIFY] [phpunit.xml](file:///var/www/html/agsonhos/phpunit.xml)
- Atualizar a tag `<directory>` para apontar especificamente para `tests/Validation` (ou manter `tests` direcionado para `Validation`).

---

## Verification Plan

### Automated Tests
1. Executar a suíte de testes em lote:
   ```bash
   vendor/bin/phpunit --testdox
   ```
2. Garantir que 100% das suítes e asserções passem sem falhas e sem warnings.
3. Verificar a ausência de scripts soltos executados fora do PHPUnit.

### Manual Verification
- Confirmar que a pasta `tests/Validation/` contém todas as áreas funcionais organizadas e que a pasta `tests/` está limpa e organizada.

# Tasks - Reorganização e Consolidação de Testes em `tests/Validation/`

- [x] Criar suítes de testes PHPUnit em `tests/Validation/`
  - [x] `AdminLanguageValidationTest.php` (Admin language & translation loading)
  - [x] `ProductValidationTest.php` (Product creation & stock status hiding)
  - [x] `PosCashierValidationTest.php` (POS Cashier & Pre-Order lifecycle)
  - [x] `ReturnProductValidationTest.php` (Return products & repository)
  - [x] `SupplierContactValidationTest.php` (Supplier contacts & manufacturer links)
  - [x] `LoggingValidationTest.php` (Audit logging)
  - [x] `TenantProvisioningValidationTest.php` (OOBE & tenant provisioning)
  - [x] `OrderStatusValidationTest.php` (Order status cycle)
  - [x] `CustomerAddressValidationTest.php` (Customer & Order addresses)
  - [x] `FulltextSearchValidationTest.php` (Fulltext search)
  - [x] `ExtendedSecurityValidationTest.php` (Debug mode, LGPD, secure cookie, user mgmt)
- [x] Eliminar scripts soltos e redundantes em `tests/` e `tests/security_tests/`
- [x] Atualizar `phpunit.xml`
- [x] Executar suíte completa via `vendor/bin/phpunit --testdox` e validar (58 testes, 161 asserções - 100% OK)
- [x] Criar `walkthrough.md` com os resultados

# Resumo das Alterações - Consolidação de Testes em `tests/Validation/`

Todos os testes procedurais soltos e redundantes foram migrados e consolidados com sucesso para a suíte padronizada do **PHPUnit** em [tests/Validation/](file:///var/www/html/agsonhos/tests/Validation/).

---

## 🚀 Principais Conquistas

1. **Testes em Lote Unificados (`vendor/bin/phpunit`)**:
   - 100% dos testes da aplicação agora rodam de forma automatizada com um único comando.
   - Total de **58 suítes de testes** e **161 asserções** validadas com **0 falhas e 0 erros**.

2. **Novas Suítes de Testes PHPUnit Criadas**:
   - [AdminLanguageValidationTest.php](file:///var/www/html/agsonhos/tests/Validation/AdminLanguageValidationTest.php): Validação de troca de idioma (pt-br, en-gb, fr-fr), cookies e carregamento DRY/legado.
   - [ProductValidationTest.php](file:///var/www/html/agsonhos/tests/Validation/ProductValidationTest.php): Cadastro completo de produto, persistência e regra de exibição/ocultação por estoque (`stock_status_id`).
   - [PosCashierValidationTest.php](file:///var/www/html/agsonhos/tests/Validation/PosCashierValidationTest.php): Ações do Caixa PDV, criação de pré-pedido, transição de status (1 -> 5) e integridade de estoque sem dupla dedução.
   - [ReturnProductValidationTest.php](file:///var/www/html/agsonhos/tests/Validation/ReturnProductValidationTest.php): Resolução de Actions de devolução para clientes e painel admin.
   - [SupplierContactValidationTest.php](file:///var/www/html/agsonhos/tests/Validation/SupplierContactValidationTest.php): CRUD de contatos de fornecedores, relacionamentos pivot e limpeza de orfãos (orphan cleanup).
   - [LoggingValidationTest.php](file:///var/www/html/agsonhos/tests/Validation/LoggingValidationTest.php): Auditoria de tentativas de login inválidas.
   - [TenantProvisioningValidationTest.php](file:///var/www/html/agsonhos/tests/Validation/TenantProvisioningValidationTest.php): Wizard de instalação (OOBE), leitor atômico `.env` e middleware de bloqueio/redirecionamento `/setup`.
   - [OrderStatusValidationTest.php](file:///var/www/html/agsonhos/tests/Validation/OrderStatusValidationTest.php): Repositório e ciclo de vida de status de pedidos.
   - [CustomerAddressValidationTest.php](file:///var/www/html/agsonhos/tests/Validation/CustomerAddressValidationTest.php): CRUD de endereços de clientes e mapeamento em pedidos.
   - [FulltextSearchValidationTest.php](file:///var/www/html/agsonhos/tests/Validation/FulltextSearchValidationTest.php): Sanitização de operadores booleanos e execução de busca FULLTEXT no banco.
   - [ExtendedSecurityValidationTest.php](file:///var/www/html/agsonhos/tests/Validation/ExtendedSecurityValidationTest.php): Sanitização LGPD, flags de debug, cookies de sessão e permissões de grupo.

3. **Remoção de Arquivos Soltos e Duplicados**:
   - Eliminados 16 scripts soltos na raiz da pasta `tests/` (`Test*.php` e `test_*.php`).
   - Removida a pasta `tests/security_tests/` (10 scripts procedurais integrados em `tests/Validation/`).

4. **Configuração Unificada (`phpunit.xml`)**:
   - Aponta a suíte padrão de testes diretamente para `tests/Validation`.

---

## 🧪 Validação dos Testes

Execução realizada via terminal com sucesso:

```bash
vendor/bin/phpunit --testdox
```

### Resultado:
```text
OK (58 tests, 161 assertions)
Time: 00:00.643, Memory: 32.50 MB
```

