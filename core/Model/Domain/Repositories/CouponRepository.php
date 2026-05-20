<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\CouponMapper;
use Alpha\Model\Domain\Entities\Coupon;
use Alpha\Model\DataAccessObject\QueryBuilder;

/**
 * CouponRepository - Gerencia a lógica de negócio e validação de cupons de desconto.
 * 
 * Implementa o Snapshot Pattern através da persistência histórica de uso, garantindo
 * que o valor do desconto aplicado a um pedido permaneça imutável.
 */
class CouponRepository extends AbstractRepository implements BaseRepositoryInterface
{
    protected function getMapper(): CouponMapper
    {
        return $this->mapperFactory->get(CouponMapper::class);
    }

    public function find(int $id): ?Coupon
    {
        return $this->getMapper()->findById($id);
    }

    public function findAll(): array
    {
        return $this->getMapper()->findAll();
    }

    /**
     * Busca um cupom pelo código alfanumérico.
     */
    public function findByCode(string $code): ?Coupon
    {
        return $this->getMapper()->findOneBy(['code' => $code]);
    }

    /**
     * Valida se um cupom pode ser aplicado com base nas regras de domínio.
     * 
     * @param Coupon $coupon A entidade do cupom.
     * @param float $subTotal Valor total dos itens no carrinho.
     * @param int $customerId ID do cliente (opcional).
     * @return bool
     */
    public function isValid(Coupon $coupon, float $subTotal, int $customerId = 0): bool
    {
        $now = new \DateTime();

        // 1. Verificação de Status e Datas
        if (!$coupon->getStatus()) {
            return false;
        }

        if ($coupon->getDateStart() && $now < new \DateTime($coupon->getDateStart())) {
            return false;
        }

        if ($coupon->getDateEnd() && $now > new \DateTime($coupon->getDateEnd())) {
            return false;
        }

        // 2. Verificação de Valor Mínimo
        if ($subTotal < $coupon->getTotal()) {
            return false;
        }

        // 3. Verificação de Login Obrigatório
        if ($coupon->getLogged() && !$customerId) {
            return false;
        }

        // 4. Verificação de Limites de Uso (Snapshot Pattern Readiness)
        if ($coupon->getUsesTotal() > 0 && $this->getTotalUses($coupon->getId()) >= $coupon->getUsesTotal()) {
            return false;
        }

        if ($customerId && $coupon->getUsesCustomer() > 0) {
            if ($this->getCustomerUses($coupon->getId(), $customerId) >= $coupon->getUsesCustomer()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Obtém o total de vezes que o cupom foi utilizado (leitura de histórico/snapshot).
     */
    private function getTotalUses(int $couponId): int
    {
        $builder = (new QueryBuilder())
            ->from(DB_PREFIX . 'coupon_history')
            ->where('coupon_id = ?', [$couponId])
            ->select('COUNT(*) AS total');
        
        $result = $this->dao->executeQuery($builder);
        return (int)($result[0]['total'] ?? 0);
    }

    /**
     * Obtém o total de vezes que um cliente específico usou este cupom.
     */
    private function getCustomerUses(int $couponId, int $customerId): int
    {
        $builder = (new QueryBuilder())
            ->from(DB_PREFIX . 'coupon_history')
            ->where('coupon_id = ? AND customer_id = ?', [$couponId, $customerId])
            ->select('COUNT(*) AS total');

        $result = $this->dao->executeQuery($builder);
        return (int)($result[0]['total'] ?? 0);
    }
}