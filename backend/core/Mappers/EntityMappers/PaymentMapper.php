<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;

/**
 * PaymentMapper - Orquestra a listagem e validação de métodos de pagamento (Alpha Engine).
 * 
 * Melhoras Alpha Engine:
 * - Desacoplamento: Move a lógica de descoberta de extensões para fora do controlador.
 * - Centralização: Garante que a ordenação e validação de status sigam o padrão Alpha.
 * - Performance: Utiliza o registry centralizado para carga dinâmica de modelos de extensão.
 */
class PaymentMapper extends BaseMapper {

    protected string $tableName = 'extension';

    /**
     * Alpha Engine: Obtém todos os métodos de pagamento disponíveis e ativos para um endereço.
     * 
     * @param array $payment_address
     * @return array
     */
    public function getMethods(array $payment_address): array {
        $payment_methods = [];

        $extensionRepo = \Alpha\Model\Domain\Repositories\RepositoryFactory::getInstance()->get(\Alpha\Model\Domain\Repositories\ExtensionRepository::class);
        $results = $extensionRepo->getExtensionsByType('payment');

        $sort_order = [];

        foreach ($results as $key => $value) {
            $sort_order[$key] = (int)$this->container->get('config')->get('payment_' . $value->getCode() . '_sort_order');
        }

        array_multisort($sort_order, SORT_ASC, $results);

        /** @var \Alpha\Model\Domain\Entities\Extension $result */
        foreach ($results as $result) {
            // Alpha Engine: Verificação de status via config nativa
            if ($this->container->get('config')->get('payment_' . $result->getCode() . '_status')) {
                // Invocação dinâmica da extensão (enquanto as extensões de pagamento não são convertidas em Mappers)
                $load = $this->container->get('load');
                
                $extension_route = 'extension/' . $result->getExtension() . '/payment/' . $result->getCode();
                
                $load->model($extension_route);
                
                $model_name = 'model_extension_' . $result->getExtension() . '_payment_' . $result->getCode();
                
                if ($this->container->has($model_name)) {
                    $method = $this->container->get($model_name)->getMethods($payment_address);

                    if ($method) {
                        $payment_methods[$result['code']] = $method;
                    }
                }
            }
        }

        return $payment_methods;
    }
}