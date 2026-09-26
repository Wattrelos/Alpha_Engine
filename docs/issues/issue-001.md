# CI Pipeline (PHPUnit + Behat + Playwright E2E)

### 🔍 Quais foram os verdadeiros erros no log do GitHub?

Analisando minuciosamente o log que você compartilhou, houve **3 causas reais** para os 5 erros e 2 falhas:

#### 1. Modo Estrito do MySQL 8.0 no Docker (`sql_mode`) — *(Causou os Erros 1, 2, 3 e 5)*
- **Erros 1 e 2 (`ONLY_FULL_GROUP_BY`)**: O container `mysql:8.0` vem por padrão com `sql_mode=only_full_group_by`. Como o `ProductMapper` agrupa por `p.id` e faz select de colunas da tabela de descrição `pd.name`, o MySQL 8 padrão rejeita a consulta com o erro `1055: Expression #37 of SELECT list is not in GROUP BY clause`.
- **Erros 3 e 5 (`NO_ZERO_DATE`)**: O MySQL 8 padrão rejeita datas com valor `'0000-00-00'` (`Incorrect DATE value: '0000-00-00'`), comum em verificações de `date_available`.
- **No ambiente local**: O seu MySQL local tem essas restrições desativadas (`STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION`), por isso no seu computador passava normalmente.

#### 2. Coluna Inexistente no teste `ProductValidationTest` — *(Causou o Erro 4)*
- No arquivo [`ProductValidationTest.php`](/tests/Validation/ProductValidationTest.php#L94), se a tabela `agsc_category` estiver vazia, o teste tentava criar uma categoria de fallback com:
  ```php
  INSERT INTO agsc_category (parent_id, sort_order, status, date_added, date_modified) ...
  ```
- Só que a tabela `agsc_category` **não possui** as colunas `date_added` e `date_modified`. Localmente isso nunca falhou porque o banco já tinha categorias salvas. No GitHub, o banco começa limpo, ativando essa linha e causando `1054 Unknown column 'date_added'`.

#### 3. Tabela `agsc_user` Vazia em Banco Limpo — *(Causou as Falhas 1 e 2)*
- O [`AdminSessionMiddleware.php`](/backend/core/Auth/Middleware/AdminSessionMiddleware.php#L97) possui uma regra de segurança: se a tabela de administradores estiver completamente vazia (`count === 0`), ele redireciona a requisição para a tela de instalação `/setup` com **HTTP 302**.
- Os testes de RBAC (`AuditLogValidationTest` e `RbacAccessControlTest`) esperavam receber **HTTP 403 Forbidden** (usuário sem permissão). Como no GitHub Actions a tabela estava vazia, o middleware retornou **302** em vez de 403.

---

### 🛠️ Correções Aplicadas

1. **Compatibilidade Global e de Sessão com MySQL 8**:
   - Em [`ConnectionDB.php`](/backend/core/Model/DataAccessObject/ConnectionDB.php#L44), adicionamos:
     ```php
     $this->connection->exec("SET SESSION sql_mode = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");
     ```
   - No workflow [`.github/workflows/playwright.yml`](/.github/workflows/playwright.yml#L64), definimos o `sql_mode` global logo após subir o MySQL.
2. **Correção do `ProductValidationTest.php`**:
   - Ajustamos o insert de categoria em [`ProductValidationTest.php`](/tests/Validation/ProductValidationTest.php#L94) para inserir apenas os campos reais (`parent_id`, `sort_order`, `status`).
3. **Criação do Seed de Testes (`seed_ci.sql`)**:
   - Criamos [`backend/resources/schema/seed_ci.sql`](/backend/resources/schema/seed_ci.sql) que insere:
     - 1 Administrador Super User (evitando o redirect 302 para `/setup`).
     - 1 Categoria base e 1 Produto base ("Adaptador Tubo Cerâmica 100mm") com ID 1.
   - O [`.github/workflows/playwright.yml`](/.github/workflows/playwright.yml#L68) agora roda esse seed logo após o `install.sql`.

---

### ✅ Resultado Local

- **PHPUnit**: `105 testes, 386 asserções, 0 erros, 0 falhas`
- **Behat**: `104 cenários executados com sucesso`

Basta fazer o commit dessas alterações e dar `git push` para que a pipeline execute e fique verde no GitHub Actions!