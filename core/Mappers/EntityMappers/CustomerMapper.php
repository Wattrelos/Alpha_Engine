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
    protected string $table = 'customer';
    protected string $entityClass = Customer::class;

    /**
     * Localiza um cliente pelo e-mail.
     */
    public function findByEmail(string $email): ?Customer
    {
        return $this->findOneBy(['email' => strtolower($email)]);
    }

    /**
     * Persiste (Salva ou Atualiza) um cliente no banco de dados.
     */
    public function save(Customer $customer): int
    {
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
}