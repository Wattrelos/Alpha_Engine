<?php
namespace Alpha\Model\DataAccessObject;

class QueryBuilder {
    private string $type = 'SELECT';
    private array $select = [];
    private string $from = '';
    private array $joins = [];
    private array $where = [];
    private array $set = [];
    private array $orderBy = [];
    private array $params = [];
    private ?int $limit = null;
    private ?int $offset = null;
    protected array $groupBy = [];
    

    public function select(string ...$columns): self {
        $this->select = array_merge($this->select, $columns);
        return $this;
    }

    public function delete(string $table): self {
        $this->type = 'DELETE';
        $this->from = $table;
        return $this;
    }

    public function update(string $table): self {
        $this->type = 'UPDATE';
        $this->from = $table;
        return $this;
    }

    public function set(string $column, mixed $value): self {
        $this->set[] = "{$column} = ?";
        $this->params[] = $value;
        return $this;
    }

    public function from(string $table, string $alias = ''): self {
        $this->from = $alias ? "{$table} {$alias}" : $table;
        return $this;
    }

    public function join(string $table, string $alias, string $on, string $type = 'INNER'): self {
        $this->joins[] = "{$type} JOIN {$table} {$alias} ON ({$on})";
        return $this;
    }

    public function leftJoin(string $table, string $alias, string $on): self {
        return $this->join($table, $alias, $on, 'LEFT');
    }

    public function where(string $condition, array $params = []): self {
        $this->where[] = $condition;
        $this->params = array_merge($this->params, $params);
        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): self {
        $this->orderBy[] = "{$column} {$direction}";
        return $this;
    }

    public function getParams(): array {
        return $this->params;
    }

    public function limit(int $limit): self {  
        $this->limit = $limit;  
        return $this;  
    }  

    public function offset(int $offset): self {  
        $this->offset = $offset;  
        return $this;  
    }

    /**
     * Adiciona uma cláusula GROUP BY à query.
     * 
     * @param string $column O nome da coluna (ex: 'p.id')
     * @return self
     */
    public function groupBy(string $column): self 
    {
        $this->groupBy[] = $column;
        return $this;
    }


    /**
     * Gera o SQL focado estritamente na contagem total de registros filtrados
     */

    public function getSQL(): string {  
        if ($this->type === 'DELETE') {
            $sql = "DELETE FROM " . $this->from;
            if (!empty($this->where)) {
                $sql .= " WHERE " . implode(' AND ', $this->where);
            }
            return $sql;
        }

        if ($this->type === 'UPDATE') {
            $sql = "UPDATE " . $this->from;
            if (!empty($this->set)) {
                $sql .= " SET " . implode(', ', $this->set);
            }
            if (!empty($this->where)) {
                $sql .= " WHERE " . implode(' AND ', $this->where);
            }
            return $sql;
        }

        $sql = "SELECT " . (empty($this->select) ? "*" : implode(', ', $this->select));  
        $sql .= " FROM " . $this->from;  
          
        if (!empty($this->joins)) {  
            $sql .= " " . implode(' ', $this->joins);  
        }  
          
        if (!empty($this->where)) {  
            $sql .= " WHERE " . implode(' AND ', $this->where);  
        }  

        if (!empty($this->groupBy)) {
            $sql .= " GROUP BY " . implode(', ', $this->groupBy);
        }
    
        if (!empty($this->orderBy)) {  
            $sql .= " ORDER BY " . implode(', ', $this->orderBy);  
        }  

        // Adiciona o LIMIT e OFFSET se estiverem definidos
        if ($this->limit !== null) {
            $sql .= " LIMIT " . $this->limit;
        }

        if ($this->offset !== null) {
            $sql .= " OFFSET " . $this->offset;
        }
        
        return $sql;  
    }  
    
    // O método getCountSQL() ignora o limit/offset para contar o total absoluto
    public function getCountSQL(): string {  
        // Se a consulta for agrupada, COUNT(*) geraria múltiplas linhas.
        // Usamos COUNT(DISTINCT colunas) para contar o número real de agrupamentos.
        if (!empty($this->groupBy)) {
            $sql = "SELECT COUNT(DISTINCT " . implode(', ', $this->groupBy) . ") as total FROM " . $this->from;
        } else {
            $sql = "SELECT COUNT(*) as total FROM " . $this->from;  
        }
          
        if (!empty($this->joins)) {  
            $sql .= " " . implode(' ', $this->joins);  
        }  
          
        if (!empty($this->where)) {  
            $sql .= " WHERE " . implode(' AND ', $this->where);  
        }

        return $sql;  
    }


}
