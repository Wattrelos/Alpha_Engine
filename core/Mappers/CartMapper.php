<?php

namespace Alpha\Mappers;

use Alpha\Model\DataAccessObject\DataAccessObject;
use Alpha\Model\DataAccessObject\QueryBuilder;
use Alpha\Model\Domain\Entities\Cart;

/**
 * CartMapper - Gerencia o ciclo de vida do carrinho de compras.
 * 
 * Melhoras Alpha Engine:
 * - Gerenciamento de Opções Complexas: Compara hashes de opções para evitar duplicidade.
 * - Persistência via Entidade: Utiliza a entidade Cart e o DAO para salvar dados.
 * - Inteligência de Merge: Facilita a unificação de carrinhos entre sessão e cliente logado.
 */
class CartMapper {
    private DataAccessObject $dao;

    public function __construct() {
        $this->dao = new DataAccessObject();
    }

    /**
     * Adiciona um produto ao carrinho com suporte a opções complexas e assinaturas.
     */
    public function add(int $product_id, int $quantity, array $options, int $subscription_plan_id, int $customer_id, string $session_id): void {
        $option_json = json_encode($options);

        // 1. Busca se já existe um item idêntico no carrinho
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'cart')
            ->where("product_id = ?", [$product_id])
            ->where("subscription_plan_id = ?", [$subscription_plan_id])
            ->where("`option` = ?", [$option_json]);

        if ($customer_id) {
            $query->where("customer_id = ?", [$customer_id]);
        } else {
            $query->where("session_id = ?", [$session_id]);
        }

        $results = $this->dao->executeQuery($query);

        if ($results) {
            // 2. Se existe, apenas incrementa a quantidade via Entidade
            $cart = new Cart();
            $cart->setId((int)$results[0]['id']);
            $this->dao->read($cart);
            
            $cart->setQuantity($cart->getQuantity() + $quantity);
            $this->dao->update($cart);
        } else {
            // 3. Se não existe, cria um novo registro
            $cart = new Cart();
            $cart->setCustomerId($customer_id)
                 ->setSessionId($session_id)
                 ->setProductId($product_id)
                 ->setSubscriptionPlanId($subscription_plan_id)
                 ->setOption($option_json)
                 ->setQuantity($quantity)
                 ->setDateAdded(date('Y-m-d H:i:s'));
            
            $this->dao->create($cart);
        }
    }

    /**
     * Obtém os itens do carrinho hidratados como Entidades.
     * @return Cart[]
     */
    public function getCartItems(int $customer_id, string $session_id): array {
        $cart = new Cart();
        if ($customer_id) {
            $cart->setCustomerId($customer_id);
        } else {
            $cart->setSessionId($session_id);
        }

        return $this->dao->read($cart);
    }

    /**
     * Atualiza a quantidade de um registro específico no carrinho.
     */
    public function updateQuantity(int $cart_id, int $quantity): void {
        $cart = new Cart();
        $cart->setId($cart_id);
        
        if ($this->dao->read($cart)) {
            $cart->setQuantity($quantity);
            $this->dao->update($cart);
        }
    }

    /**
     * Remove um item e valida a propriedade para segurança.
     */
    public function delete(int $cart_id, int $customer_id, string $session_id): void {
        $cart = new Cart();
        $cart->setId($cart_id);
        
        $results = $this->dao->read($cart);
        if (!$results) return;

        $item = $results[0];
        // Validação de segurança: o item pertence a quem está tentando deletar?
        if (($customer_id && $item->getCustomerId() === $customer_id) || ($item->getSessionId() === $session_id)) {
            $this->dao->delete($item);
        }
    }

    /**
     * Limpa todo o carrinho de um cliente ou sessão
     * 
     * @param int $customer_id
     * @param string $session_id
     * @return void
     */
    public function clear(int $customer_id, string $session_id): void {
        $conn = \Alpha\Model\DataAccessObject\ConnectionDB::getInstance()->getConnection();
        $sql = $customer_id ? "DELETE FROM `" . DB_PREFIX . "cart` WHERE `customer_id` = ?" : "DELETE FROM `" . DB_PREFIX . "cart` WHERE `session_id` = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$customer_id ?: $session_id]);
    }
}