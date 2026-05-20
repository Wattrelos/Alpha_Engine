<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\DataAccessObject\QueryBuilder;
use Alpha\Model\Domain\Entities\Country;

/**
 * Mapper para gerenciar a lógica de Países (Countries)
 */
class CountryMapper extends BaseMapper {

    protected string $entityClass = Country::class;
    protected string $tableName = 'country';

    /**
     * Obtém um país específico pelo ID
     * 
     * @param int $country_id
     * @return Country|null
     */
    public function getCountry(int $country_id): ?Country {
        $country = new Country();
        $country->setId($country_id);
        
        $results = $this->dao->read($country);
        return $results ? $results[0] : null;
    }

    public function findById(int $id): ?Country
    {
        return $this->getCountry($id);
    }

    /**
     * Lista todos os países ativos
     * 
     * @return array
     */
    public function getCountries(): array {
        $builder = (new QueryBuilder())
            ->from(DB_PREFIX . 'country', 'c')
            ->where("c.status = ?", [1])
            ->select('id');

        $rows = $this->dao->executeQuery($builder);
        $ids = array_column($rows, 'id');
        
        return $this->dao->readByIds(Country::class, $ids);
    }
}