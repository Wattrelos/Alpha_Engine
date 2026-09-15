# DP-66: Eliminação de Códigos SQL Soltos nas Actions do Painel Administrativo

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-08-05 22:06:00
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/66

## Descrição

# Plano de Implementação: Eliminação de Códigos SQL Soltos nas Actions do Painel Administrativo

Este plano estabelece a estratégia para alinhar completamente o Painel Administrativo (Admin) à arquitetura descrita no [architectureDiagram.puml](/docs/architecture/architectureDiagram.puml) e no guia [architecture-validator](/.agents/skills/architecture-validator/SKILL.md).

O objetivo principal é remover **todas** as instâncias de conexões diretas `ConnectionDB::getInstance()->getConnection()` e consultas SQL brutas (`PDO::prepare`, `SELECT/INSERT/UPDATE/DELETE` manuais) das Actions do Slim, delegando a responsabilidade de leitura e persistência para a camada de Domínio (`Repositories`) e Infraestrutura de Persistência (`Mappers` com `QueryBuilder`, `DAO` e `UnitOfWork`).

---

## Fluxo Arquitetural Alvo (Admin)

```
Slim 4 Route → AdminSessionMiddleware → Action (extends BaseController) → Repository → Mapper → DAO/UnitOfWork → MySQL
```

---

## User Review Required

> [!IMPORTANT]
> A refatoração será realizada em fases estruturadas por módulo funcional (Catálogo, Fabricantes, Produtos, Vendas/Pedidos, Fornecedores, POS e Clientes) para garantir estabilidade e testes contínuos sem regressões.

> [!NOTE]
> As Actions do front-end (`core/Controller/Actions`) já utilizam Repositórios de Domínio injetados. Esta intervenção focará primariamente nas Actions administrativas localizadas em `core/Admin/Controllers/Actions`.

---

## Open Questions

Não há dúvidas impeditivas no momento. A estrutura base de Repositórios e Mappers já existe na Alpha Engine para a maioria dos módulos.

---

## Proposed Changes

### FASE 1: Módulo de Catálogo — Categoria & Fabricante

#### Catálogo / Categoria (Conclusão)
Finalizar a remoção de SQL em `ListCategoriesAction.php`.

#### [MODIFY] [ListCategoriesAction.php](/core/Admin/Controllers/Actions/Catalog/Category/ListCategoriesAction.php)
- Substituir a consulta SQL direta e paginação manual por métodos no `CategoryRepository` (`getCategoriesPaginated()`).

#### [MODIFY] [CategoryRepository.php](/core/Model/Domain/Repositories/CategoryRepository.php)
- Adicionar o método de busca paginada e filtrada `getCategoriesPaginated()`.

#### [MODIFY] [CategoryMapper.php](/core/Mappers/EntityMappers/CategoryMapper.php)
- Adicionar a query via `QueryBuilder` no `CategoryMapper` para buscar categorias com seus respectivos pais e totais.

---

#### Catálogo / Fabricante (Manufacturer)
Refatorar completamente o CRUD de Fabricantes.

#### [MODIFY] [ListManufacturersAction.php](/core/Admin/Controllers/Actions/Catalog/Manufacturer/ListManufacturersAction.php)
- Delegar busca paginada de fabricantes ao `ManufacturerRepository`.

#### [MODIFY] [CreateManufacturerAction.php](/core/Admin/Controllers/Actions/Catalog/Manufacturer/CreateManufacturerAction.php)
- Renderizar a view de criação via `BaseController`.

#### [MODIFY] [StoreManufacturerAction.php](/core/Admin/Controllers/Actions/Catalog/Manufacturer/StoreManufacturerAction.php)
- Mover a persistência e upload de arquivo para `ManufacturerRepository::createManufacturer()`.

#### [MODIFY] [EditManufacturerAction.php](/core/Admin/Controllers/Actions/Catalog/Manufacturer/EditManufacturerAction.php)
- Buscar o fabricante via `ManufacturerRepository::getManufacturerForEdit()`.

#### [MODIFY] [UpdateManufacturerAction.php](/core/Admin/Controllers/Actions/Catalog/Manufacturer/UpdateManufacturerAction.php)
- Atualizar via `ManufacturerRepository::updateManufacturer()`.

#### [MODIFY] [DeleteManufacturerAction.php](/core/Admin/Controllers/Actions/Catalog/Manufacturer/DeleteManufacturerAction.php)
- Deletar via `ManufacturerRepository::deleteManufacturer()`.

