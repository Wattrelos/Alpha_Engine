<?php
namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\CartMapper;
use Opencart\System\Engine\Registry;

/**
 * Class CartRepository
 * 
 * Camada de abstração para operações no Carrinho de Compras.
 * Centraliza a inteligência de contexto, resolvendo automaticamente 
 * a transição entre visitantes (session_id) e clientes logados (customer_id).
 */
class CartRepository extends AbstractRepository {

    private Registry $registry;

    public function __construct(CartMapper $mapper, Registry $registry) {
        parent::__construct($mapper);
        $this->registry = $registry;
    }

    /**
     * Obtém os produtos atuais do carrinho, auto-resolvendo o contexto do usuário.
     */
    public function getProducts(): array {
        $customer = $this->registry->get('customer');
        $session = $this->registry->get('session');
        $config = $this->registry->get('config');

        $customerId = (int)($customer->isLogged() ? $customer->getId() : 0);
        $sessionId = $session->getId();
        $languageId = (int)$config->get('config_language_id');
        $storeId = (int)$config->get('config_store_id');
        $customerGroupId = (int)($customer->isLogged() ? $customer->getGroupId() : $config->get('config_customer_group_id'));

        /** @var CartMapper $mapper */
        $mapper = $this->getMapper();
        return $mapper->getProducts($customerId, $sessionId, $languageId, $storeId, $customerGroupId);
    }

    /**
     * Adiciona um produto ao carrinho ou incrementa a quantidade se já existir (Baseado no Hash de opções).
     */
    public function add(int $productId, int $quantity = 1, array $option = [], int $subscriptionPlanId = 0): void {
        $customer = $this->registry->get('customer');
        $session = $this->registry->get('session');

        $customerId = (int)($customer->isLogged() ? $customer->getId() : 0);
        $sessionId = $session->getId();

        /** @var CartMapper $mapper */
        $mapper = $this->getMapper();
        $mapper->add($customerId, $sessionId, $productId, $quantity, $option, $subscriptionPlanId);
    }

    /**
     * Atualiza a quantidade de um item específico no carrinho.
     */
    public function update(int $cartId, int $quantity): void {
        /** @var CartMapper $mapper */
        $mapper = $this->getMapper();
        $mapper->update($cartId, $quantity);
    }

    /**
     * Remove um item específico do carrinho.
     */
    public function remove(int $cartId): void {
        /** @var CartMapper $mapper */
        $mapper = $this->getMapper();
        $mapper->remove($cartId);
    }

    /**
     * Limpa totalmente o carrinho atual do usuário (utilizado no sucesso do Checkout).
     */
    public function clear(): void {
        $customer = $this->registry->get('customer');
        $session = $this->registry->get('session');

        $customerId = (int)($customer->isLogged() ? $customer->getId() : 0);
        $sessionId = $session->getId();

        /** @var CartMapper $mapper */
        $mapper = $this->getMapper();
        $mapper->clear($customerId, $sessionId);
    }
    
    // Nota Alpha Engine: Regras de negócio adicionais como validação de estoque 
    // máximo, bloqueios de produtos e regras de peso mínimo/máximo podem 
    // ser injetadas neste repositório no futuro!
}