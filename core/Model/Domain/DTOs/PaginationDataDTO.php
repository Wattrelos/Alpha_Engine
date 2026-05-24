<?php

namespace Alpha\Model\Domain\DTOs;

/**
 * PaginationDataDTO - Transporta o estado calculado da paginação.
 */
class PaginationDataDTO implements \JsonSerializable
{
    public function __construct(private readonly array $data = [], private readonly bool $hasMultiplePages = false) {}

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

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}