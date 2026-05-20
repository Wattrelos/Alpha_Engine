<?php

namespace Alpha\Model\Domain\DTOs;

/**
 * PaginationDataDTO - Transporta o estado calculado da paginação.
 */
class PaginationDataDTO
{
    public function __construct(private array $data, private bool $hasMultiplePages) {}

    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * Alpha Engine: Indica se o componente deve ser renderizado.
     */
    public function shouldRender(): bool
    {
        return $this->hasMultiplePages || !empty($this->data['back']);
    }
}