<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

class ReturnReasonDescription extends BaseEntity
{
    private int $returnReasonId = 0;
    private int $languageId = 0;
    private string $name = '';

    #[ManyToOne(targetEntity: Language::class, foreignKey: 'languageId')]
    private ?Language $language = null;

    public function getReturnReasonId(): int { return $this->returnReasonId; }
    public function setReturnReasonId(int $id): self { $this->returnReasonId = $id; return $this; }

    public function getLanguageId(): int { return $this->languageId; }
    public function setLanguageId(int $id): self { $this->languageId = $id; return $this; }

    public function getName(): string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }

    public function getLanguage(): ?Language { return $this->language; }
    public function setLanguage(?Language $lang): self { $this->language = $lang; return $this; }
}