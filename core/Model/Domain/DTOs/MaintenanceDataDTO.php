<?php

namespace Alpha\Model\Domain\DTOs;

class MaintenanceDataDTO
{
    public function __construct(private array $data) {}

    public function toArray(): array
    {
        return $this->data;
    }

    public function getTitle(): string
    {
        return $this->data['heading_title'] ?? '';
    }
}