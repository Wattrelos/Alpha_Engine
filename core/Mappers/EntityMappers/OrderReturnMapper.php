<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\Domain\Entities\OrderReturn;
use Alpha\Model\DataAccessObject\QueryBuilder;

/**
 * Mapper para a entidade OrderReturn.
 * Isola o banco de dados (tabela return) da lógica de domínio.
 */
class OrderReturnMapper extends BaseMapper
{
    protected string $table = 'return';
    protected string $entityClass = OrderReturn::class;

    public function getReturnsArray(int $customerId, int $languageId, int $start = 0, int $limit = 20): array
    {
        $query = (new QueryBuilder())
            ->select('r.return_id', 'r.order_id', 'r.firstname', 'r.lastname', 'rs.name as status', 'r.date_added')
            ->from(DB_PREFIX . "return", "r")
            ->leftJoin(DB_PREFIX . "return_status", "rs", "r.return_status_id = rs.return_status_id")
            ->where("r.customer_id = ?", [$customerId])
            ->where("rs.language_id = ?", [$languageId])
            ->orderBy("r.return_id", "DESC")
            ->limit($limit)
            ->offset($start);

        return $this->dao->executeQuery($query);
    }

    public function getTotalReturnsCount(int $customerId): int
    {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . "return")
            ->where("customer_id = ?", [$customerId]);

        return $this->dao->executeCount($query);
    }

    public function getReturnArray(int $returnId, int $customerId, int $languageId): array
    {
        $query = (new QueryBuilder())
            ->select(
                'r.return_id', 'r.order_id', 'r.firstname', 'r.lastname', 'r.email', 'r.telephone', 'r.product', 'r.model', 'r.quantity', 'r.opened',
                "(SELECT rr.name FROM " . DB_PREFIX . "return_reason rr WHERE rr.return_reason_id = r.return_reason_id AND rr.language_id = " . (int)$languageId . ") AS reason",
                "(SELECT ra.name FROM " . DB_PREFIX . "return_action ra WHERE ra.return_action_id = r.return_action_id AND ra.language_id = " . (int)$languageId . ") AS action",
                "(SELECT rs.name FROM " . DB_PREFIX . "return_status rs WHERE rs.return_status_id = r.return_status_id AND rs.language_id = " . (int)$languageId . ") AS status",
                'r.comment', 'r.date_ordered', 'r.date_added', 'r.date_modified'
            )
            ->from(DB_PREFIX . "return", "r")
            ->where("r.return_id = ?", [$returnId])
            ->where("r.customer_id = ?", [$customerId]);

        $results = $this->dao->executeQuery($query);
        return $results[0] ?? [];
    }

    public function getHistoriesArray(int $returnId, int $languageId, int $start = 0, int $limit = 20): array
    {
        $query = (new QueryBuilder())
            ->select('rh.date_added', 'rs.name AS status', 'rh.comment')
            ->from(DB_PREFIX . "return_history", "rh")
            ->leftJoin(DB_PREFIX . "return_status", "rs", "rh.return_status_id = rs.return_status_id")
            ->where("rh.return_id = ?", [$returnId])
            ->where("rs.language_id = ?", [$languageId])
            ->orderBy("rh.date_added", "ASC")
            ->limit($limit)
            ->offset($start);

        return $this->dao->executeQuery($query);
    }

    public function getTotalHistoriesCount(int $returnId): int
    {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . "return_history")
            ->where("return_id = ?", [$returnId]);

        return $this->dao->executeCount($query);
    }

    public function addReturnArray(array $data, int $customerId, int $defaultStatusId): void
    {
        $sql = "INSERT INTO `" . DB_PREFIX . "return` SET 
                  order_id = :order_id, 
                  customer_id = :customer_id, 
                  firstname = :firstname, 
                  lastname = :lastname, 
                  email = :email, 
                  telephone = :telephone, 
                  product = :product, 
                  model = :model, 
                  quantity = :quantity, 
                  opened = :opened, 
                  return_reason_id = :return_reason_id, 
                  return_status_id = :return_status_id, 
                  comment = :comment, 
                  date_ordered = :date_ordered, 
                  date_added = NOW(), 
                  date_modified = NOW()";

        $this->dao->executeRawSQL($sql, [
            'order_id'         => (int)$data['order_id'],
            'customer_id'      => $customerId,
            'firstname'        => $data['firstname'] ?? '',
            'lastname'         => $data['lastname'] ?? '',
            'email'            => $data['email'] ?? '',
            'telephone'        => $data['telephone'] ?? '',
            'product'          => $data['product'] ?? '',
            'model'            => $data['model'] ?? '',
            'quantity'         => (int)($data['quantity'] ?? 1),
            'opened'           => empty($data['opened']) ? 0 : 1,
            'return_reason_id' => (int)($data['return_reason_id'] ?? 0),
            'return_status_id' => $defaultStatusId,
            'comment'          => $data['comment'] ?? '',
            'date_ordered'     => $data['date_ordered'] ?? ''
        ]);
    }
}