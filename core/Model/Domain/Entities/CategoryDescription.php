<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade CategoryDescription - Metadados e traduções das categorias.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Localização SEO: Campos de meta title, description e keyword isolados por idioma para SEO internacional.
 * - Injeção Automática: Propriedade categoryId configurada para hidratação automática via processAssociations do DAO.
 * - Sanitização: Propriedades inicializadas para evitar retornos nulos em templates legados.
 */
class CategoryDescription extends BaseEntity
{
    private int $categoryId = 0;
    private int $languageId = 0;
    private string $name = '';
    private string $description = '';
    private string $metaTitle = '';
    private string $metaDescription = '';
    private string $metaKeyword = '';

    #[ManyToOne(targetEntity: Category::class, foreignKey: 'categoryId')]
    private ?Category $category = null;

    #[ManyToOne(targetEntity: Language::class, foreignKey: 'languageId')]
    private ?Language $language = null;

    public function getCategoryId(): int { return $this->categoryId; }
    public function setCategoryId(int $value): self { $this->categoryId = $value; return $this; }

    public function getLanguageId(): int { return $this->languageId; }
    public function setLanguageId(int $value): self { $this->languageId = $value; return $this; }

    public function getName(): string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }

    public function getDescription(): string { return $this->description; }
    public function setDescription(string $description): self { $this->description = $description; return $this; }

    public function getMetaTitle(): string { return $this->metaTitle; }
    public function setMetaTitle(string $metaTitle): self { $this->metaTitle = $metaTitle; return $this; }

    public function getMetaDescription(): string { return $this->metaDescription; }
    public function setMetaDescription(string $metaDescription): self { $this->metaDescription = $metaDescription; return $this; }

    public function getMetaKeyword(): string { return $this->metaKeyword; }
    public function setMetaKeyword(string $metaKeyword): self { $this->metaKeyword = $metaKeyword; return $this; }

    public function getCategory(): ?Category { return $this->category; }
    public function setCategory(?Category $category): self { $this->category = $category; return $this; }

    public function getLanguage(): ?Language { return $this->language; }
    public function setLanguage(?Language $language): self { $this->language = $language; return $this; }
}