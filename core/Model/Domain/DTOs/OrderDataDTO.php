<?php
namespace Alpha\Model\Domain\DTOs;

/**
 * Class OrderDataDTO
 * 
 * Objeto de Transferência de Dados para persistência de pedidos na Alpha Engine.
 * Centraliza a estrutura de dados necessária para criar um registro de Order.
 */
class OrderDataDTO implements \JsonSerializable {
    public function __construct(private readonly array $data = []) {}

    /**
     * Recupera um valor específico do DTO ou um fallback.
     */
    public function get(string $key, mixed $default = null): mixed {
        return $this->data[$key] ?? $default;
    }

    /**
     * Retorna o array completo de dados brutos para o Mapper.
     */
    public function toArray(): array {
        return $this->data;
    }

    /**
     * Validação básica de integridade do DTO.
     */
    public function isValid(): bool {
        return !empty($this->data['customer_id']) && 
               !empty($this->data['products']) && 
               isset($this->data['total']) &&
               !empty($this->data['payment_code']);
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}