<?php

namespace Alpha\Model\DataAccessObject;

use PDO;
use Exception;
use Illuminate\Support\LazyCollection;
use PDOException;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use Alpha\Model\DataAccessObject\ConnectionDB;
use Alpha\Model\Domain\InterfaceEntity;
use Alpha\Model\DataAccessObject\QueryBuilder;

/**
 * Refere-se a DataAccessObject.java
 * Adaptado para PHP 8.4.16
 * 
 * Melhoras Alpha Engine:
 * - Identity Map (Static): Garante uma única instância por ID por requisição.
 */
class DataAccessObject
{
    private string $tablePrefix;
    private static array $identityMap = [];

    public function __construct()
    {
        $this->tablePrefix = (DB_PREFIX ?? 'table_');
    }

    /**
     * Alpha Engine: Previne Memory Leaks em Daemons e Cron Jobs.
     * Limpa o Identity Map estático para permitir que o Garbage Collector do PHP libere a RAM.
     */
    public static function clearIdentityMap(): void
    {
        self::$identityMap = [];
    }

    /**
     * Executa a estrutura gerada pelo QueryBuilder
     */
    public function executeQuery(QueryBuilder $builder): array
    {

        $conn = ConnectionDB::getInstance()->getConnection();
        $stmt = $conn->prepare($builder->getSQL());

        try {
            $startTime = microtime(true);
            $stmt->execute($builder->getParams());
            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Prepara a amostra da primeira linha para auditar nomenclatura do ORM (Crash-Proof)
            $rowCount = count($results);
            $sample = [];
            if ($rowCount > 0) {
                foreach ($results[0] as $col => $val) {
                    $sample[$col] = (is_string($val) && strlen($val) > 150) ? substr($val, 0, 150) . '...[TRUNC]' : $val;
                }
            }

            $this->logDebugQuery($builder->getSQL(), $builder->getParams() ?? [], $startTime, $rowCount, $sample);
            return $results;
        } catch (\PDOException $e) {
            $this->logDebugQuery("[ERRO SQL] " . $e->getMessage() . " | " . $builder->getSQL(), $builder->getParams() ?? []);
            throw $e;
        }
    }

    /**
     * Alpha Engine: Executa comandos de escrita (DELETE, UPDATE, INSERT) de forma atômica
     * através do QueryBuilder, retornando o status de sucesso da operação.
     */
    public function execute(QueryBuilder $builder): bool
    {
        $conn = ConnectionDB::getInstance()->getConnection();
        $stmt = $conn->prepare($builder->getSQL());
        return $stmt->execute($builder->getParams());
    }

    /**
     * Alpha Engine: Executa uma instrução SQL bruta com parâmetros.
     * Útil para comandos customizados de escrita (como INSERT ... ON DUPLICATE KEY UPDATE).
     * 
     * @param string $sql
     * @param array $params
     * @return bool
     */
    public function executeRawSQL(string $sql, array $params = []): bool
    {
        $conn = ConnectionDB::getInstance()->getConnection();
        $stmt = $conn->prepare($sql);

        try {
            $startTime = microtime(true);
            $result = $stmt->execute($params);

            $rowCount = $stmt->rowCount();
            $this->logDebugQuery($sql, $params, $startTime, $rowCount, ['action' => 'RAW_WRITE']); // Para debug

            return $result;
        } catch (\PDOException $e) {
            $this->logDebugQuery("[ERRO RAW SQL] " . $e->getMessage() . " | " . $sql, $params); // Para debug
            throw $e;
        }
    }

    /**
     * Executa a query de contagem e retorna o total absoluto de linhas
     */
    public function executeCount(QueryBuilder $builder): int
    {
        $conn = ConnectionDB::getInstance()->getConnection();
        $stmt = $conn->prepare($builder->getCountSQL());
        $stmt->execute($builder->getParams());
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return (int)($result['total'] ?? 0);
    }

    // ---------------------------------------------------------------------------------------------------
    // Método create

    public function create(InterfaceEntity $entity): ?int
    {
        $hierarchy = $this->getEntityHierarchy(get_class($entity));
        $lastId = null;
        $managedTransaction = false;

        try {
            $conn = ConnectionDB::getInstance()->getConnection();

            if (!$conn->inTransaction()) {
                $conn->beginTransaction();
                $managedTransaction = true;
            }

            foreach ($hierarchy as $clazz) {
                $lastId = $this->insertForClass($conn, $entity, $clazz, $lastId);
            }

            $entity->setId($lastId);
            $this->addToIdentityMap($entity);
            $this->saveAssociations($conn, $entity);

            if ($managedTransaction) {
                $conn->commit();
            }
            return $lastId;
        } catch (Exception $e) {
            if ($conn && $conn->inTransaction()) {
                $conn->rollBack();
                error_log("Rollback executado devido a: " . $e->getMessage());
            }
            error_log($e->getTraceAsString());
            return null;
        }
    }

