<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Model\DataAccessObject\DataAccessObject;
use Alpha\Model\DataAccessObject\QueryBuilder;
use Alpha\Model\Domain\Entities\Customer;

/**
 * CustomerMapper - Gerencia a autenticação e persistência de clientes.
 * 
 * Melhoras Alpha Engine:
 * - Autenticação Segura: Busca por e-mail utilizando Prepared Statements.
 * - Gestão de Auditoria: Métodos para registrar tentativas de login (customer_login).
 * - Tipagem Estrita: Retorna entidades Customer hidratadas via DataAccessObject.
 */
class CustomerMapper
{
    private DataAccessObject $dao;

    public function __construct()
    {
        $this->dao = new DataAccessObject();
    }

    /**
     * Localiza um cliente pelo e-mail.
     */
    public function getCustomerByEmail(string $email): ?Customer
    {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'customer')
            ->where("LCASE(email) = ?", [strtolower($email)])
            ->select('id');

        $results = $this->dao->executeQuery($query);
        
        if (!$results) {
            return null;
        }

        $customer = new Customer();
        $customer->setId((int)$results[0]['id']);
        
        $hydrated = $this->dao->read($customer);
        return $hydrated ? $hydrated[0] : null;
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
     * Salva ou atualiza os dados do cliente.
     */
    public function save(Customer $customer): ?int
    {
        return ($customer->getId() > 0) ? $this->dao->update($customer) : $this->dao->create($customer);
    }
}