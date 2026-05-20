<?php
namespace Alpha\Model\Domain\Entities;

 use Alpha\Model\Domain\BaseEntity;
 use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade InformationDescription - Representa as traduções e metadados das páginas CMS.
 */
class InformationDescription extends BaseEntity
{
    private int $informationId = 0;
    private string $title = '';
    private string $description = '';
    private string $metaTitle = '';
    private string $metaDescription = '';
    private string $metaKeyword = '';

    #[ManyToOne(targetEntity: Language::class, foreignKey: 'languageId')]
    private ?Language $language = null;
    /**
     * O DAO utiliza este setter para injetar o ID da Information pai.
     */
    public function getInformationId(): int
    {
        return $this->informationId;
    }

    public function setInformationId(int $value): self
    {
        $this->informationId = $value;
        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $value): self
    {
        $this->title = $value;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $value): self
    {
        $this->description = $value;
        return $this;
    }

    public function getMetaTitle(): string
    {
        return $this->metaTitle;
    }

    public function setMetaTitle(string $value): self
    {
        $this->metaTitle = $value;
        return $this;
    }

    public function getMetaDescription(): string
    {
        return $this->metaDescription;
    }

    public function setMetaDescription(string $value): self
    {
        $this->metaDescription = $value;
        return $this;
    }

    public function getMetaKeyword(): string
    {
        return $this->metaKeyword;
    }

    public function setMetaKeyword(string $value): self
    {
        $this->metaKeyword = $value;
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
