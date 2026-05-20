<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade LengthClassDescription - Descrição multi-idioma de uma unidade de medida de comprimento.
 *
 * Alpha Engine:
 * - Tipagem estrita PHP 8.4.
 * - Relacionamento ManyToOne com Language para hidratação automática.
 */
class LengthClassDescription extends BaseEntity
{
    protected int $lengthClassId = 0;
    protected int $languageId = 0;
    protected string $title = '';
    protected string $unit = '';

    #[ManyToOne(targetEntity: Language::class, foreignKey: 'languageId')]
    protected ?Language $language = null;

    public function getLengthClassId(): int
    {
        return $this->lengthClassId;
    }

    public function setLengthClassId(int $lengthClassId): self
    {
        $this->lengthClassId = $lengthClassId;
        return $this;
    }

    public function getLanguageId(): int
    {
        return $this->languageId;
    }

    public function setLanguageId(int $languageId): self
    {
        $this->languageId = $languageId;
        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getUnit(): string
    {
        return $this->unit;
    }

    public function setUnit(string $unit): self
    {
        $this->unit = $unit;
        return $this;
    }
}