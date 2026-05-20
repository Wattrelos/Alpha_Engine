<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade ProductAttribute - Valores específicos da ficha técnica do produto.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Localização Nativa: Suporte a múltiplos idiomas para o texto do atributo (ex: "Algodão" vs "Cotton").
 * - Mapeamento de Contexto: Vinculação tripla (Product, Attribute, Language) que o DAO resolve via injeção de dependência.
 * - Sanitização: Propriedade text inicializada para prevenir erros de renderização em especificações vazias.
 */
class ProductAttribute extends BaseEntity
{
    private int $productId = 0;
    private int $attributeId = 0;
    private int $languageId = 0;
    private string $text = '';

    #[ManyToOne(targetEntity: Product::class, foreignKey: 'productId')]
    private ?Product $product = null;

    #[ManyToOne(targetEntity: Attribute::class, foreignKey: 'attributeId')]
    private ?Attribute $attribute = null;

    #[ManyToOne(targetEntity: Language::class, foreignKey: 'languageId')]
    private ?Language $language = null;

    public function getProductId(): int { return $this->productId; }
    public function setProductId(int $value): self { $this->productId = $value; return $this; }

    public function getAttributeId(): int { return $this->attributeId; }
    public function setAttributeId(int $value): self { $this->attributeId = $value; return $this; }

    public function getLanguageId(): int { return $this->languageId; }
    public function setLanguageId(int $value): self { $this->languageId = $value; return $this; }

    public function getText(): string { return $this->text; }
    public function setText(string $text): self { $this->text = $text; return $this; }

    public function getProduct(): ?Product { return $this->product; }
    public function setProduct(?Product $product): self { $this->product = $product; return $this; }

    public function getAttribute(): ?Attribute { return $this->attribute; }
    public function setAttribute(?Attribute $attribute): self { $this->attribute = $attribute; return $this; }

    public function getLanguage(): ?Language { return $this->language; }
    public function setLanguage(?Language $language): self { $this->language = $language; return $this; }
}