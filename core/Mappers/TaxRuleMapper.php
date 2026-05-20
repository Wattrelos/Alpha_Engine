<?php
namespace Alpha\Mappers;

use Alpha\Mappers\CollectionToArrayConverter;
use Alpha\Model\DataAccessObject\DataAccessObject;
use Alpha\Model\DataAccessObject\QueryBuilder;
use Alpha\Model\Domain\Entities\GeoZone;
use Alpha\Model\Domain\Entities\TaxRate;
use Alpha\Model\Domain\Entities\TaxRule;



class TaxRuleMapper {
    private DataAccessObject $dao;

    public function __construct() {
        $this->dao = new DataAccessObject();
    }

    public function getRules(string $based, int $countryId, int $zoneId, int $customerGroupId, array $additionalZones = [], int $page = 1, int $limit = 100): array {
        // 1. Configura a estrutura base com todos os filtros comuns       
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'tax_rule', 'tr1')
            ->leftJoin(DB_PREFIX . 'tax_rate', 'tr2', 'tr1.tax_rate_id = tr2.id') // Corrigido: tr1.tax_rate_id referencia tr2.id
            ->join(DB_PREFIX . 'tax_rate_to_customer_group', 'tr2cg', 'tr2.id = tr2cg.tax_rate_id')
            ->leftJoin(DB_PREFIX . 'zone_to_geo_zone', 'z2gz', 'tr2.geo_zone_id = z2gz.geo_zone_id')
            ->leftJoin(DB_PREFIX . 'geo_zone', 'gz', 'tr2.geo_zone_id = gz.id')
            ->where("tr1.based = ?", [$based])
            ->where("tr2cg.customer_group_id = ?", [$customerGroupId])
            ->where("z2gz.country_id = ?", [$countryId])
            ->select(
                'tr1.id AS id', // tax_class_id
                'tr1.based AS based',
                'tr1.priority AS priority',
                'tr2.id AS tax_rate_id',
                'tr2.name AS tax_rate_name',
                'tr2.rate AS tax_rate_rate',
                'tr2.type AS tax_rate_type',
                'tr2.geo_zone_id AS tax_rate_geo_zone_id'
            )
            ->orderBy('tr1.priority', 'ASC');

        // Aplica filtro de zonas (incluindo as adicionais se houver)
        $zones = array_unique(array_merge(['0', (string)$zoneId], array_map('strval', $additionalZones)));
        $placeholders = implode(',', array_fill(0, count($zones), '?'));
        $query->where("z2gz.zone_id IN ($placeholders)", $zones);

        // 2. Executa a paginação completa através do DAO (Count + Select)
        $pagination = $this->dao->paginate($query, $page, $limit);
        $totalRows = $pagination['total'];
        $rawCollection = $pagination['data'];

        $taxRuleEntities = [];
        foreach ($rawCollection as $row) {
            $taxRate = new TaxRate();
            $taxRate->setId((int)$row['tax_rate_id']);
            $taxRate->setName($row['tax_rate_name']);
            $taxRate->setRate((float)$row['tax_rate_rate']);
            $taxRate->setType($row['tax_rate_type']);
            
            $geoZone = new GeoZone();
            $geoZone->setId((int)$row['tax_rate_geo_zone_id']);
            $taxRate->setGeoZone($geoZone);

            $taxRule = new TaxRule();
            $taxRule->setId((int)$row['id']); // tr1.id
            $taxRule->setBased($row['based']);
            $taxRule->setPriority((int)$row['priority']);
            $taxRule->setTaxRate($taxRate); // Aninha o objeto TaxRate

            $taxRuleEntities[] = $taxRule;
        }

        // 5. Retorna o pacote completo estruturado
        return [
            'total' => $totalRows,
            'data'  => CollectionToArrayConverter::convertCollection($taxRuleEntities) // Converte entidades para array
        ];
    }
}