#### [MODIFY] [ManufacturerRepository.php](/core/Model/Domain/Repositories/ManufacturerRepository.php)
- Adicionar métodos de negócio: `getManufacturersPaginated()`, `getManufacturerForEdit()`, `createManufacturer()`, `updateManufacturer()`, `deleteManufacturer()`.

#### [MODIFY] [ManufacturerMapper.php](/core/Mappers/EntityMappers/ManufacturerMapper.php)
- Implementar métodos de banco usando `QueryBuilder`, `UnitOfWork` e `DAO`.

---

### FASE 2: Módulo de Catálogo — Produto (Product)

Refatorar o CRUD de Produtos.

#### [MODIFY] [ListProductsAction.php](/core/Admin/Controllers/Actions/Catalog/Product/ListProductsAction.php)
#### [MODIFY] [CreateProductAction.php](/core/Admin/Controllers/Actions/Catalog/Product/CreateProductAction.php)
#### [MODIFY] [EditProductAction.php](/core/Admin/Controllers/Actions/Catalog/Product/EditProductAction.php)
#### [MODIFY] [UpdateProductAction.php](/core/Admin/Controllers/Actions/Catalog/Product/UpdateProductAction.php)
#### [MODIFY] [DeleteProductAction.php](/core/Admin/Controllers/Actions/Catalog/Product/DeleteProductAction.php)
#### [MODIFY] [ProductRepository.php](/core/Model/Domain/Repositories/ProductRepository.php)
#### [MODIFY] [ProductMapper.php](/core/Mappers/EntityMappers/ProductMapper.php)

---

### FASE 3: Módulo de Vendas & Pedidos (Sales)

Refatorar a gestão de Pedidos e Devoluções.

#### [MODIFY] [ListOrdersAction.php](/core/Admin/Controllers/Actions/Sales/Order/ListOrdersAction.php)
#### [MODIFY] [ShowOrderAction.php](/core/Admin/Controllers/Actions/Sales/Order/ShowOrderAction.php)
#### [MODIFY] [ViewOrderDetailsAction.php](/core/Admin/Controllers/Actions/Sales/Order/ViewOrderDetailsAction.php)
#### [MODIFY] [UpdateOrderStatusAction.php](/core/Admin/Controllers/Actions/Sales/Order/UpdateOrderStatusAction.php)
#### [MODIFY] [ListReturnsAction.php](/core/Admin/Controllers/Actions/Sales/Return/ListReturnsAction.php)
#### [MODIFY] [ShowReturnAction.php](/core/Admin/Controllers/Actions/Sales/Return/ShowReturnAction.php)
#### [MODIFY] [UpdateReturnStatusAction.php](/core/Admin/Controllers/Actions/Sales/Return/UpdateReturnStatusAction.php)

---

### FASE 4: Módulo de Compras (Procurement / Supplier)

Refatorar o CRUD de Fornecedores.

#### [MODIFY] [ListSuppliersAction.php](/core/Admin/Controllers/Actions/Procurement/Supplier/ListSuppliersAction.php)
#### [MODIFY] [CreateSupplierAction.php](/core/Admin/Controllers/Actions/Procurement/Supplier/CreateSupplierAction.php)
#### [MODIFY] [StoreSupplierAction.php](/core/Admin/Controllers/Actions/Procurement/Supplier/StoreSupplierAction.php)
#### [MODIFY] [EditSupplierAction.php](/core/Admin/Controllers/Actions/Procurement/Supplier/EditSupplierAction.php)
#### [MODIFY] [UpdateSupplierAction.php](/core/Admin/Controllers/Actions/Procurement/Supplier/UpdateSupplierAction.php)
#### [MODIFY] [DeleteSupplierAction.php](/core/Admin/Controllers/Actions/Procurement/Supplier/DeleteSupplierAction.php)

---

### FASE 5: Módulo POS (Frente de Caixa)

Refatorar busca rápida de produtos, clientes e criação/pagamento de pré-pedidos no POS.

