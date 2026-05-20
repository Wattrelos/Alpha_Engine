<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\VoucherMapper;
use Alpha\Model\Domain\Entities\Voucher;
use Alpha\Model\DataAccessObject\QueryBuilder;

/**
 * VoucherRepository - Gerencia a lógica de negócio e validação de cartões-presente (Vouchers).
 * 
 * Implementa o controle de saldo através da leitura do histórico de uso (Snapshot Pattern),
 * garantindo que o valor resgatado de um voucher respeite rigorosamente o seu saldo restante,
 * de forma atômica e independente de modificações na entidade original.
 */
class VoucherRepository extends AbstractRepository implements BaseRepositoryInterface
{
    protected function getMapper(): VoucherMapper
    {
        return $this->mapperFactory->get(VoucherMapper::class);
    }

    public function find(int $id): ?Voucher
    {
        return $this->getMapper()->findById($id);
    }

    public function findAll(): array
    {
        return $this->getMapper()->findAll();
    }

    /**
     * Busca um voucher pelo código.
     */
    public function findByCode(string $code): ?Voucher
    {
        return $this->getMapper()->findOneBy(['code' => $code]);
    }

    /**
     * Calcula o saldo restante do voucher.
     * 
     * O saldo é o valor original subtraído pela soma de todos os usos registrados no histórico.
     * 
     * @param int $voucherId
     * @return float
     */
    public function getBalance(int $voucherId): float
    {
        $voucher = $this->find($voucherId);
        
        if (!$voucher) {
            return 0.0;
        }

        $builder = (new QueryBuilder())
            ->from(DB_PREFIX . 'voucher_history')
            ->where('voucher_id = ?', [$voucherId])
            ->select('SUM(amount) AS total_used');
        
        $result = $this->dao->executeQuery($builder);
        $totalUsed = (float)($result[0]['total_used'] ?? 0.0);

        $balance = $voucher->getAmount() - $totalUsed;
        
        return $balance > 0 ? $balance : 0.0;
    }

    /**
     * Valida se um voucher pode ser aplicado no carrinho.
     */
    public function isValid(Voucher $voucher): bool
    {
        // 1. Verificação de Status
        if (!$voucher->getStatus()) {
            return false;
        }

        // 2. Verificação de Saldo (Impede uso de vouchers esgotados)
        return $this->getBalance($voucher->getId()) > 0;
    }
}