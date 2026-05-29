<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Model\Domain\InterfaceEntity;

/**
 * PriceRepository - Domain Service focado na Precificação do Domínio.
 * 
 * Centraliza a inteligência de preços base (promoções, descontos) e isola 
 * a geração de modificadores SQL do Mapper, permitindo que a camada de dados
 * permaneça agnóstica à complexidade financeira do sistema (Alpha Engine).
 */
class PriceRepository extends AbstractRepository implements BaseRepositoryInterface
{
    /**
     * Retorna os modificadores de preço em SQL para Batch Loading (Desempenho).
     * Otimizado para evitar Type Juggling no banco de dados, utilizando 
     * injeção de data dinâmica na string e respeitando índices nativos.
     */
    public function getPriceStatements(int $customerGroupId): array
    {
        $today = date('Y-m-d');

        return [
            'discount' => "(SELECT (CASE WHEN `pd2`.`type` = 'P' THEN (`p`.`price` - (`p`.`price` * (`pd2`.`price` / 100))) WHEN `pd2`.`type` = 'S' THEN (`p`.`price` - `pd2`.`price`) ELSE `pd2`.`price` END) FROM `" . DB_PREFIX . "product_discount` `pd2` WHERE `pd2`.`product_id` = `p`.`id` AND `pd2`.`customer_group_id` = '" . $customerGroupId . "' AND `pd2`.`quantity` = '1' AND `pd2`.`special` = '0' AND ((`pd2`.`date_start` = '0000-00-00' OR `pd2`.`date_start` <= '{$today}') AND (`pd2`.`date_end` = '0000-00-00' OR `pd2`.`date_end` >= '{$today}')) ORDER BY `pd2`.`priority` ASC, `pd2`.`price` ASC LIMIT 1) AS `discount`",
            
            'special'  => "(SELECT (CASE WHEN `ps`.`type` = 'P' THEN (`p`.`price` - (`p`.`price` * (`ps`.`price` / 100))) WHEN `ps`.`type` = 'S' THEN (`p`.`price` - `ps`.`price`) ELSE `ps`.`price` END) FROM `" . DB_PREFIX . "product_discount` `ps` WHERE `ps`.`product_id` = `p`.`id` AND `ps`.`customer_group_id` = '" . $customerGroupId . "' AND `ps`.`quantity` = '1' AND `ps`.`special` = '1' AND ((`ps`.`date_start` = '0000-00-00' OR `ps`.`date_start` <= '{$today}') AND (`ps`.`date_end` = '0000-00-00' OR `ps`.`date_end` >= '{$today}')) ORDER BY `ps`.`priority` ASC, `ps`.`price` ASC LIMIT 1) AS `special`",
            
            'reward'   => "(SELECT `pr`.`points` FROM `" . DB_PREFIX . "product_reward` `pr` WHERE `pr`.`product_id` = `p`.`id` AND `pr`.`customer_group_id` = '" . $customerGroupId . "') AS `reward`",
            
            'review'   => "(SELECT COUNT(*) FROM `" . DB_PREFIX . "review` `r` WHERE `r`.`product_id` = `p`.`id` AND `r`.`status` = '1' GROUP BY `r`.`product_id`) AS `reviews`"
        ];
    }

    // Métodos obrigatórios da BaseRepositoryInterface
    public function find(int $id): ?InterfaceEntity { return null; }
    public function findAll(): array { return []; }
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array { return []; }
    public function findOneBy(array $criteria): ?InterfaceEntity { return null; }
}
