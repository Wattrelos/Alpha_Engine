<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\DataAccessObject\QueryBuilder;

/**
 * Class CartMapper
 * 
 * Gerencia as operações de banco de dados (CRUD) exclusivas da tabela de carrinho,
 * isolando o SQL da camada de domínio (CartRepository).
 */
class CartMapper extends BaseMapper
{
    protected string $tableName = 'cart';
    protected string $entityClass = \Alpha\Model\Domain\Entities\Cart::class;

    /**
     * Limpa carrinhos abandonados de visitantes baseando-se no tempo de expiração da sessão.
     */
    public function deleteExpired(int $storeId, int $expireSeconds): void
    {
        $query = (new QueryBuilder())
            ->delete($this->getFullTableName())
            ->where('store_id = ?', [$storeId])
            ->where('customer_id = ?', [0])
            ->where('date_added < DATE_SUB(NOW(), INTERVAL ? SECOND)', [$expireSeconds]);

        $this->dao->execute($query);
    }

    /**
     * Mescla o carrinho salvo do cliente (banco) com os itens que ele 
     * adicionou na sessão atual (visitante) antes de fazer o login.
     */
    public function mergeCartOnLogin(int $customerId, string $sessionId, int $storeId): void
    {
        // 1. Atualiza o ID da sessão nos itens antigos salvos pelo cliente
        $query1 = (new QueryBuilder())
            ->update($this->getFullTableName())
            ->set('session_id', $sessionId)
            ->where('store_id = ?', [$storeId])
            ->where('customer_id = ?', [$customerId]);
        $this->dao->execute($query1);

        // 2. Associa os novos itens adicionados como visitante (customer_id = 0) ao cliente recém-logado
        $query2 = (new QueryBuilder())
            ->update($this->getFullTableName())
            ->set('customer_id', $customerId)
            ->where('store_id = ?', [$storeId])
            ->where('customer_id = ?', [0])
            ->where('session_id = ?', [$sessionId]);
        $this->dao->execute($query2);
    }

    /**
     * Busca todos os itens do carrinho com base no contexto (Logado ou Visitante).
     */
    public function getItems(int $customerId, string $sessionId, int $storeId): array
    {
        $query = (new QueryBuilder())
            ->from($this->getFullTableName())
            ->where('store_id = ?', [$storeId]);

        if ($customerId) {
            $query->where('customer_id = ?', [$customerId]);
        } else {
            $query->where('customer_id = ?', [0])
                  ->where('session_id = ?', [$sessionId]);
        }

        return $this->dao->executeQuery($query);
    }

    /**
     * Atualiza a quantidade de um item existente no carrinho.
     */
    public function updateItem(int $cartId, int $quantity, int $customerId, string $sessionId): void
    {
        $query = (new QueryBuilder())
            ->update($this->getFullTableName())
            ->set('quantity', $quantity)
            ->where('id = ?', [$cartId]); // Alpha Engine: PK padronizada como id

        if ($customerId) {
            $query->where('customer_id = ?', [$customerId]);
        } else {
            $query->where('session_id = ?', [$sessionId]);
        }

        $this->dao->execute($query);
    }

    /**
     * Remove um item específico do carrinho.
     */
    public function removeItem(int $cartId, int $customerId, string $sessionId): void
    {
        $query = (new QueryBuilder())
            ->delete($this->getFullTableName())
            ->where('id = ?', [$cartId]);

        if ($customerId) {
            $query->where('customer_id = ?', [$customerId]);
        } else {
            $query->where('session_id = ?', [$sessionId]);
        }

        $this->dao->execute($query);
    }

    /**
     * Esvazia completamente o carrinho do usuário atual (usado após a confirmação do pedido).
     */
    public function clearItems(int $customerId, string $sessionId, int $storeId): void
    {
        $query = (new QueryBuilder())
            ->delete($this->getFullTableName())
            ->where('store_id = ?', [$storeId]);

        if ($customerId) {
            $query->where('customer_id = ?', [$customerId]);
        } else {
            $query->where('customer_id = ?', [0])
                  ->where('session_id = ?', [$sessionId]);
        }

        $this->dao->execute($query);
    }

    /**
     * Insere um novo item no carrinho de forma bruta (inserção limpa).
     * (Nota: A validação para não duplicar itens e somar a quantidade será tratada pelo Repository)
     */
    public function addItem(int $customerId, string $sessionId, int $storeId, int $productId, int $quantity, string $optionHash, int $subscriptionPlanId = 0): void
    {
        // Alpha Engine: Como a entidade Cart pode estar desatualizada (sem setStoreId), 
        // executamos a inserção de forma atômica via PDO para máxima performance e segurança.
        $conn = \Alpha\Model\DataAccessObject\ConnectionDB::getInstance()->getConnection();
        $sql = "INSERT INTO `" . $this->getFullTableName() . "` (`customer_id`, `session_id`, `store_id`, `product_id`, `subscription_plan_id`, `option`, `quantity`, `date_added`) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            $customerId,
            $sessionId,
            $storeId,
            $productId,
            $subscriptionPlanId,
            $optionHash,
            $quantity
        ]);
    }
}