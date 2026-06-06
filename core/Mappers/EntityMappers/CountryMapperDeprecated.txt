<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\Domain\Entities\Country;

/**
 * Mapper para gerenciar a lógica de Países (Countries)
 * Refatorado (Alpha Engine): Estende BaseMapper para hidratação automática.
 */
class CountryMapper extends BaseMapper {
    
    protected string $tableName = 'country';
    protected string $entityClass = Country::class;

    /**
     * Obtém um país específico pelo ID
     * 
     * @param int $country_id
     * @return Country|null
     */
    public function getCountry(int $country_id): ?Country {
        $country = $this->findById($country_id);
        return ($country && $country->getStatus()) ? $country : null;
    }

    /**
     * Lista todos os países ativos
     * 
     * @return Country[]
     */
    public function getCountries(): array {
        return $this->search(['status' => 1]);
    }
}