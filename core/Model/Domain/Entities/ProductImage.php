<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade ProductImage - Gerencia a galeria de fotos secundárias do produto.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Ordenação Garantida: Tipagem estrita em sortOrder para evitar inconsistências visuais no carrossel de imagens.
 * - Integridade Relacional: Atributo #[ManyToOne] configurado para permitir que o DAO limpe imagens órfãs automaticamente em deleções.
 * - Normalização: Caminho da imagem tratado como string para compatibilidade total com o sistema de arquivos do OpenCart.
 */
class ProductImage extends BaseEntity
{
    private int $productId = 0;
    private string $image = '';
    private int $sortOrder = 0;

    #[ManyToOne(targetEntity: Product::class, foreignKey: 'productId')]
    private ?Product $product = null;

    public function getProductId(): int { return $this->productId; }
    public function setProductId(int $value): self { $this->productId = $value; return $this; }

    public function getImage(): string { return $this->image; }
    public function setImage(string $image): self { $this->image = $image; return $this; }

    public function getSortOrder(): int { return $this->sortOrder; }
    public function setSortOrder(int $sortOrder): self { $this->sortOrder = $sortOrder; return $this; }

    public function getProduct(): ?Product { return $this->product; }
    public function setProduct(?Product $product): self { $this->product = $product; return $this; }
}