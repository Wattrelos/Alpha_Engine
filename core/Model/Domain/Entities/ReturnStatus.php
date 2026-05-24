<?php
namespace Alpha\Model\Domain\Entities;

 use Alpha\Model\Domain\BaseEntity;
 use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade ReturnStatus
 * Define as descrições dos estados de devolução (Traduções).
 * 
 * @Table(name="return_status")
 */
class ReturnStatus extends BaseEntity
{
    private int $returnStatusId = 0;
    private string $name = '';
    private int $languageId = 0;

    #[ManyToOne(targetEntity: Language::class, foreignKey: 'languageId')]
    private ?Language $language = null;

    public function getReturnStatusId(): int
    {
        return $this->returnStatusId;
    }

    public function setReturnStatusId(int $returnStatusId): self
    {
        $this->returnStatusId = $returnStatusId;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
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

    public function getLanguage(): ?Language
    {
        return $this->language;
    }

    public function setLanguage(?Language $language): self
    {
        $this->language = $language;
        return $this;
    }
}
