<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;
use Alpha\Model\Domain\Entities\Customer\Customer;

/**
 * Entidade Review - Gerencia as avaliações de produtos feitas pelos clientes.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Feedback de Qualidade: Rastreia a satisfação do cliente com notas e texto.
 * - Tipagem PHP 8.4: Uso de int para ratings e bool para status de moderação.
 * - Relacionamentos: #[ManyToOne] para vincular a avaliação ao Produto e ao Cliente.
 */
class Review extends BaseEntity
{
    private int $productId = 0;
    private int $customerId = 0;
    private string $author = '';
    private string $text = '';
    private int $rating = 0;
    private bool $status = false;
    private string $dateAdded = '';
    private string $dateModified = '';

    #[ManyToOne(targetEntity: Product::class, foreignKey: 'productId')]
    private ?Product $product = null;

    #[ManyToOne(targetEntity: Customer::class, foreignKey: 'customerId')]
    private ?Customer $customer = null;

    public function getProductId(): int { return $this->productId; }
    public function setProductId(int $id): self { $this->productId = $id; return $this; }

    public function getCustomerId(): int { return $this->customerId; }
    public function setCustomerId(int $id): self { $this->customerId = $id; return $this; }

    public function getAuthor(): string { return $this->author; }
    public function setAuthor(string $author): self { $this->author = $author; return $this; }

    public function getText(): string { return $this->text; }
    public function setText(string $text): self { $this->text = $text; return $this; }

    public function getRating(): int { return $this->rating; }
    public function setRating(int $rating): self { $this->rating = $rating; return $this; }

    public function isStatus(): bool { return $this->status; }
    public function setStatus(bool $status): self { $this->status = $status; return $this; }

    public function getDateAdded(): string { return $this->dateAdded; }
    public function setDateAdded(string $date): self { $this->dateAdded = $date; return $this; }

    public function getDateModified(): string { return $this->dateModified; }
    public function setDateModified(string $date): self { $this->dateModified = $date; return $this; }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self { $this->product = $product; return $this; }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): self { $this->customer = $customer; return $this; }
}