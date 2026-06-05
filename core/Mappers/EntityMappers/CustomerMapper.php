<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\DataAccessObject\QueryBuilder;
use Alpha\Model\Domain\Entities\Customer;

/**
 * CustomerMapper - Gerencia a autenticação e persistência de clientes.
 * 
 * Melhoras Alpha Engine:
 * - Herança do BaseMapper para uso nativo do DataAccessObject.
 * - Autenticação Segura: Busca por e-mail centralizada usando o findOneBy().
 * - Gestão de Auditoria: Métodos para registrar tentativas de login (customer_login).
 */
class CustomerMapper extends BaseMapper
{
    protected string $tableName = 'customer';
    protected string $entityClass = Customer::class;

    /**
     * Localiza um cliente pelo e-mail.
     */
    public function findByEmail(string $email): ?Customer
    {
        $query = (new QueryBuilder())
            ->from($this->getFullTableName())
            ->where("LCASE(email) = LCASE(?)", [$email])
            ->select('id')
            ->limit(1);

        $results = $this->dao->executeQuery($query);

        if (!empty($results)) {
            $customer = new Customer();
            $customer->setId((int)$results[0]['id']);
            $hydrated = $this->dao->read($customer);
            return $hydrated ? $hydrated[0] : null;
        }

        return null;
    }

    /**
     * Localiza um cliente baseado em critérios genéricos.
     */
    public function findOneBy(array $criteria): ?Customer
    {
        $builder = (new QueryBuilder())
            ->from($this->getFullTableName())
            ->select('id')
            ->limit(1);
            
        foreach ($criteria as $field => $value) {
            $builder->where("`$field` = ?", [$value]);
        }

        $results = $this->dao->executeQuery($builder);
        
        if (!empty($results)) {
            $customer = new Customer();
            $customer->setId((int)$results[0]['id']);
            $hydrated = $this->dao->read($customer);
            return $hydrated ? $hydrated[0] : null;
        }

        return null;
    }

    /**
     * Persiste (Salva ou Atualiza) um cliente no banco de dados.
     */
    public function save(\Alpha\Model\Domain\InterfaceEntity $customer): ?int {
        if ($customer->getId() > 0) {
            $this->dao->update($customer);
            return $customer->getId();
        }
        return $this->dao->create($customer);
    }

    /**
     * Registra uma tentativa de login para auditoria e bloqueio de brute-force.
     */
    public function addLoginAttempt(string $email, string $ip): void
    {
        $query = "INSERT INTO `" . DB_PREFIX . "customer_login` SET `email` = ?, `ip` = ?, `date_added` = NOW()";
        $conn = \Alpha\Model\DataAccessObject\ConnectionDB::getInstance()->getConnection();
        $stmt = $conn->prepare($query);
        $stmt->execute([$email, $ip]);
    }

    /**
     * Conta o número de tentativas de login em um período (ex: 1 hora).
     */
    public function getLoginAttempts(string $email): int
    {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'customer_login')
            ->where("LCASE(email) = ?", [strtolower($email)])
            ->where("date_added > ?", [date('Y-m-d H:i:s', strtotime('-1 hour'))])
            ->select('COUNT(*) AS total');

        $results = $this->dao->executeQuery($query);
        return $results ? (int)$results[0]['total'] : 0;
    }

    /**
     * Limpa o histórico de tentativas após um login bem-sucedido.
     */
    public function deleteLoginAttempts(string $email): void
    {
        $conn = \Alpha\Model\DataAccessObject\ConnectionDB::getInstance()->getConnection();
        $stmt = $conn->prepare("DELETE FROM `" . DB_PREFIX . "customer_login` WHERE LCASE(email) = ?");
        $stmt->execute([strtolower($email)]);
    }

    /**
     * Retorna a lista de transações do cliente.
     */
    public function getTransactionsArray(int $customerId, int $start = 0, int $limit = 20): array
    {
        $query = (new QueryBuilder())
            ->select('id', 'order_id', 'description', 'amount', 'date_added')
            ->from(DB_PREFIX . "customer_transaction")
            ->where("customer_id = ?", [$customerId])
            ->orderBy("id", "DESC")
            ->limit($limit)
            ->offset($start);

        return $this->dao->executeQuery($query);
    }

    /**
     * Conta o total de transações do cliente.
     */
    public function getTotalTransactionsCount(int $customerId): int
    {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . "customer_transaction")
            ->where("customer_id = ?", [$customerId]);

        return $this->dao->executeCount($query);
    }

    /**
     * Calcula o saldo total das transações do cliente.
     */
    public function getTransactionTotalSum(int $customerId): float
    {
        $query = "SELECT SUM(amount) AS total FROM `" . DB_PREFIX . "customer_transaction` WHERE customer_id = ?";
        $conn = \Alpha\Model\DataAccessObject\ConnectionDB::getInstance()->getConnection();
        $stmt = $conn->prepare($query);
        $stmt->execute([$customerId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ? (float)($row['total'] ?? 0.0) : 0.0;
    }
}