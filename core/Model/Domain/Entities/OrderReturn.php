<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;
use Alpha\Model\Domain\Entities\Customer\Customer;

/**
 * Entidade OrderReturn (Mapeada para a tabela "return")
 * Registra e rastreia as devoluções de produtos (RMA).
 * O nome foi adaptado porque "return" é palavra reservada em PHP.
 * 
 * @Table(name="return")
 */
class OrderReturn extends BaseEntity implements \Alpha\Model\Domain\VersionedEntityInterface
{
    public const TABLE_NAME = 'product_return';
    private int $orderId = 0;
    private int $customerId = 0;
    private string $firstname = '';
    private string $lastname = '';
    private string $email = '';
    private string $telephone = '';
    private string $cpfCnpj = '';
    private string $persontype = '';
    private int $productId = 0;
    private string $product = '';
    private string $model = '';
    private int $quantity = 0;
    private bool $opened = false;
    private int $returnReasonId = 0;
    private int $returnActionId = 0;
    private int $returnStatusId = 0;
    private string $comment = '';
    private string $dateOrdered = '';
    private string $dateAdded = '';
    private string $dateModified = '';
    private int $version = 1;

    #[ManyToOne(targetEntity: Order::class, foreignKey: 'orderId')]
    private ?Order $order = null;

    #[ManyToOne(targetEntity: Customer::class, foreignKey: 'customerId')]
    private ?Customer $customer = null;

    #[ManyToOne(targetEntity: Product::class, foreignKey: 'productId')]
    private ?Product $relatedProduct = null;

    public function getOrderId(): int { return $this->orderId; }
    public function setOrderId(int $val): self { $this->orderId = $val; return $this; }

    public function getCustomerId(): int { return $this->customerId; }
    public function setCustomerId(int $val): self { $this->customerId = $val; return $this; }

    public function getFirstname(): string { return $this->firstname; }
    public function setFirstname(string $val): self { $this->firstname = $val; return $this; }

    public function getLastname(): string { return $this->lastname; }
    public function setLastname(string $val): self { $this->lastname = $val; return $this; }

    public function getEmail(): string { return $this->email; }
    public function setEmail(string $val): self { $this->email = $val; return $this; }

    public function getTelephone(): string { return $this->telephone; }
    public function setTelephone(string $val): self { $this->telephone = $val; return $this; }

    public function getCpfCnpj(): string { return $this->cpfCnpj; }
    public function setCpfCnpj(string $val): self { $this->cpfCnpj = $val; return $this; }

    public function getPersontype(): string { return $this->persontype; }
    public function setPersontype(string $val): self { $this->persontype = $val; return $this; }

    public function getProductId(): int { return $this->productId; }
    public function setProductId(int $val): self { $this->productId = $val; return $this; }

    public function getProduct(): string { return $this->product; }
    public function setProduct(string $val): self { $this->product = $val; return $this; }

    public function getModel(): string { return $this->model; }
    public function setModel(string $val): self { $this->model = $val; return $this; }

    public function getQuantity(): int { return $this->quantity; }
    public function setQuantity(int $val): self { $this->quantity = $val; return $this; }

    public function isOpened(): bool { return $this->opened; }
    public function setOpened(bool $val): self { $this->opened = $val; return $this; }

    public function getReturnReasonId(): int { return $this->returnReasonId; }
    public function setReturnReasonId(int $val): self { $this->returnReasonId = $val; return $this; }

    public function getReturnActionId(): int { return $this->returnActionId; }
    public function setReturnActionId(int $val): self { $this->returnActionId = $val; return $this; }

    public function getReturnStatusId(): int { return $this->returnStatusId; }
    public function setReturnStatusId(int $val): self { $this->returnStatusId = $val; return $this; }

    public function getComment(): string { return $this->comment; }
    public function setComment(string $val): self { $this->comment = $val; return $this; }

    public function getDateOrdered(): string { return $this->dateOrdered; }
    public function setDateOrdered(string $val): self { $this->dateOrdered = $val; return $this; }

    public function getDateAdded(): string { return $this->dateAdded; }
    public function setDateAdded(string $val): self { $this->dateAdded = $val; return $this; }

    public function getDateModified(): string { return $this->dateModified; }
    public function setDateModified(string $val): self { $this->dateModified = $val; return $this; }

    public function getVersion(): int { return $this->version; }
    public function setVersion(int $val): self { $this->version = $val; return $this; }

    public function getOrder(): ?Order { return $this->order; }
    public function getCustomer(): ?Customer { return $this->customer; }
    public function getRelatedProduct(): ?Product { return $this->relatedProduct; }
}