<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade ProductDescription - Traduções e metadados SEO do produto.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - SEO Nativo: Isolamento de metadados textuais, permitindo indexação independente por idioma sem sobrecarregar a entidade principal de estoque.
 * - Tipagem Estrita: Propriedades string inicializadas para prevenir erros de 'null pointer' em motores de template.
 * - Injeção de Contexto: Propriedade productId mapeada para permitir que o DAO vincule automaticamente a tradução ao seu pai.
 */
class ProductDescription extends BaseEntity
{
    private int $productId = 0;
    private int $languageId = 0;
    private string $name = '';
    private string $description = '';
    private string $tag = '';
    private string $metaTitle = '';
    private string $metaDescription = '';
    private string $metaKeyword = '';

    #[ManyToOne(targetEntity: Product::class, foreignKey: 'productId')]
    private ?Product $product = null;

    #[ManyToOne(targetEntity: Language::class, foreignKey: 'languageId')]
    private ?Language $language = null;

    public function getProductId(): int { return $this->productId; }
    public function setProductId(int $value): self { $this->productId = $value; return $this; }

    public function getLanguageId(): int { return $this->languageId; }
    public function setLanguageId(int $value): self { $this->languageId = $value; return $this; }

    public function getName(): string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }

    public function getDescription(): string { return $this->description; }
    public function setDescription(string $description): self { $this->description = $description; return $this; }

    public function getTag(): string { return $this->tag; }
    public function setTag(string $tag): self { $this->tag = $tag; return $this; }

    public function getMetaTitle(): string { return $this->metaTitle; }
    public function setMetaTitle(string $metaTitle): self { $this->metaTitle = $metaTitle; return $this; }

    public function getMetaDescription(): string { return $this->metaDescription; }
    public function setMetaDescription(string $metaDescription): self { $this->metaDescription = $metaDescription; return $this; }

    public function getMetaKeyword(): string { return $this->metaKeyword; }
    public function setMetaKeyword(string $metaKeyword): self { $this->metaKeyword = $metaKeyword; return $this; }

    public function getProduct(): ?Product { return $this->product; }
    public function setProduct(?Product $product): self { $this->product = $product; return $this; }

    public function getLanguage(): ?Language { return $this->language; }
    public function setLanguage(?Language $language): self { $this->language = $language; return $this; }
}