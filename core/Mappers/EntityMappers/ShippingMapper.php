<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Model\Domain\Repositories\LengthClassRepository;
use Alpha\Model\Domain\Repositories\WeightClassRepository;
use Alpha\Mappers\BaseMapper;
use Alpha\Support\Registry;

/**
 * ShippingMapper - Orquestra a listagem e cálculo de métodos de frete (Alpha Engine).
 *
 * Refatorado para incluir dependências de WeightClassRepository e LengthClassRepository,
 * preparando para cálculos de frete volumétrico e de dimensões de alta precisão.
 *
 * Embora o mapper não realize as conversões diretamente, ele garante que os repositórios
 * estejam disponíveis para os módulos de frete legados (via Registry) ou para futuras
 * refatorações dos próprios módulos de frete para o padrão Alpha Engine, onde seriam
 * injetados diretamente.
 * 
 * Melhoras Alpha Engine:
 * - Desacoplamento: Move a lógica de descoberta de extensões para fora do controlador.
 * - Centralização: Garante que a ordenação e validação de status sigam o padrão Alpha.
 * - Performance: Utiliza o registry centralizado para carga dinâmica de modelos de extensão.
 */
class ShippingMapper extends BaseMapper
{

    protected string $tableName = 'extension';

    private WeightClassRepository $weightClassRepository;
    private LengthClassRepository $lengthClassRepository;

    public function __construct(Registry $registry)
    {
        parent::__construct($registry);
        $mapperFactory = $registry->get('mapperFactory');
        $this->weightClassRepository = $mapperFactory->get(WeightClassRepository::class);
        $this->lengthClassRepository = $mapperFactory->get(LengthClassRepository::class);
    }
    /**
     * Alpha Engine: Obtém todos os métodos de frete disponíveis e ativos para um endereço.
     * 
     * @param array $shipping_address
     * @return array
     */
    public function getMethods(array $shipping_address): array
    {
        $shipping_methods = [];

        // Alpha Engine: O ExtensionMapper agora gerencia o cache internamente por tipo
        $extensionMapper = $this->registry->get('alpha_mapper_factory')->get(ExtensionMapper::class);
        $results = $extensionMapper->getExtensionsByType('shipping');

        $sort_order = [];

        foreach ($results as $key => $value) {
            $sort_order[$key] = (int)$this->registry->get('config')->get('shipping_' . $value->getCode() . '_sort_order');
        }

        array_multisort($sort_order, SORT_ASC, $results);

        /** @var \Alpha\Model\Domain\Entities\Extension $result */
        foreach ($results as $result) {
            // Alpha Engine: Verificação de status via config nativa
            if ($this->registry->get('config')->get('shipping_' . $result->getCode() . '_status')) {
                // Invocação dinâmica da extensão (enquanto as extensões de frete não são convertidas em Mappers)
                $load = $this->registry->get('load');

                $route = 'extension/' . $result->getExtension() . '/shipping/' . $result->getCode();

                $load->model($route);

                $model_name = 'model_extension_' . $result->getExtension() . '_shipping_' . $result->getCode();

                if ($this->registry->has($model_name)) {
                    // Alpha Engine: Correção para o padrão OpenCart (extensões de frete usam getQuote)
                    $quote = $this->registry->get($model_name)->getQuote($shipping_address);

                    if ($quote) {
                        $shipping_methods[$result->getCode()] = $quote;
                    }
                }
            }
        }

        return $shipping_methods;
    }
}