    private function insertForClass(\PDO $conn, InterfaceEntity $entity, string $clazz, ?int $parentId): ?int
    {
        $columnsMap = [];

        if ($parentId !== null) {
            $columnsMap['id'] = $parentId;
        }

        $reflection = new ReflectionClass($clazz);
        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
            $name = $method->getName();
            if ($this->isGetter($method)) {

                // Em PHP, verificamos se o retorno seria um array (coleção)
                // Como PHP não tem tipos genéricos fortes em runtime para arrays, 
                // dependemos da lógica de negócio ou PHPDoc, mas aqui simplificamos:
                if ($name === 'getId') continue;

                $value = $method->invoke($entity);

                if (is_array($value)) continue;

                $prefixLength = str_starts_with($name, 'is') ? 2 : 3;
                $colName = $this->convertPascalCaseToSnakeCase(substr($name, $prefixLength));

                $returnType = $method->getReturnType();
                $returnTypeName = $returnType instanceof \ReflectionNamedType ? $returnType->getName() : null;

                // Aplica Sufixo _id se o valor for Entidade ou se a Assinatura do Método garantir que é uma Entidade
                if ($value instanceof InterfaceEntity || ($returnTypeName && is_subclass_of($returnTypeName, InterfaceEntity::class))) {
                    $colName .= "_id";
                    $value = $value ? $value->getId() : null;
                }

                // Alpha Engine: Desduplicação de colunas e Resolução de Conflitos FK vs Objeto Nulo.
                if (!array_key_exists($colName, $columnsMap) || $value !== null) {
                    $columnsMap[$colName] = $value;
                }
            }
        }

