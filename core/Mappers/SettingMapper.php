<?php

namespace Alpha\Mappers;

use Opencart\System\Engine\Registry;

/**
 * Class SettingMapper
 * 
 * Gerencia as operações de banco de dados para as configurações da loja.
 */
class SettingMapper
{
    private object $db;

    public function __construct(Registry $registry)
    {
        $this->db = $registry->get('db');
    }

    public function findAll(): array
    {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "setting`");
        return $query->rows;
    }
}