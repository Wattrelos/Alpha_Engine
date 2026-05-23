<?php
namespace Alpha\Mappers;

use Alpha\Model\DataAccessObject\ConnectionDB;
use Alpha\Model\DataAccessObject\DataAccessObject;
use Alpha\Model\DataAccessObject\ProxyFactory;
use Alpha\Model\DataAccessObject\QueryBuilder;
use Alpha\Model\Domain\InterfaceEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;
use Opencart\System\Engine\Registry;
use ReflectionClass;

/**
 * BaseMapper - Classe abstrata genérica para operações de banco de dados.
 * Implementa lógica de hidratação automática e suporte a Lazy Loading.
 */
abstract class BaseMapper implements MapperInterface
 {
    protected \PDO $db; 
    protected ?Registry $registry = null;
    protected string $entityClass = '';
    protected string $tableName;
    protected string $primaryKey = 'id';
    protected DataAccessObject $dao;

    /**
     * @param Registry|object|null $registry O Registry do OpenCart ou conexão DB legada.
     */
    public function __construct($registry = null)
    {
        if ($registry instanceof Registry) {
            $this->registry = $registry;
            $dbSource = $registry->get('db');
        } else {
            $dbSource = $registry;
        }

        $connection = ConnectionDB::getInstance($dbSource)->getConnection();
        
        if (!$connection instanceof \PDO) {
            throw new \RuntimeException("Erro Alpha Engine: Mapper requer uma conexão PDO ativa.");
        }

        $this->db = $connection;
        $this->dao = new DataAccessObject($this->db, $this->entityClass); // Passa a classe da entidade para o DAO
    }

    /**
     * Retorna o nome da tabela com o prefixo do banco.
     */
    protected function getFullTableName(): string
    {
        return DB_PREFIX . $this->tableName;
    }

    /**
     * Implementação padrão de salvamento via DAO.
     */
    public function save(InterfaceEntity $entity): ?int
    {
        return ($entity->getId() > 0) ? $this->dao->update($entity) : $this->dao->create($entity);
    }

    /**
     * Implementação padrão de exclusão via DAO.
     */
    public function delete(int $id): bool
    {
        $reflection = new ReflectionClass($this->entityClass);
        $entity = $reflection->newInstance();
        
        if (method_exists($entity, 'setId')) {
            $entity->setId($id);
        }

        return (bool)$this->dao->delete($entity);
    }

    /**
     * Busca uma entidade pelo ID.
     */
    public function findById(int $id): ?InterfaceEntity
    {
        $sql = "SELECT * FROM " . $this->getFullTableName() . " WHERE " . $this->primaryKey . " = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? $this->mapRowToEntity($row) : null;
    }

    /**
     * Retorna todos os registros da tabela.
     */
    public function findAll(): array
    {
        $sql = "SELECT * FROM " . $this->getFullTableName();
        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll();

        $entities = [];
        foreach ($rows as $row) {
            $entities[] = $this->mapRowToEntity($row);
        }
        return $entities;
    }

    /**
     * Implementação genérica de busca por filtros.
     * Suporta igualdade simples ou busca parcial se o valor contiver '%'.
     */
    public function search(array $filters, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        $query = (new QueryBuilder())->from($this->getFullTableName());

        foreach ($filters as $key => $value) {
            $column = $this->camelToSnake($key);
            
            if (is_string($value) && str_contains($value, '%')) {
                $query->where("{$column} LIKE ?", [$value]);
            } else {
                $query->where("{$column} = ?", [$value]);
            }
        }

        if ($orderBy !== null) {
            foreach ($orderBy as $column => $direction) {
                $query->orderBy($this->camelToSnake($column), strtoupper($direction));
            }
        }

        if ($limit !== null) {
            $query->limit($limit);
        }

        if ($offset !== null) {
            $query->offset($offset);
        }

        $rows = $this->dao->executeQuery($query);
        $entities = [];
        
        foreach ($rows as $row) {
            $entities[] = $this->mapRowToEntity($row);
        }
        
        return $entities;
    }

    /**
     * Implementação genérica de busca paginada.
     * Retorna os resultados hidratados, contagem total de registros e total de páginas.
     */
    public function paginate(array $filters, int $page = 1, int $limit = 10, ?array $orderBy = null): array
    {
        $query = (new QueryBuilder())->from($this->getFullTableName());

        foreach ($filters as $key => $value) {
            $column = $this->camelToSnake($key);
            
            if (is_string($value) && str_contains($value, '%')) {
                $query->where("{$column} LIKE ?", [$value]);
            } else {
                $query->where("{$column} = ?", [$value]);
            }
        }

        if ($orderBy !== null) {
            foreach ($orderBy as $column => $direction) {
                $query->orderBy($this->camelToSnake($column), strtoupper($direction));
            }
        }

        $result = $this->dao->paginate($query, $page, $limit);
        
        $entities = [];
        foreach ($result['data'] as $row) {
            $entities[] = $this->mapRowToEntity($row);
        }
        
        return [
            'data'        => $entities,
            'total'       => $result['total'],
            'total_pages' => (int)ceil($result['total'] / $limit)
        ];
    }

    /**
     * Converte um array do banco (row) em uma instância da Entidade.
     * Lida com propriedades simples e associações ManyToOne (Lazy Loading).
     */
    protected function mapRowToEntity(array $row): InterfaceEntity
    {
        $reflection = new ReflectionClass($this->entityClass);
        $entity = $reflection->newInstance();

        // Define o ID (herdado de BaseEntity)
        if (isset($row[$this->primaryKey])) {
            $entity->setId((int)$row[$this->primaryKey]);
        }

        foreach ($reflection->getProperties() as $property) {
            $propertyName = $property->getName();
            $columnName = $this->camelToSnake($propertyName);

            // Verifica se a propriedade possui o atributo ManyToOne
            $manyToOneAttr = $property->getAttributes(ManyToOne::class);
            
            if (!empty($manyToOneAttr)) {
                $attrInstance = $manyToOneAttr[0]->newInstance();
                $foreignKey = $attrInstance->foreignKey;
                $dbForeignKey = $this->camelToSnake($foreignKey);
                
                if (isset($row[$dbForeignKey]) && $row[$dbForeignKey] > 0) {
                    // Implementação de Lazy Loading via Proxy
                    $targetClass = $attrInstance->targetEntity;
                    $proxy = ProxyFactory::createProxy($targetClass, (int)$row[$dbForeignKey], function($id) use ($targetClass) {
                        // Lógica de carregamento tardio: resolve a tabela do alvo
                        $targetMapper = $this->resolveMapperFor($targetClass);
                        return ConnectionDB::getInstance()->queryOne(
                            "SELECT * FROM " . DB_PREFIX . $targetMapper->tableName . " WHERE id = ?", 
                            [$id]
                        );
                    });
                    
                    $property->setAccessible(true);
                    $property->setValue($entity, $proxy);
                }
                continue;
            }

            // Hidratação de campos simples
            if (array_key_exists($columnName, $row)) {
                $method = 'set' . ucfirst($propertyName);
                if (method_exists($entity, $method)) {
                    $entity->$method($row[$columnName]);
                }
            }
        }

        return $entity;
    }

    protected function camelToSnake(string $input): string
    {
        return strtolower(preg_replace('/(?<!^)([A-Z])/', '_$1', $input));
    }

    private function resolveMapperFor(string $entityClass): object
    {
        $className = str_replace('Alpha\\Model\\Domain\\Entities\\', '', $entityClass);
        // Alpha Engine: Agora os mappers de entidade residem no sub-namespace EntityMappers
        $mapperName = "\\Alpha\\Mappers\\EntityMappers\\" . $className . "Mapper";
        return new $mapperName();
    }
}