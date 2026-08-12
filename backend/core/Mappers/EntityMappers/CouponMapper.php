<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Model\DataAccessObject\DataAccessObject;
use Alpha\Model\DataAccessObject\QueryBuilder;
use Alpha\Model\Domain\Entities\Coupon;

/**
 * CouponMapper - Centraliza a persistência e recuperação da entidade Coupon.
 * 
 * Seguindo os padrões Alpha Engine:
 * - Isolamento de SQL via DataAccessObject.
 * - Suporte a filtros dinâmicos para validação de regras de negócio.
 * - Hidratação automática de objetos de domínio.
 */
class CouponMapper
{
    private DataAccessObject $dao;

    public function __construct()
    {
        $this->dao = new DataAccessObject();
    }

    /**
     * Localiza um cupom pelo ID primário.
     */
    public function findById(int $id): ?Coupon
    {
        $coupon = new Coupon();
        $coupon->setId($id);

        $results = $this->dao->read($coupon);
        return $results[0] ?? null;
    }

    /**
     * Retorna todos os cupons cadastrados.
     */
    public function findAll(): array
    {
        return $this->dao->read(new Coupon());
    }

    /**
     * Busca um cupom específico com base em critérios flexíveis (ex: code).
     */
    public function findOneBy(array $criteria): ?Coupon
    {
        $builder = new QueryBuilder();
        $builder->from('coupon');
        
        foreach ($criteria as $field => $value) {
            $builder->where("$field = ?", [$value]);
        }

        $results = $this->dao->executeQuery($builder);
        
        if (empty($results)) {
            return null;
        }

        // Reutilizamos o findById para garantir que o DAO aplique o Identity Map e hidratação completa
        return $this->findById((int)$results[0]['id']);
    }

    /**
     * Persiste ou atualiza um cupom.
     */
    public function save(Coupon $coupon): ?int
    {
        if ($coupon->getId() > 0) {
            return $this->dao->update($coupon);
        }
        return $this->dao->create($coupon);
    }

    /**
     * Remove um cupom do sistema.
     */
    public function delete(int $id): bool
    {
        $coupon = new Coupon();
        $coupon->setId($id);
        return (bool)$this->dao->delete($coupon);
    }
}