        $attempts = 0;
        while ($attempts < 5) {
            $columns = [];
            $placeholders = [];
            $values = [];

            foreach ($columnsMap as $col => $val) {
                $columns[] = "`$col`";
                $placeholders[] = "?";

                if ($val instanceof \DateTimeInterface) {
                    $val = $val->format('Y-m-d H:i:s');
                }
                $values[] = is_bool($val) ? (int)$val : $val;
            }

            $sql = $this->buildInsertSql($clazz, $columns, $placeholders);

            try {
                $startTime = microtime(true);
                $stmt = $conn->prepare($sql);
                $stmt->execute($values);

                $insertId = $parentId ?? (int)$conn->lastInsertId();

                $this->logDebugQuery($sql, $values, $startTime, $insertId, ['action' => 'CREATE']); // Para debug
                return $insertId;
            } catch (PDOException $e) {
                // Auto-Healing: Resiliência contra colunas legadas inexistentes (ex: address_id em bancos migrados)
                if ($e->getCode() == '42S22' && preg_match('/Unknown column \'([^\']+)\'/', $e->getMessage(), $matches)) {
                    $missingCol = trim($matches[1], '`');
                    if (array_key_exists($missingCol, $columnsMap)) {
                        error_log("Alpha Engine [Auto-Healing]: Coluna '{$missingCol}' ausente no BD. Ignorando e reprocessando a Inserção...");
                        unset($columnsMap[$missingCol]);
                        $attempts++;
                        continue;
                    }
                }

                $this->logDebugQuery("[ERRO SQL CREATE] " . $e->getMessage() . " | " . $sql, $values); // Para debug
                error_log("Erro na tabela " . $reflection->getShortName() . ": " . $e->getMessage());

                if ($e->getCode() == 23000 || (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1062)) {
                    throw new \DomainException("Alpha Engine [Violação de Integridade]: O banco de dados rejeitou a operação pois um registro com estes dados únicos (ex: e-mail) já existe.", 1062, $e);
                }
                throw $e;
            }
        }
        return null;
    }

    private function getEntityHierarchy(string $startClass): array
    {
        $hierarchy = [];
        $current = $startClass;

        while ($current && is_subclass_of($current, InterfaceEntity::class)) {
            $reflection = new ReflectionClass($current);
            // Ignora classes abstratas (como BaseEntity) pois elas não possuem tabelas próprias
            if (!$reflection->isAbstract()) {
                array_unshift($hierarchy, $current);
            }
            $current = get_parent_class($current);
        }
        return $hierarchy;
    }

    // ---------------------------------------------------------------------------------------------------
    // Associações

    private function saveAssociations(\PDO $conn, InterfaceEntity $instance): void
    {
        $reflection = new ReflectionClass($instance);
        foreach ($reflection->getProperties() as $property) {
            // Em PHP 8+, usamos Attributes em vez de Anotações Java
            $attrs = $property->getAttributes();
            $isOneToMany = false;
            $isManyToMany = false;
            $targetEntity = null;
            $foreignKey = null;

            foreach ($attrs as $attr) {
                if (str_ends_with($attr->getName(), 'OneToMany')) $isOneToMany = true;
                if (str_ends_with($attr->getName(), 'ManyToMany')) $isManyToMany = true;
                // Assume-se que o atributo guarda o tipo da classe filha
                $args = $attr->getArguments();
                $targetEntity = $args['targetEntity'] ?? null;
                $foreignKey = $args['foreignKey'] ?? null;
            }

            if (!$isOneToMany && !$isManyToMany) continue;

            $property->setAccessible(true);
            $collection = $property->getValue($instance);

            if (!is_array($collection) || empty($collection)) continue;

            foreach ($collection as $item) {
                if (!$item instanceof InterfaceEntity) continue;

                if ($isOneToMany) {
                    // Normaliza o nome da FK para PascalCase para encontrar o setter (ex: customer_id -> CustomerId)
                    $fkName = $foreignKey ?? ($reflection->getShortName() . "Id");
                    $fkNamePascal = str_replace('_', '', ucwords($fkName, '_'));
                    $fkSetterName = "set" . ucfirst($fkNamePascal);

                    if (method_exists($item, $fkSetterName)) {
                        $item->$fkSetterName($instance->getId());
                        if ($item->getId() === null || $item->getId() <= 0) {
                            $this->saveRecursiveInTransaction($conn, $item);
                        }
                    }
                } elseif ($isManyToMany) {
                    if ($item->getId() === null || $item->getId() <= 0) {
                        $this->saveRecursiveInTransaction($conn, $item);
                    }

                    $tableLink = $this->tablePrefix . $this->convertPascalCaseToSnakeCase($reflection->getShortName()) .
                        "_" . $this->convertPascalCaseToSnakeCase((new ReflectionClass($item))->getShortName());

                    $fkParent = $this->convertPascalCaseToSnakeCase($reflection->getShortName()) . "_id";
                    $fkChild = $this->convertPascalCaseToSnakeCase((new ReflectionClass($item))->getShortName()) . "_id";

                    $sqlM2M = "INSERT IGNORE INTO `$tableLink` (`$fkParent`, `$fkChild`) VALUES (?, ?)";
                    $conn->prepare($sqlM2M)->execute([$instance->getId(), $item->getId()]);
                }
            }
        }
    }

    private function saveRecursiveInTransaction(\PDO $conn, InterfaceEntity $entity): int
    {
        $hierarchy = $this->getEntityHierarchy(get_class($entity));
        $lastId = $entity->getId();

        if ($lastId !== null && $lastId > 0) return $lastId;

        foreach ($hierarchy as $clazz) {
            $lastId = $this->insertForClass($conn, $entity, $clazz, $lastId);
        }

        $entity->setId($lastId);
        $this->saveAssociations($conn, $entity);
        return $lastId;
    }

    // ---------------------------------------------------------------------------------------------------
    // Método read

    public function read(InterfaceEntity $entity): array
    {
        $listEntity = [];
        $clazz = get_class($entity);
        $reflection = new ReflectionClass($clazz);

        $where = $this->buildWhereClause($entity);
        $tableName = $this->tablePrefix . $this->convertPascalCaseToSnakeCase($reflection->getShortName());
        $sql = "SELECT * FROM `$tableName` " . $where;

        try {
            $conn = ConnectionDB::getInstance()->getConnection();
            $stmt = $conn->query($sql);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($rows as $row) {
                $id = (int)($row['id'] ?? 0);
                $instance = $this->getFromIdentityMap($clazz, $id);

                if (!$instance) {
                    $instance = $reflection->newInstance();
                    $instance->setId($id);
                    $this->addToIdentityMap($instance);
                    $this->fillEntityRecursively($instance, $clazz, $row, $conn);
                    $this->processAssociations($instance, $conn);
                }
                $listEntity[] = $instance;
            }
        } catch (Exception $e) {
            error_log("Erro ao ler entidade: " . $e->getMessage());
        }
        return $listEntity;
    }

    /**
     * Alpha Engine: Hidrata um array bruto de banco de dados para uma Entidade rica,
     * respeitando estritamente o Identity Map, Associações e Lazy Loading.
     * Centraliza a construção para paginate(), search(), findAll() e findById() dos Mappers.
     */
    public function hydrate(string $className, array $row): InterfaceEntity
    {
        $id = (int)($row['id'] ?? 0);
        $instance = $this->getFromIdentityMap($className, $id);

        if (!$instance) {
            $reflection = new ReflectionClass($className);
            $instance = $reflection->newInstance();
            $instance->setId($id);
            $this->addToIdentityMap($instance);

            $conn = ConnectionDB::getInstance()->getConnection();
            $this->fillEntityRecursively($instance, $className, $row, $conn);
            $this->processAssociations($instance, $conn);
        }

        return $instance;
    }

    /**
     * Carrega múltiplas entidades de uma vez por seus IDs.
     * Reduz o overhead de múltiplas chamadas ao banco para listagens.
     */
    public function readByIds(string $className, array $ids): array
    {
        if (empty($ids)) return [];

        $results = [];
        $toFetchIds = [];

        foreach ($ids as $id) {
            $cached = $this->getFromIdentityMap($className, (int)$id);
            if ($cached) {
                $results[] = $cached;
            } else {
                $toFetchIds[] = (int)$id;
            }
        }

        if (empty($toFetchIds)) return $results;

        $reflection = new ReflectionClass($className);
        $tableName = $this->tablePrefix . $this->convertPascalCaseToSnakeCase($reflection->getShortName());
        $placeholders = implode(',', array_fill(0, count($toFetchIds), '?'));
        $sql = "SELECT * FROM `$tableName` WHERE id IN ($placeholders)";

        try {
            $conn = ConnectionDB::getInstance()->getConnection();
            $stmt = $conn->prepare($sql);
            $stmt->execute($toFetchIds);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($rows as $row) {
                $instance = $reflection->newInstance();
                $instance->setId((int)($row['id'] ?? 0));
                $this->addToIdentityMap($instance);
                $this->fillEntityRecursively($instance, $className, $row, $conn);
                $this->processAssociations($instance, $conn);
                $results[] = $instance;
            }
        } catch (Exception $e) {
            error_log("Erro ao ler lote de entidades: " . $e->getMessage());
        }
        return $results;
    }

    private function fillEntityRecursively(InterfaceEntity $instance, string $currentClazz, array $row, \PDO $conn): void
    {
        $reflection = new ReflectionClass($currentClazz);

        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($this->isSetter($method)) {
                $params = $method->getParameters();
                $type = $params[0]->getType();
                $paramType = $type instanceof \ReflectionNamedType ? $type->getName() : null;

                // Simplificação: Se for array, ignoramos (associações tratadas depois)
                if ($paramType === 'array') continue;

                // Tenta encontrar a propriedade correspondente para checar atributos de Foreign Key
                $propertyName = lcfirst(substr($method->getName(), 3));

                if (!$paramType && $reflection->hasProperty($propertyName)) {
                    $propType = $reflection->getProperty($propertyName)->getType();
                    $paramType = $propType instanceof \ReflectionNamedType ? $propType->getName() : null;
                }

                $foreignKey = null;
                if ($reflection->hasProperty($propertyName)) {
                    $prop = $reflection->getProperty($propertyName);
                    foreach ($prop->getAttributes() as $attr) {
                        $attrName = $attr->getName();
                        if (str_ends_with($attrName, 'ManyToOne') || str_ends_with($attrName, 'HasOne')) {
                            $args = $attr->getArguments();
                            $foreignKey = $args['foreignKey'] ?? null;
                        }
                    }
                }

                // Se houver uma foreignKey explícita no atributo, usamos ela.
                // Caso contrário, usamos o nome derivado do método.
                $derivedName = substr($method->getName(), 3);
                $columnName = $this->convertPascalCaseToSnakeCase($foreignKey ?? $derivedName);

                // Tenta encontrar o valor na coluna exata ou com o sufixo _id (regra de negócio da Alpha Engine)
                $value = $row[$columnName] ?? $row[$columnName . "_id"] ?? null;

                if ($value !== null) {
                    if ($paramType && is_subclass_of($paramType, InterfaceEntity::class)) {
                        $childId = (int)$value;

                        // Alpha Engine: Defuse do Anti-Pattern "FK = 0" herdado de banco de dados legado.
                        // Se o ID estrangeiro for 0, consideramos que a relação não existe (ex: categoria raiz).
                        if ($childId === 0) {
                            continue;
                        }

                        $childInstance = $this->getFromIdentityMap($paramType, $childId);

                        if (!$childInstance) {
                            // Alpha Engine: Injeta o Proxy nativo de ManyToOne para Lazy Loading (Deep Hydration).
                            $childInstance = ProxyFactory::createProxy($paramType, $childId, function ($proxy, $id) use ($paramType) {
                                $tableName = $this->tablePrefix . $this->convertPascalCaseToSnakeCase((new ReflectionClass($paramType))->getShortName());
                                $conn = ConnectionDB::getInstance()->getConnection();
                                $stmt = $conn->prepare("SELECT * FROM `$tableName` WHERE id = ?");
                                $stmt->execute([$id]);
                                $row = $stmt->fetch(\PDO::FETCH_ASSOC);

                                if ($row) {
                                    $this->fillEntityRecursively($proxy, $paramType, $row, $conn);
                                    $this->processAssociations($proxy, $conn);
                                }
                            });

                            $this->addToIdentityMap($childInstance); // Mantém a integridade referencial mesmo no proxy
                        }
                        $method->invoke($instance, $childInstance);
                    } else {
                        $convertedValue = $this->convertToTargetType($value, $paramType ?? 'string');
                        $method->invoke($instance, $convertedValue);
                    }
                }
            }
        }

        $parentClass = get_parent_class($currentClazz);
        if ($parentClass && is_subclass_of($parentClass, InterfaceEntity::class)) {
            $currentId = $instance->getId() ?? (isset($row['id']) ? (int)$row['id'] : 0);
            $reflectionParent = new ReflectionClass($parentClass);

            // Se a classe pai não for abstrata e tiver uma tabela própria, buscamos os dados
            // No ecossistema Alpha, a maioria herda de BaseEntity (abstrata),
            // então o DAO pula buscas desnecessárias.
            if ($currentId > 0 && !$reflectionParent->isAbstract()) {
                $instance->setId($currentId);
                $parentTableName = $this->tablePrefix . $this->convertPascalCaseToSnakeCase((new ReflectionClass($parentClass))->getShortName());
                $stmt = $conn->prepare("SELECT * FROM `$parentTableName` WHERE id = ?");
                $stmt->execute([$currentId]);
                if ($parentRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $this->fillEntityRecursively($instance, $parentClass, $parentRow, $conn);
                }
            }
        }
    }
    private function readEntityComplete(InterfaceEntity $entity, \PDO $conn): void
    {
        $this->addToIdentityMap($entity);
        $reflection = new ReflectionClass($entity);
        $tableName = $this->tablePrefix . $this->convertPascalCaseToSnakeCase($reflection->getShortName());
        $stmt = $conn->prepare("SELECT * FROM `$tableName` WHERE id = ?");
        $stmt->execute([$entity->getId()]);
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->fillEntityRecursively($entity, get_class($entity), $row, $conn);
            $this->processAssociations($entity, $conn);
        }
    }

    // ---------------------------------------------------------------------------------------------------
    // Método update

    public function update(InterfaceEntity $entity): ?int
    {
        if ($entity->getId() === null || $entity->getId() <= 0) {
            throw new Exception("ID inválido para atualização.");
        }

        $hierarchy = $this->getEntityHierarchy(get_class($entity));
        $managedTransaction = false;

        try {
            $conn = ConnectionDB::getInstance()->getConnection();

            if (!$conn->inTransaction()) {
                $conn->beginTransaction();
                $managedTransaction = true;
            }

            foreach ($hierarchy as $clazz) {
                $this->updateForClass($conn, $entity, $clazz);
            }

            $this->saveAssociations($conn, $entity);

            if ($managedTransaction) {
                $conn->commit();
            }
            return $entity->getId();
        } catch (Exception $e) {
            if ($conn && $conn->inTransaction()) $conn->rollBack();
            error_log($e->getMessage());
            return null;
        }
    }

    private function updateForClass(\PDO $conn, InterfaceEntity $entity, string $clazz): void
    {
        $columnsMap = [];
        $reflection = new ReflectionClass($clazz);

        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $name = $method->getName();
            if ($this->isGetter($method)) {
                if ($name === 'getId') continue;

                $value = $method->invoke($entity);
                if (is_array($value)) continue;

                $prefixLength = str_starts_with($name, 'is') ? 2 : 3;
                $colName = $this->convertPascalCaseToSnakeCase(substr($name, $prefixLength));

                $returnType = $method->getReturnType();
                $returnTypeName = $returnType instanceof \ReflectionNamedType ? $returnType->getName() : null;

                if ($value instanceof InterfaceEntity || ($returnTypeName && is_subclass_of($returnTypeName, InterfaceEntity::class))) {
                    $colName .= "_id";
                    $value = $value ? $value->getId() : null;
                }

                if (!array_key_exists($colName, $columnsMap) || $value !== null) {
                    $columnsMap[$colName] = $value;
                }
            }
        }

        $attempts = 0;
        while ($attempts < 5) {
            $setClauses = [];
            $values = [];

            foreach ($columnsMap as $col => $val) {
                if ($val !== null) {
                    $setClauses[] = "`$col` = ?";

                    if ($val instanceof \DateTimeInterface) {
                        $val = $val->format('Y-m-d H:i:s');
                    }
                    $values[] = is_bool($val) ? (int)$val : $val;
                }
            }

            if (empty($setClauses)) return;

            $sql = "UPDATE `" . $this->tablePrefix . $this->convertPascalCaseToSnakeCase($reflection->getShortName()) .
                "` SET " . implode(", ", $setClauses) . " WHERE id = ?";

            $values[] = $entity->getId();

            try {
                $startTime = microtime(true);
                $conn->prepare($sql)->execute($values);
                $this->logDebugQuery($sql, $values, $startTime, $entity->getId(), ['action' => 'UPDATE']);
                return;
            } catch (PDOException $e) {
                // Auto-Healing: Resiliência contra colunas legadas inexistentes
                if ($e->getCode() == '42S22' && preg_match('/Unknown column \'([^\']+)\'/', $e->getMessage(), $matches)) {
                    $missingCol = trim($matches[1], '`');
                    if (array_key_exists($missingCol, $columnsMap)) {
                        error_log("Alpha Engine [Auto-Healing]: Coluna '{$missingCol}' ausente no BD. Ignorando e reprocessando Atualização...");
                        unset($columnsMap[$missingCol]);
                        $attempts++;
                        continue;
                    }
                }

                $this->logDebugQuery("[ERRO SQL UPDATE] " . $e->getMessage() . " | " . $sql, $values);

                if ($e->getCode() == 23000 || (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1062)) {
                    throw new \DomainException("Alpha Engine [Violação de Integridade]: Atualização rejeitada. Os dados informados entram em conflito com registros únicos já existentes.", 1062, $e);
                }
                throw $e;
            }
        }
    }

    // ---------------------------------------------------------------------------------------------------
    // Método delete

    public function delete(InterfaceEntity $entity): int
    {
        if ($entity->getId() === null || $entity->getId() <= 0) {
            throw new Exception("ID inválido para exclusão.");
        }

        $hierarchy = $this->getEntityHierarchy(get_class($entity));
        $hierarchy = array_reverse($hierarchy);
        $conn = null;

        try {
            $conn = ConnectionDB::getInstance()->getConnection();
            $conn->beginTransaction();

            foreach ($hierarchy as $clazz) {
                $tableName = $this->tablePrefix . $this->convertPascalCaseToSnakeCase((new ReflectionClass($clazz))->getShortName());
                $stmt = $conn->prepare("DELETE FROM `$tableName` WHERE id = ?");
                $stmt->execute([$entity->getId()]);
            }

            $conn->commit();
            return $entity->getId();
        } catch (Exception $e) {
            if ($conn && $conn->inTransaction()) $conn->rollBack();
            return 0;
        }
    }

    // ---------------------------------------------------------------------------------------------------
    // Utilitários

    private function convertPascalCaseToSnakeCase(string $name): string
    {
        // Adiciona underscore antes de maiúsculas e antes de números que seguem letras
        return strtolower(preg_replace('/(?<!^)([A-Z]|(?<=[a-zA-Z])[0-9])/', '_$1', $name));
    }

    private function buildWhereClause(InterfaceEntity $entity): string
    {
        $where = [];
        $reflection = new ReflectionClass($entity);

        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($this->isGetter($method)) {
                $value = $method->invoke($entity);
                if ($this->isValidValue($value)) {
                    $columnName = $this->convertPascalCaseToSnakeCase(substr($method->getName(), str_starts_with($method->getName(), 'get') ? 3 : 2));
                    if (is_string($value)) {
                        $where[] = "`$columnName` LIKE '%$value%'";
                    } else {
                        $where[] = "`$columnName` = $value";
                    }
                }
            }
        }
        return empty($where) ? "" : " WHERE " . implode(" AND ", $where);
    }

    private function isValidValue(mixed $value): bool
    {
        if ($value === null) return false;
        if (is_bool($value)) return $value;
        if (is_numeric($value)) return $value > 0;
        if (is_string($value)) return trim($value) !== '';
        return false;
    }

    private function isSetter(ReflectionMethod $method): bool
    {
        return str_starts_with($method->getName(), 'set') && $method->getNumberOfParameters() === 1;
    }

    private function isGetter(ReflectionMethod $method): bool
    {
        $name = $method->getName();
        return (str_starts_with($name, 'get') || str_starts_with($name, 'is'))
            && $method->getNumberOfParameters() === 0
            && $name !== 'getClass';
    }

    private function getFromIdentityMap(string $className, int $id): ?InterfaceEntity
    {
        $key = $className . ':' . $id;
        return self::$identityMap[$key] ?? null;
    }

    private function addToIdentityMap(InterfaceEntity $entity): void
    {
        $id = $entity->getId();
        if ($id !== null && $id > 0) {
            $key = get_class($entity) . ':' . $id;
            self::$identityMap[$key] = $entity;
        }
    }

    private function convertToTargetType(mixed $value, string $targetType): mixed
    {
        // PHP 8 Match Expression (Equivalente ao Switch moderno do Java)
        return match ($targetType) {
            'int', 'integer' => (int)$value,
            'float', 'double' => (float)$value,
            'bool', 'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'string' => (string)$value,
            default => $value
        };
    }

    private function buildInsertSql(string $clazz, array $columns, array $placeholders): string
    {
        $tableName = $this->tablePrefix . $this->convertPascalCaseToSnakeCase((new ReflectionClass($clazz))->getShortName());
        return "INSERT INTO `$tableName` (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $placeholders) . ")";
    }

    private function processAssociations(InterfaceEntity $instance, \PDO $conn): void
    {
        $reflection = new ReflectionClass($instance);
        foreach ($reflection->getProperties() as $property) {
            $attrs = $property->getAttributes();
            $isOneToMany = false;
            $isManyToMany = false;
            $targetEntityClass = null;
            $foreignKey = null;

            foreach ($attrs as $attr) {
                $attrName = $attr->getName();
                if (str_ends_with($attrName, 'OneToMany')) $isOneToMany = true;
                if (str_ends_with($attrName, 'ManyToMany')) $isManyToMany = true;

                $args = $attr->getArguments();
                $targetEntityClass = $args['targetEntity'] ?? null;
                $foreignKey = $args['foreignKey'] ?? null;
            }

            if ((!$isOneToMany && !$isManyToMany) || !$targetEntityClass) continue;

            if ($isOneToMany) {
                // Cria uma instância da entidade alvo para servir de filtro
                $targetInstance = (new ReflectionClass($targetEntityClass))->newInstance();

                // Define a chave estrangeira no objeto alvo
                // Normalizamos para PascalCase para garantir que encontre o setter (ex: customer_id -> CustomerId)
                $fkName = $foreignKey ?? ($reflection->getShortName() . "Id");
                $fkNamePascal = str_replace('_', '', ucwords($fkName, '_'));
                $setterName = "set" . ucfirst($fkNamePascal);

                if (method_exists($targetInstance, $setterName)) {
                    $targetInstance->$setterName($instance->getId());

                    // Também normaliza o setter da propriedade na entidade pai
                    $parentSetter = "set" . ucfirst(str_replace('_', '', ucwords($property->getName(), '_')));

                    if (method_exists($instance, $parentSetter)) {
                        if ($this->shouldLazyLoad($property)) {
                            // Alpha Engine: Injeta LazyCollection para carregamento sob demanda (Lazy Loading)
                            $loader = fn() => $this->read($targetInstance);
                            $instance->$parentSetter(new LazyCollection($loader));
                        } else {
                            // Hidratação imediata (Eager Loading)
                            $children = $this->read($targetInstance);
                            $instance->$parentSetter($children);
                        }
                    }
                }
            } elseif ($isManyToMany) {
                // Lógica para tabelas pivot (ex: category_to_layout)
                $targetReflection = new ReflectionClass($targetEntityClass);
                $tableLink = $this->tablePrefix . $this->convertPascalCaseToSnakeCase($reflection->getShortName()) .
                    "_" . $this->convertPascalCaseToSnakeCase($targetReflection->getShortName());

                $fkParent = $this->convertPascalCaseToSnakeCase($reflection->getShortName()) . "_id";
                $fkChild = $this->convertPascalCaseToSnakeCase($targetReflection->getShortName()) . "_id";

                $sql = "SELECT `$fkChild` FROM `$tableLink` WHERE `$fkParent` = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$instance->getId()]);
                $stmt->fetchAll(\PDO::FETCH_ASSOC);

                // Para cada ID encontrado na tabela pivot, poderíamos carregar a entidade (omitido por brevidade)
            }
        }
    }

    /**
     * Alpha Engine: Detecta se a associação deve ser carregada via Lazy Loading.
     * Analisa os argumentos do Atributo da propriedade (ex: #[OneToMany(fetch: 'LAZY')]).
     * 
     * @param ReflectionProperty $property Propriedade da entidade a ser analisada.
     * @return bool Verdadeiro se o atributo fetch estiver definido como LAZY.
     */
    private function shouldLazyLoad(ReflectionProperty $property): bool
    {
        foreach ($property->getAttributes() as $attr) {
            $args = $attr->getArguments();
            // Verifica se o argumento 'fetch' está definido como 'LAZY'
            if (isset($args['fetch']) && $args['fetch'] === 'LAZY') {
                return true;
            }
        }
        return false;
    }

    /**
     * Executa a paginação retornando os registros mapeados e o total geral
     * * @param QueryBuilder $builder
     * @param int $page Página atual (ex: 1, 2, 3...)
     * @param int $limit Quantidade de itens por página
     * @return array Contendo ['data' => [...], 'total' => int]
     */
    public function paginate(QueryBuilder $builder, int $page = 1, int $limit = 10): array
    {

        // 1. Calcula o deslocamento (Offset)
        $offset = ($page - 1) * $limit;

        // 2. Modifica o builder com as instruções de paginação
        $builder->limit($limit)->offset($offset);

        // 3. Executa a busca dos dados reais (substitua pelo seu método de buscar/mapear objetos)
        $conn = ConnectionDB::getInstance()->getConnection();
        $stmt = $conn->prepare($builder->getSQL());
        $stmt->execute($builder->getParams());
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Se preferir mapear para entidades aqui:
        // $entities = [];
        // foreach ($rows as $row) { $entities[] = $this->mapToObject($row); }

        // 4. Executa a query de contagem total usando o mesmo Builder (mantendo os filtros de WHERE/JOIN)        
        $stmtCount = $conn->prepare($builder->getCountSQL());
        $stmtCount->execute($builder->getParams());
        $countResult = $stmtCount->fetch(PDO::FETCH_ASSOC);
        $total = (int)($countResult['total'] ?? 0);

        // 5. Retorna a estrutura completa esperada pela camada de exibição/controlador
        return [
            'data'  => $rows, // ou $entities
            'total' => $total
        ];
    }

    /**
     * Alpha Engine: Debugger Ultra-Leve (Crash-Proof & Memory Safe)
     * Centraliza a auditoria de queries para leitura e escrita.
     */
    private function logDebugQuery(string $sql, array $params, ?float $startTime = null, ?int $affectedRowsOrId = null, array $sample = []): void
    {
        if (!defined('DIR_LOGS')) return;

        $logFile = DIR_LOGS . 'queries.php';
        if (!file_exists($logFile)) {
            @file_put_contents($logFile, "<?php die('Acesso Restrito'); ?>\n\n");
        }

        $runnableSql = $sql;
        foreach ($params as $param) {
            $safeParam = (is_string($param) && strlen($param) > 500)
                ? substr($param, 0, 500) . '... [TRUNCATED, SIZE: ' . strlen($param) . ' bytes]'
                : $param;

            if ($safeParam === null) $value = 'NULL';
            elseif (is_bool($safeParam)) $value = $safeParam ? '1' : '0';
            elseif (is_numeric($safeParam) && !is_string($safeParam)) $value = (string)$safeParam;
            else $value = "'" . addslashes((string)$safeParam) . "'";

            $pos = strpos($runnableSql, '?');
            if ($pos !== false) {
                $runnableSql = substr_replace($runnableSql, $value, $pos, 1);
            }
        }

        @error_log("[" . date('Y-m-d H:i:s') . "] " . $runnableSql . "\n", 3, $logFile);

        if ($startTime !== null) {
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);
            $msg = "  -> [RETORNO] Tempo: {$executionTime}ms | " . ($affectedRowsOrId !== null ? "Linhas/ID: {$affectedRowsOrId} | " : "");
            if (!empty($sample)) $msg .= "Amostra/Detalhes: " . json_encode($sample);
            @error_log($msg . "\n", 3, $logFile);
        }
    }
}
