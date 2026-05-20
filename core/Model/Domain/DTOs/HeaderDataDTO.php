<?php

namespace Alpha\Model\Domain\DTOs;

/**
 * HeaderDataDTO - Objeto de transferência de dados para o cabeçalho.
 */
class HeaderDataDTO
{
    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function toArray(): array
    {
        return $this->data;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }
}