<?php

namespace Alpha\Model\DataTransferObject;

use JsonSerializable;

/**
 * BaseDTO - Classe base abstrata para todos os DTOs na Alpha Engine.
 * Fornece métodos padronizados para manipulação e serialização de dados.
 */
abstract class BaseDTO implements JsonSerializable
{
    protected array $data = [];
    protected array $errors = [];

    public function __construct(array $initialData = [])
    {
        $this->data = $initialData;
    }

    public function set(string $key, mixed $value): static
    {
        $this->data[$key] = $value;
        return $this;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    public function merge(array $bulkData): static
    {
        $this->data = array_merge($this->data, $bulkData);
        return $this;
    }

    public function toArray(): array
    {
        return $this->data;
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function __get(string $key): mixed
    {
        return $this->get($key);
    }

    public function setErrors(array $errors): self
    {
        $this->errors = $errors;
        return $this;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}