# DP-58: Ajuste de Isolamento Multi-tenant (`store_id = 1`)

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-07-30 11:14:14
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/58

## Descrição

# Plano de Implementação - Ajuste de Isolamento Multi-tenant (`store_id = 1`)

Conforme documentado em [`docs/anti_patterns.md`](file:///var/www/html/agsonhos/docs/anti_patterns.md), a Alpha Engine eliminou o anti-pattern da "loja 0" do legado OpenCart (que violava a integridade referencial com a tabela `tbkk_store`). Na Alpha Engine, a loja principal possui obrigatoriamente **`store_id = 1`**.

O objetivo desta tarefa é alinhar o [`CartRepository.php`](file:///var/www/html/agsonhos/core/Model/Domain/Repositories/CartRepository.php) e os mappers/repositórios correlatos para utilizar o padrão `store_id = 1`, garantindo a proteção contra acesso cross-tenant e corrigindo divergências no método `getStoreId()`.

## Core Architectural Rules (Padrão Alpha Engine)

1. **Loja Principal (`store_id = 1`)**: A loja principal registrada no banco `tbkk_store` possui ID 1. O fallback padrão para resolução de loja quando não informada é `1`.
2. **Resolução Única via `AbstractRepository`**: Todos os repositórios que estendem `AbstractRepository` devem utilizar a resolução centralizada `$this->getStoreId()`, que busca dinamicamente o ID da loja a partir do container PSR-11 (`storeId`, `configSettings`, `config` ou header `HTTP_X_STORE_ID`) e retorna `1` como fallback.
3. **Isolamento de Mutação (`CartMapper`)**: Operações de mutação e exclusão no carrinho (`updateItem`, `removeItem`) devem exigir a validação de `store_id` além do `cart_id`, impedindo que uma requisição em um tenant altere ou remova itens de outro tenant.

---

## Proposed Changes

### Core Repository & Mapper Layer

#### [MODIFY] [AbstractRepository.php](file:///var/www/html/agsonhos/core/Model/Domain/Repositories/AbstractRepository.php)
- Atualizar a resolução mágica de `store_id` para:
  1. Verificar `$this->container->get('storeId')`.
  2. Verificar `$this->container->get('configSettings')['config_store_id']`.
  3. Verificar `$this->container->get('config')->get('config_store_id')`.
  4. Verificar `$_SERVER['HTTP_X_STORE_ID']`.
  5. Retornar `1` (Loja Principal) como fallback padrão.

#### [MODIFY] [CartRepository.php](file:///var/www/html/agsonhos/core/Model/Domain/Repositories/CartRepository.php)
- Remover o método privado conflitante `private function getStoreId(): int` (linhas 59-66) para que o `CartRepository` herde e utilize publicamente o `AbstractRepository::getStoreId()`.
- Atualizar `update()` e `remove()` para repassar o `$this->getStoreId()` ao mapper.

#### [MODIFY] [CartMapper.php](file:///var/www/html/agsonhos/core/Mappers/EntityMappers/CartMapper.php)
- Adicionar o parâmetro `$storeId` e a cláusula `->where('store_id = ?', [$storeId])` nos métodos `updateItem()` e `removeItem()`.

#### [MODIFY] [CustomerRepository.php](file:///var/www/html/agsonhos/core/Model/Domain/Repositories/CustomerRepository.php)
- No método `registerCustomer()`, utilizar `$this->getStoreId()` para obter o ID da loja ativa.

#### [MODIFY] [SettingRepository.php](file:///var/www/html/agsonhos/core/Model/Domain/Repositories/SettingRepository.php)
- Ajustar os fallbacks de `getSettings()`, `getSetting()` e `getValue()` para usar `$this->getStoreId()` (loja 1 padrão).

#### [MODIFY] [LayoutRepository.php](file:///var/www/html/agsonhos/core/Model/Domain/Repositories/LayoutRepository.php) & [SitemapRepository.php](file:///var/www/html/agsonhos/core/Model/Domain/Repositories/SitemapRepository.php)
- Substituir acessos diretos `$this->config->get('config_store_id')` por `$this->getStoreId()`.

#### [MODIFY] [ThemeRepository.php](file:///var/www/html/agsonhos/core/Model/Domain/Repositories/ThemeRepository.php) & [ThemeMapper.php](file:///var/www/html/agsonhos/core/Mappers/EntityMappers/ThemeMapper.php)
- Passar `$this->getStoreId()` de `ThemeRepository` para `ThemeMapper::getTheme()`.

### Controllers & Actions

#### [MODIFY] [BaseController.php](file:///var/www/html/agsonhos/core/Controller/BaseController.php)
- Garantir que `$this->storeId` seja inicializado via `$settings['config_store_id'] ?? 1`.

#### [MODIFY] [SubmitCheckoutAction.php](file:///var/www/html/agsonhos/core/Controller/Actions/Cart/SubmitCheckoutAction.php)
- Garantir que `orderData['store_id']` seja resolvido com fallback `1`.

### Verification & Tests

#### [MODIFY] [teste_tenant_isolation.php](file:///var/www/html/agsonhos/tests/security_tests/teste_tenant_isolation.php)
- Adicionar testes automatizados para verificar:
  - Resolução de `store_id = 1` como loja padrão.
  - Bloqueio de mutação cross-tenant em `CartMapper` (impedir que Loja 1 altere/remova item da Loja 2).

---

## Verification Plan

### Automated Tests
- Executar os testes de isolamento de tenant:
  ```bash
  php tests/security_tests/teste_tenant_isolation.php
  ```
- Executar os testes de registro de cliente:
  ```bash
  php tests/scripts_uteis/test_customer_registration.php
  ```

### Manual Verification
- Validar se o `CartRepository` resolve e isola corretamente carrinhos e itens por `store_id`.

# Lista de Tarefas - Isolamento Multi-tenant (`store_id = 1`)

- [x] Atualizar `AbstractRepository.php` para resolver `store_id` (container storeId, configSettings, config, HTTP header) com fallback `1`
- [x] Ajustar `CartRepository.php` (remover `private function getStoreId()` duplicado e usar `AbstractRepository::getStoreId()`)
- [x] Atualizar `CartMapper.php` para incluir `store_id` em `updateItem()` e `removeItem()`
- [x] Atualizar chamadas em `CartRepository::update()` e `CartRepository::remove()` para passar `$this->getStoreId()`
- [x] Atualizar `CustomerRepository.php` no método `registerCustomer()` para usar `$this->getStoreId()`
- [x] Ajustar `SettingRepository.php` (`$loadedStoreId = -1`, fallbacks usando `$this->getStoreId()`)
- [x] Ajustar `LayoutRepository.php` e `SitemapRepository.php` para usar `$this->getStoreId()`
- [x] Ajustar `ThemeRepository.php` e `ThemeMapper.php` para repassar `store_id`
- [x] Ajustar `BaseController.php` e `SubmitCheckoutAction.php` para fallback `1`
- [x] Atualizar e rodar os testes em `tests/security_tests/teste_tenant_isolation.php`

# Walkthrough - Alinhamento do Isolamento Multi-tenant (`store_id = 1`)

Concluímos com sucesso o alinhamento da proteção de **Isolamento Multi-tenant (`store_id = 1`)** no [CartRepository.php](file:///var/www/html/agsonhos/core/Model/Domain/Repositories/CartRepository.php) e na arquitetura geral de repositórios e mappers da **Alpha Engine**.

## Alterações Realizadas

### 1. Camada Base & Resolução Única de Loja
- **[AbstractRepository.php](file:///var/www/html/agsonhos/core/Model/Domain/Repositories/AbstractRepository.php)**:
  - Atualizado `__get('store_id')` e `getStoreId()` para buscar o ID da loja dinamicamente no container PSR-11 (`storeId`, `configSettings`, `config` ou header `HTTP_X_STORE_ID`).
  - Definido `1` (loja principal ancorada em `tbkk_store`) como fallback padrão de resolução.

### 2. CartRepository & CartMapper
- **[CartRepository.php](file:///var/www/html/agsonhos/core/Model/Domain/Repositories/CartRepository.php)**:
  - Removido o método privado duplicado `private function getStoreId(): int` (linhas 59-66) para que o repositório herde o método público `AbstractRepository::getStoreId()`.
  - Atualizadas as chamadas em `update()` e `remove()` para enviar `$this->getStoreId()` ao mapper.
- **[CartMapper.php](file:///var/www/html/agsonhos/core/Mappers/EntityMappers/CartMapper.php)**:
  - Adicionada a cláusula `store_id = ?` e parâmetro `$storeId` nos métodos `updateItem()` e `removeItem()`, bloqueando tentativas de modificação ou exclusão de itens de carrinho cross-tenant.

### 3. Repositórios e Mappers Correlatos
- **[CustomerRepository.php](file:///var/www/html/agsonhos/core/Model/Domain/Repositories/CustomerRepository.php)**: `registerCustomer()` ajustado para ler `$this->getStoreId()`.
- **[SettingRepository.php](file:///var/www/html/agsonhos/core/Model/Domain/Repositories/SettingRepository.php)**: `$loadedStoreId` inicializado em `-1` para permitir recarga limpa da loja 1; métodos `getSettings()`, `getSetting()`, `getValue()` e `editSetting()` utilizando `$this->getStoreId()`.
- **[LayoutRepository.php](file:///var/www/html/agsonhos/core/Model/Domain/Repositories/LayoutRepository.php)** & **[SitemapRepository.php](file:///var/www/html/agsonhos/core/Model/Domain/Repositories/SitemapRepository.php)**: Acessos diretos de `config_store_id` substituídos por `$this->getStoreId()`.
- **[ThemeRepository.php](file:///var/www/html/agsonhos/core/Model/Domain/Repositories/ThemeRepository.php)** & **[ThemeMapper.php](file:///var/www/html/agsonhos/core/Mappers/EntityMappers/ThemeMapper.php)**: Repasse explícito de `$this->getStoreId()` para `ThemeMapper::getTheme()`.

### 4. Controllers & Actions
- **[BaseController.php](file:///var/www/html/agsonhos/core/Controller/BaseController.php)**: `$this->storeId` resolvido via `$container->get('storeId') ?? $settings['config_store_id'] ?? 1`.
- **[SubmitCheckoutAction.php](file:///var/www/html/agsonhos/core/Controller/Actions/Cart/SubmitCheckoutAction.php)**: `orderData['store_id']` resolvido via container com fallback `1`.

---

## Verificação e Testes Executados

### 1. Suíte de Isolamento Multi-tenant
Executado o script [`teste_tenant_isolation.php`](file:///var/www/html/agsonhos/tests/security_tests/teste_tenant_isolation.php):
```bash
php tests/security_tests/teste_tenant_isolation.php
```
**Resultado:**
- ✅ Resolução de `store_id` via `AbstractRepository` funcionou com valor 1.
- ✅ Acesso cross-tenant a clientes de outras lojas bloqueado com sucesso (`NULL`).
- ✅ `OrderRepository` e `CartRepository` aplicam e herdam `store_id = 1`.
- ✅ `CartMapper::updateItem` repassa o parâmetro `store_id = 1` para impedir atualizações em lojas paralelas.

### 2. Teste de Registro de Clientes
Executado o script [`test_customer_registration.php`](file:///var/www/html/agsonhos/tests/scripts_uteis/test_customer_registration.php):
```bash
php tests/scripts_uteis/test_customer_registration.php
```
**Resultado:**
- ✅ Registro e hidratação de cliente validados com `StoreId: 1`.

