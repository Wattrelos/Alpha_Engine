# 📊 Análise de Viabilidade: Migração de PKs e FKs para BIGINT

Este documento apresenta a análise de viabilidade técnica para a alteração dos tipos das Chaves Primárias (PKs) e Chaves Estrangeiras (FKs) de `INT(11)` para `BIGINT` no banco de dados da **Alpha Engine**.

---

## 🔍 1. Comportamento no PHP 8.4 (Camada de Domínio e ORM)

No PHP, o tipo `int` é assinado (signed) e o seu tamanho depende da arquitetura do processador e do sistema operacional da máquina onde o runtime é executado:
*   **Arquiteturas de 64 bits (Padrão de Mercado/Produção)**: O tipo `int` do PHP possui 64 bits de largura (representando valores de $-2^{63}$ a $2^{63}-1$). Isso é exatamente idêntico ao tipo `BIGINT` assinado do MySQL.
*   **Hidratação do DAO**: Como visto no [DataAccessObject](file:///var/www/html/agsonhos/core/Model/DataAccessObject/DataAccessObject.php#L751-L761), o ORM nativo faz coerção explícita de tipos utilizando cast para `(int)` na leitura e gravação de chaves:
    ```php
    $id = (int)($row['id'] ?? 0);
    ```
    Em plataformas de 64 bits, esta conversão não causa nenhuma perda de precisão ou truncamento, sendo 100% transparente para as Entidades de Domínio.

---

## 💾 2. Comportamento no MySQL / MariaDB (Camada de Banco de Dados)

### Diferenças de Armazenamento e Performance:
*   **INT (32-bit)**: Ocupa **4 bytes** por registro. Limite máximo de `2.147.483.647` (assinado).
*   **BIGINT (64-bit)**: Ocupa **8 bytes** por registro. Limite máximo de `9.223.372.036.854.775.807` (assinado).

### Impacto Físico e Operacional:
1.  **Espaço em Disco e Memória**: O dobro de espaço para as colunas de ID. Em bases pequenas e médias, o impacto é insignificante (alguns megabytes extras). Em tabelas gigantescas (como logs ou históricos com milhões de registros), pode haver um aumento notável no tamanho dos índices das chaves primárias e estrangeiras, ocupando mais espaço no Pool de Buffer do InnoDB.
2.  **Operações de Migração (DDL)**:
    *   **Bloqueio de Tabelas**: Alterar colunas que são chaves primárias ou que possuem relacionamentos ativos de chave estrangeira (`FOREIGN KEY`) exige desativar temporariamente a checagem de chaves (`SET FOREIGN_KEY_CHECKS = 0;`), alterar tanto a tabela pai quanto a filha, e depois reativar as checagens.
    *   **In-Place vs Copy**: Dependendo da versão do MySQL/MariaDB, a conversão de `INT` para `BIGINT` pode exigir reconstruir a tabela inteira, o que pode causar travamento (locks) de leitura/escrita em bases grandes sob tráfego concorrente.

---

## 🌐 3. Comportamento na Camada de Apresentação (JSON / JavaScript)

*   **Limitação do JavaScript**: O JavaScript utiliza o padrão IEEE 754 de ponto flutuante de precisão dupla para representar números (`Number`). O maior inteiro seguro que pode ser representado em JS sem perda de precisão é `9.007.199.254.740,991` (`Number.MAX_SAFE_INTEGER`).
*   **Implicação Prática**: Se as chaves autoincrementais crescerem sequencialmente (1, 2, 3...), essa limitação não causará qualquer impacto por centenas de anos. No entanto, se o sistema adotasse IDs gerados aleatoriamente com largura total de 64 bits (ex: Snowflake IDs de alta escala), as chaves passadas via JSON para o frontend perderiam a precisão ao serem convertidas em objetos JS, exigindo tráfego de IDs como strings.

---

## ⚖️ Veredito: É seguro migrar?

> [!TIP]
> **Sim, a migração é 100% segura e recomendável** sob a perspectiva do código PHP da **Alpha Engine**.

### ⚠️ Recomendações Críticas para a Migração (Plano de Ação):
1.  **Ordem de Execução**: Sempre altere primeiro as tabelas filhas (que hospedam as FKs) e depois as tabelas pai (que hospedam as PKs) ou desative as checagens antes de iniciar a alteração:
    ```sql
    SET FOREIGN_KEY_CHECKS = 0;
    ALTER TABLE oc_product MODIFY product_id BIGINT AUTO_INCREMENT;
    ALTER TABLE oc_product_description MODIFY product_id BIGINT;
    -- (repetir para todas as relações)
    SET FOREIGN_KEY_CHECKS = 1;
    ```
2.  **Manter Consistência de Tipos**: Uma PK do tipo `BIGINT` que se relaciona com uma FK do tipo `INT` causará erros de syntax/compatibilidade e impedirá a integridade referencial. Todas as colunas relacionadas devem ser migradas juntas.
3.  **Ambiente de Homologação**: Execute a migração em um clone da base em desenvolvimento/homologação para aferir o tempo exato de travamento das tabelas antes de aplicar em ambiente de produção.