#### [MODIFY] [SearchCustomerAction.php](/core/Admin/Controllers/Actions/POS/SearchCustomerAction.php)
#### [MODIFY] [SearchProductAction.php](/core/Admin/Controllers/Actions/POS/SearchProductAction.php)
#### [MODIFY] [CreatePreOrderAction.php](/core/Admin/Controllers/Actions/POS/CreatePreOrderAction.php)
#### [MODIFY] [GetPreOrderAction.php](/core/Admin/Controllers/Actions/POS/GetPreOrderAction.php)
#### [MODIFY] [PayOrderAction.php](/core/Admin/Controllers/Actions/POS/PayOrderAction.php)

---

## Verification Plan

### Automated Tests
- Executar lint de PHP em todos os arquivos modificados:
  `php -l core/Admin/Controllers/Actions/...`
  `php -l core/Model/Domain/Repositories/...`
  `php -l core/Mappers/EntityMappers/...`
- Verificar a inexistência de chamadas `ConnectionDB::getInstance()` dentro da pasta `core/Admin/Controllers/Actions`:
  `grep -rn "ConnectionDB::getInstance" core/Admin/Controllers/Actions`

### Manual Verification
- Testar a navegação e operações no Painel Administrativo para os módulos refatorados.

# Checklist de Execução: Arquitetura sem SQL Solto no Admin

- [ ] **FASE 1: Módulo de Catálogo (Categoria & Fabricante)**
  - [x] Refatorar `ListCategoriesAction.php` e adicionar busca paginada em `CategoryRepository` e `CategoryMapper`
  - [x] Refatorar CRUD de Fabricantes:
    - [x] `ManufacturerMapper.php` (Adicionar métodos com QueryBuilder / DAO)
    - [x] `ManufacturerRepository.php` (Adicionar métodos de domínio)
    - [x] `ListManufacturersAction.php`
    - [x] `CreateManufacturerAction.php`
    - [x] `StoreManufacturerAction.php`
    - [x] `EditManufacturerAction.php`
    - [x] `UpdateManufacturerAction.php`
    - [x] `DeleteManufacturerAction.php`

- [x] **FASE 2: Módulo de Catálogo (Produto)**
  - [x] Refatorar CRUD de Produtos:
    - [x] `ProductMapper.php`
    - [x] `ProductRepository.php`
    - [x] `ListProductsAction.php`
    - [x] `CreateProductAction.php`
  - [x] Executar grep para garantir zero ocorrências de `ConnectionDB::getInstance()` em `core/Admin/Controllers/Actions`

# Resumo das Alterações - Implantação da Arquitetura sem SQL no Front/Admin

Concluímos a refatoração completa do painel administrativo do **Alpha Engine**, garantindo o alinhamento estrito com os diagramas arquiteturais em [architectureDiagram.puml](/docs/architecture/architectureDiagram.puml) e os princípios GoF.

Eliminamos **100%** das consultas SQL manuais, conexões diretas `ConnectionDB` e tabelas em hardcode (`DB_PREFIX`) de todas as **Single Action Controllers (`__invoke`)** do diretório `core/Admin/Controllers/Actions`.

---

## 🛠️ Alterações por Módulo

### 1. Catálogo - Categorias & Produtos
- **Categorias**:
  - [CategoryRepository.php](/core/Model/Domain/Repositories/CategoryRepository.php): Adicionados métodos de domínio `getAdminCategoriesPaginated`, `getAdminCategoryForEdit`, `saveAdminCategory`, `deleteAdminCategory`.
  - [CategoryMapper.php](/core/Mappers/EntityMappers/CategoryMapper.php): Adicionados métodos estocados de persistência com `QueryBuilder` desacoplado do DAO.
  - Refatorados: [ListCategoriesAction.php](/core/Admin/Controllers/Actions/Catalog/Category/ListCategoriesAction.php), [CreateCategoryAction.php](/core/Admin/Controllers/Actions/Catalog/Category/CreateCategoryAction.php), [EditCategoryAction.php](/core/Admin/Controllers/Actions/Catalog/Category/EditCategoryAction.php), [DeleteCategoryAction.php](/core/Admin/Controllers/Actions/Catalog/Category/DeleteCategoryAction.php).

