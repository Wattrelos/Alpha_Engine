<?php

namespace Alpha\Core\Payment;

/**
 * Contrato rigoroso para todos os módulos de pagamento da Alpha Engine.
 */
interface PaymentGatewayInterface
{
    /**
     * Processa a cobrança de um pedido.
     *
     * @param mixed $order O objeto ou DTO do pedido recuperado do banco.
     * @return PaymentResult
     */
    public function charge(mixed $order): PaymentResult;

    /**
     * Retorna o código de identificação do módulo (ex: 'pix', 'stripe').
     */
    public function getCode(): string;
}