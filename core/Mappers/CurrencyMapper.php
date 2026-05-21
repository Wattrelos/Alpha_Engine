<?php

namespace Alpha\Mappers;

use Opencart\System\Engine\Registry;

/**
 * Class CurrencyMapper
 * 
 * Implementa o padrão DataAccessObject/Mapper para as Moedas,
 * centralizando consultas e garantindo reuso e performance (Alpha Engine).
 */
class CurrencyMapper
{
    private object $db;

    public function __construct(Registry $registry)
    {
        $this->db = $registry->get('db');
    }

    /**
     * Recupera todas as moedas ativas na loja.
     * 
     * @return array<int, array<string, mixed>>
     */
    public function findAllActive(): array
    {
        // Substitui a chamada direta e sem filtro do OpenCart original.
        // Adicionado filtro de status para garantir performance e integridade de negócio.
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "currency` WHERE `status` = '1'");

        return $query->rows;
    }
}