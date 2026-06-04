<?php

namespace Alpha\Services\Payments;

/**
 * DTO genérico que encapsula a resposta de qualquer gateway de pagamento.
 */
readonly class PaymentResult
{
    public function __construct(
        private bool $success,
        private string $message,
        public ?string $transactionId = null,
        public array $additionalData = []
    ) {
    }

    public function isSuccessful(): bool
    {
        return $this->success;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
