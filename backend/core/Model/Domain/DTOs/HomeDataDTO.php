<?php

namespace Alpha\Model\Domain\DTOs;

/**
 * HomeDataDTO - Objeto de transferência de dados para a Home Page.
 */
class HomeDataDTO implements \JsonSerializable
{
    private string $title = '';
    private string $description = '';

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'title'       => $this->title,
            'description' => $this->description
        ];
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}