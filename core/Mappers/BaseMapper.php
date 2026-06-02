<?php
namespace Alpha\Mappers;

use Alpha\Model\DataAccessObject\ConnectionDB;
use Alpha\Model\DataAccessObject\DataAccessObject;
use Alpha\Model\DataAccessObject\QueryBuilder;
use Alpha\Model\Domain\InterfaceEntity;
use ReflectionClass;

/**
 * BaseMapper - Classe abstrata genérica para operações de banco de dados.
 * Implementa lógica de hidratação automática e suporte a Lazy Loading.
 */
abstract class BaseMapper implements MapperInterface
 {
    protected mixed $registry = null;
    protected string $entityClass = '';
    protected string $tableName = '';
    protected string $table = ''; // Bridge de compatibilidade para mappers antigos
    protected string $primaryKey = 'id';
    protected DataAccessObject $dao;

    /**
     * @param mixed $registry O Registry, Container ou nulo.
     */
    public function __construct(mixed $registry = null)
    {
        $this->registry = $registry;
        $this->dao = new DataAccessObject();
    }

    /**
     * Retorna o nome da tabela com o prefixo do banco.
     */
    protected function getFullTableName(): string
    {
        $name = $this->tableName ?: $this->table;
        return DB_PREFIX . $name;
    }

    /**
     * Implementação padrão de salvamento via DAO.
     */
    public function save(InterfaceEntity $entity): ?int
    {
        return ($entity->getId() > 0) ? $this->dao->update($entity) : $this->dao->create($entity);
    }

    /**
     * Implementação padrão de atualização via DAO.
     */
    public function update(InterfaceEntity $entity): bool
    {
        return (bool)$this->dao->update($entity);
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
        $query = (new QueryBuilder())
            ->select('*')
            ->from($this->getFullTableName())
            ->where($this->primaryKey . " = ?", [$id])
            ->limit(1);

        $results = $this->dao->executeQuery($query);
        return $results ? $this->dao->hydrate($this->entityClass, $results[0]) : null;
    }

    /**
     * Retorna todos os registros da tabela.
     */
    public function findAll(): array
    {
        $query = (new QueryBuilder())
            ->select('*')
            ->from($this->getFullTableName());

        $rows = $this->dao->executeQuery($query);

        $entities = [];
        foreach ($rows as $row) {
            $entities[] = $this->dao->hydrate($this->entityClass, $row);
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
        $query->select('*');

        foreach ($filters as $key => $value) {
            $column = $this->camelToSnake($key);
            
            if (is_string($value) && str_contains($value, '%')) {
                $query->where("`{$column}` LIKE ?", [$value]);
            } else {
                $query->where("`{$column}` = ?", [$value]);
            }
        }

        if ($orderBy !== null) {
            foreach ($orderBy as $column => $direction) {
                $query->orderBy("`" . $this->camelToSnake($column) . "`", strtoupper($direction));
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
            $entities[] = $this->dao->hydrate($this->entityClass, $row);
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
        $query->select('*');

        foreach ($filters as $key => $value) {
            $column = $this->camelToSnake($key);
            
            if (is_string($value) && str_contains($value, '%')) {
                $query->where("`{$column}` LIKE ?", [$value]);
            } else {
                $query->where("`{$column}` = ?", [$value]);
            }
        }

        if ($orderBy !== null) {
            foreach ($orderBy as $column => $direction) {
                $query->orderBy("`" . $this->camelToSnake($column) . "`", strtoupper($direction));
            }
        }

        $result = $this->dao->paginate($query, $page, $limit);
        
        $entities = [];
        foreach ($result['data'] as $row) {
            $entities[] = $this->dao->hydrate($this->entityClass, $row);
        }
        
        return [
            'data'        => $entities,
            'total'       => $result['total'],
            'total_pages' => (int)ceil($result['total'] / $limit)
        ];
    }

    protected function camelToSnake(string $input): string
    {
        return strtolower(preg_replace('/(?<!^)([A-Z])/', '_$1', $input));
    }

    /**
     * Limpa o cache estático de entidades do Motor ORM.
     * Evita erro "Out of Memory" durante fluxos de longa duração (Ex: Importação de tabelas).
     */
    public function clearIdentityMap(): void
    {
        DataAccessObject::clearIdentityMap();
    }
}