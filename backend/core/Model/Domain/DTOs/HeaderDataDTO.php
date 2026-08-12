<?php

namespace Alpha\Model\Domain\DTOs;

/**
 * HeaderDataDTO - Objeto de transferência de dados para o cabeçalho.
 */
class HeaderDataDTO implements \JsonSerializable
{
    public function __construct(private readonly array $data = []) {}

    public function toArray(): array
    {
        return $this->data;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}