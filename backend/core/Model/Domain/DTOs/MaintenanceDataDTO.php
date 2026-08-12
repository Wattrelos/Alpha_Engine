<?php

namespace Alpha\Model\Domain\DTOs;

class MaintenanceDataDTO implements \JsonSerializable
{
    public function __construct(private readonly array $data = []) {}

    public function toArray(): array
    {
        return $this->data;
    }

    public function getTitle(): string
    {
        return $this->data['heading_title'] ?? '';
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}