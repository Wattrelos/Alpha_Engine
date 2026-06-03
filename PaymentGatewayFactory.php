<?php

namespace Alpha\Core\Payment;

use InvalidArgumentException;
use LogicException;
use Psr\Container\ContainerInterface;

/**
 * Fábrica responsável por instanciar dinamicamente o gateway de pagamento correto.
 */
class PaymentGatewayFactory
{
    public function __construct(
        private readonly ContainerInterface $container
    ) {
    }

    public function make(string $code): PaymentGatewayInterface
    {
        // Mapeamento futuro de módulos ativos na loja. 
        // Em um estágio avançado, isso pode vir do banco de dados (tbkk_extension).
        $gateways = [
            // 'pix'          => \Alpha\Core\Payment\Gateways\PixGateway::class,
            // 'mercadopago'  => \Alpha\Core\Payment\Gateways\MercadoPagoGateway::class,
        ];

        if (!isset($gateways[$code])) {
            throw new InvalidArgumentException("Gateway de pagamento [{$code}] não suportado ou não configurado no sistema.");
        }

        // Instancia o gateway injetando suas dependências via Container (Slim/PHP-DI)
        $gateway = $this->container->get($gateways[$code]);

        if (!$gateway instanceof PaymentGatewayInterface) {
            throw new LogicException("O gateway resolvido para [{$code}] deve obrigatoriamente implementar PaymentGatewayInterface.");
        }

        return $gateway;
    }
}