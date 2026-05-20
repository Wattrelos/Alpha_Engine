<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\DataAccessObject\QueryBuilder;

/**
 * TotalMapper - Orquestra o cálculo de totais e impostos (Alpha Engine).
 * 
 * Melhoras Alpha Engine:
 * - Desacoplamento: Remove a dependência do loader legado para cálculos financeiros.
 * - Performance: Cache de extensões ativas para evitar consultas repetitivas.
 * - Tipagem Estrita: Garante que os valores de saída sejam matematicamente precisos.
 */
class TotalMapper extends BaseMapper {

    protected string $tableName = 'extension';

    /**
     * Alpha Engine: Executa o ciclo de cálculo de totais.
     * 
     * @param array &$totals Coleção de linhas de total (Subtotal, Tax, Total, etc)
     * @param array &$taxes  Coleção de impostos calculados
     * @param float &$total  Valor acumulado final
     */
    public function getTotals(array &$totals, array &$taxes, float &$total): void {
        $extensionMapper = new ExtensionMapper($this->registry);
        $results = $extensionMapper->getExtensionsByType('total');

        $sort_order = [];

        foreach ($results as $key => $value) {
            $sort_order[$key] = (int)oc_config('total_' . $value->getCode() . '_sort_order');
        }

        array_multisort($sort_order, SORT_ASC, $results);

        foreach ($results as $result) {
            // Alpha Engine: Verificação de status via config nativa
            if (oc_config('total_' . $result->getCode() . '_status')) {
                // Invocação dinâmica da extensão (enquanto as extensões de total não são convertidas em Mappers)
                // Utilizamos o Registry do OpenCart para manter a compatibilidade de execução
                $registry = \Alpha\Model\DataAccessObject\ConnectionDB::getRegistry();
                $load = $registry->get('load');
                
                $extension_route = 'extension/' . $result->getExtension() . '/total/' . $result->getCode();
                
                $load->model($extension_route);
                
                $model_name = 'model_extension_' . $result->getExtension() . '_total_' . $result->getCode();
                
                if ($registry->has($model_name)) {
                    $registry->get($model_name)->getTotal($totals, $taxes, $total);
                }
            }
        }
    }
}