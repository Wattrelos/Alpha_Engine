<?php

namespace Alpha\Model\Domain\DTOs;

/**
 * CookieDataDTO - Transporta dados para o aviso de cookies.
 */
class CookieDataDTO
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