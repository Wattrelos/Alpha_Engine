<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\DataAccessObject\QueryBuilder;
use Alpha\Model\Domain\Entities\CustomField;

/**
 * CustomFieldMapper - Acesso a dados dos campos personalizados
 */
class CustomFieldMapper extends BaseMapper
{
    protected string $tableName = 'custom_field';
    protected string $entityClass = CustomField::class;

    /**
     * Recupera campos personalizados e seus valores baseados no grupo de clientes.
     * Traz todas as dependências em uma única estrutura agnóstica para formulários.
     */
    public function getCustomFields(int $customerGroupId, int $languageId): array
    {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'custom_field', 'cf')
            ->leftJoin(DB_PREFIX . 'custom_field_description', 'cfd', 'cf.id = cfd.custom_field_id')
            ->leftJoin(DB_PREFIX . 'custom_field_customer_group', 'cfcg', 'cf.id = cfcg.custom_field_id')
            ->where("cfd.language_id = ?", [$languageId])
            ->where("cfcg.customer_group_id = ?", [$customerGroupId])
            ->where("cf.status = ?", [1])
            ->orderBy("cf.sort_order", "ASC")
            ->select('cf.*', 'cf.id AS custom_field_id', 'cfd.name', 'cfcg.required');

        $customFieldData = [];
        $results = $this->dao->executeQuery($query);

        foreach ($results as $result) {
            $customFieldValueData = [];

            if (in_array($result['type'], ['select', 'radio', 'checkbox'])) {
                $queryValue = (new QueryBuilder())
                    ->from(DB_PREFIX . 'custom_field_value', 'cfv')
                    ->leftJoin(DB_PREFIX . 'custom_field_value_description', 'cfvd', 'cfv.id = cfvd.custom_field_value_id')
                    ->where("cfv.custom_field_id = ?", [(int)$result['custom_field_id']])
                    ->where("cfvd.language_id = ?", [$languageId])
                    ->orderBy("cfv.sort_order", "ASC")
                    ->select('cfv.*', 'cfv.id AS custom_field_value_id', 'cfvd.name');

                $valueResults = $this->dao->executeQuery($queryValue);

                foreach ($valueResults as $valueResult) {
                    $customFieldValueData[] = [
                        'custom_field_value_id' => $valueResult['custom_field_value_id'],
                        'name'                  => $valueResult['name']
                    ];
                }
            }

            $customFieldData[] = [
                'custom_field_id'    => $result['custom_field_id'],
                'custom_field_value' => $customFieldValueData,
                'name'               => $result['name'],
                'type'               => $result['type'],
                'value'              => $result['value'],
                'validation'         => $result['validation'],
                'location'           => $result['location'],
                'required'           => $result['required'] > 0,
                'sort_order'         => $result['sort_order']
            ];
        }

        return $customFieldData;
    }
}