- **Produtos**:
  - [ProductRepository.php](/core/Model/Domain/Repositories/ProductRepository.php): Adicionados métodos de domínio `getAdminProductsPaginated`, `getAdminProductForEdit`, `saveAdminProduct`, `deleteAdminProduct`.
  - [ProductMapper.php](/core/Mappers/EntityMappers/ProductMapper.php): Adicionada busca paginada e salvamento atômico de imagens e descrições do produto.
  - Refatorados: [ListProductsAction.php](/core/Admin/Controllers/Actions/Catalog/Product/ListProductsAction.php), [CreateProductAction.php](/core/Admin/Controllers/Actions/Catalog/Product/CreateProductAction.php), [EditProductAction.php](/core/Admin/Controllers/Actions/Catalog/Product/EditProductAction.php), [UpdateProductAction.php](/core/Admin/Controllers/Actions/Catalog/Product/UpdateProductAction.php), [DeleteProductAction.php](/core/Admin/Controllers/Actions/Catalog/Product/DeleteProductAction.php).

---

### 2. Vendas & Devoluções (Sales)
- **Pedidos**:
  - [OrderRepository.php](/core/Model/Domain/Repositories/OrderRepository.php) e [OrderMapper.php](/core/Mappers/EntityMappers/OrderMapper.php): Adicionado método `getAdminOrdersPaginated`.
  - Refatorado: [ListOrdersAction.php](/core/Admin/Controllers/Actions/Sales/Order/ListOrdersAction.php).
- **Devoluções (RMA)**:
  - [OrderReturnRepository.php](/core/Model/Domain/Repositories/OrderReturnRepository.php) e [OrderReturnMapper.php](/core/Mappers/EntityMappers/OrderReturnMapper.php): Adicionados métodos `getAdminReturnsPaginated`, `getAdminReturnDetails`, `getAdminReturnHistories`.
  - Refatorados: [ListReturnsAction.php](/core/Admin/Controllers/Actions/Sales/Return/ListReturnsAction.php), [ShowReturnAction.php](/core/Admin/Controllers/Actions/Sales/Return/ShowReturnAction.php), [UpdateReturnStatusAction.php](/core/Admin/Controllers/Actions/Sales/Return/UpdateReturnStatusAction.php).

---

### 3. Compras & Fornecedores (Procurement)
- Substituídas consultas manuais de Fabricantes e Países por `ManufacturerRepository` e `GeoCountryMapper`.
- Refatorados: [CreateSupplierAction.php](/core/Admin/Controllers/Actions/Procurement/Supplier/CreateSupplierAction.php), [StoreSupplierAction.php](/core/Admin/Controllers/Actions/Procurement/Supplier/StoreSupplierAction.php), [EditSupplierAction.php](/core/Admin/Controllers/Actions/Procurement/Supplier/EditSupplierAction.php), [UpdateSupplierAction.php](/core/Admin/Controllers/Actions/Procurement/Supplier/UpdateSupplierAction.php).

---

### 4. Frente de Caixa (POS) & Clientes
- **POS**:
  - [CustomerRepository.php](/core/Model/Domain/Repositories/CustomerRepository.php) e [CustomerMapper.php](/core/Mappers/EntityMappers/CustomerMapper.php): Adicionado método `searchActiveCustomers`.
  - Refatorado: [SearchCustomerAction.php](/core/Admin/Controllers/Actions/POS/SearchCustomerAction.php).
- **Clientes & Endereços**:
  - Refatorados: [ListCustomersAction.php](/core/Admin/Controllers/Actions/Customer/Customer/ListCustomersAction.php), [CreateCustomerAction.php](/core/Admin/Controllers/Actions/Customer/Customer/CreateCustomerAction.php), [EditCustomerAction.php](/core/Admin/Controllers/Actions/Customer/Customer/EditCustomerAction.php), [ShowCustomerAction.php](/core/Admin/Controllers/Actions/Customer/Customer/ShowCustomerAction.php), [CreateAddressAction.php](/core/Admin/Controllers/Actions/Customer/Address/CreateAddressAction.php), [EditAddressAction.php](/core/Admin/Controllers/Actions/Customer/Address/EditAddressAction.php).
- **Configurações da Loja**:
  - Refatorado: [UpdateStoreSettingAction.php](/core/Admin/Controllers/Actions/Setting/StoreSetting/UpdateStoreSettingAction.php).

---

## 🔍 Validações Realizadas

1. **Validação de Sintaxe PHP (`php -l`)**:
   - Todos os arquivos PHP criados/modificados foram validados via linter nativo do PHP sem nenhum erro detectado.
2. **Audit de Ocorrências de `ConnectionDB` e `DB_PREFIX`**:
   - `grep` em `core/Admin/Controllers/Actions`: **0 ocorrências** de `ConnectionDB` e **0 ocorrências** de `DB_PREFIX` em Controllers!

