<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

class ReturnActionDescription extends BaseEntity
{
    private int $returnActionId = 0;
    private int $languageId = 0;
    private string $name = '';

    #[ManyToOne(targetEntity: Language::class, foreignKey: 'languageId')]
    private ?Language $language = null;

    public function getReturnActionId(): int { return $this->returnActionId; }
    public function setReturnActionId(int $id): self { $this->returnActionId = $id; return $this; }

    public function getLanguageId(): int { return $this->languageId; }
    public function setLanguageId(int $id): self { $this->languageId = $id; return $this; }

    public function getName(): string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }

    public function getLanguage(): ?Language { return $this->language; }
    public function setLanguage(?Language $lang): self { $this->language = $lang; return $this; }
}