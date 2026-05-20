<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade OrderReturn - Gerencia solicitações de devolução de produtos.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Rastreabilidade: Vinculação direta com o Pedido e Cliente original.
 * - Integridade de Produto: Identificação clara do item e modelo devolvido.
 * - Triagem: Uso de booleanos estritos para o estado 'opened' (aberto).
 * - Flexibilidade: Relacionamentos para categorizar motivos e ações de resolução.
 */
class OrderReturn extends BaseEntity
{
    private string $firstname = '';
    private string $lastname = '';
    private string $email = '';
    private string $telephone = '';
    private string $product = '';
    private string $model = '';
    private int $quantity = 0;
    private bool $opened = false;
    private int $returnStatusId = 0;
    private string $comment = '';
    private string $dateAdded = '';
    private string $dateModified = '';

    #[ManyToOne(targetEntity: Order::class, foreignKey: 'orderId')]
    private ?Order $order = null;

    #[ManyToOne(targetEntity: Customer::class, foreignKey: 'customerId')]
    private ?Customer $customer = null;

    #[ManyToOne(targetEntity: ReturnReason::class, foreignKey: 'returnReasonId')]
    private ?ReturnReason $returnReason = null;

    #[ManyToOne(targetEntity: ReturnAction::class, foreignKey: 'returnActionId')]
    private ?ReturnAction $returnAction = null;

    public function getOrderId(): int { return $this->order ? (int)$this->order->getId() : 0; }
    public function setOrderId(int $id): self { 
        if (!$this->order) $this->order = new Order();
        $this->order->setId($id); 
        return $this; 
    }

    public function getCustomerId(): int { return $this->customer ? (int)$this->customer->getId() : 0; }
    public function setCustomerId(int $id): self { 
        if (!$this->customer) {
            $this->customer = new Customer();
        }
        $this->customer->setId($id);
        return $this; 
    }

    public function getFirstname(): string { return $this->firstname; }
    public function setFirstname(string $value): self { $this->firstname = $value; return $this; }

    public function getLastname(): string { return $this->lastname; }
    public function setLastname(string $value): self { $this->lastname = $value; return $this; }

    public function getEmail(): string { return $this->email; }
    public function setEmail(string $value): self { $this->email = $value; return $this; }

    public function getTelephone(): string { return $this->telephone; }
    public function setTelephone(string $value): self { $this->telephone = $value; return $this; }

    public function getProduct(): string { return $this->product; }
    public function setProduct(string $value): self { $this->product = $value; return $this; }

    public function getModel(): string { return $this->model; }
    public function setModel(string $value): self { $this->model = $value; return $this; }

    public function getQuantity(): int { return $this->quantity; }
    public function setQuantity(int $value): self { $this->quantity = $value; return $this; }

    public function isOpened(): bool { return $this->opened; }
    public function setOpened(bool|int $value): self { $this->opened = (bool)$value; return $this; }

    public function getReturnReasonId(): int { return $this->returnReason ? (int)$this->returnReason->getId() : 0; }
    public function setReturnReasonId(int $id): self { 
        if (!$this->returnReason) $this->returnReason = new ReturnReason();
        $this->returnReason->setId($id);
        return $this; 
    }

    public function getReturnActionId(): int { return $this->returnAction ? (int)$this->returnAction->getId() : 0; }
    public function setReturnActionId(int $id): self { 
        if (!$this->returnAction) $this->returnAction = new ReturnAction();
        $this->returnAction->setId($id);
        return $this; 
    }

    public function getReturnStatusId(): int { return $this->returnStatusId; }
    public function setReturnStatusId(int $id): self { $this->returnStatusId = $id; return $this; }

    public function getComment(): string { return $this->comment; }
    public function setComment(string $value): self { $this->comment = $value; return $this; }

    public function getDateAdded(): string { return $this->dateAdded; }
    public function setDateAdded(string $value): self { $this->dateAdded = $value; return $this; }

    public function getDateModified(): string { return $this->dateModified; }
    public function setDateModified(string $value): self { $this->dateModified = $value; return $this; }

    // Objetos Relacionados

    public function getOrder(): ?Order { return $this->order; }
    public function setOrder(?Order $order): self { $this->order = $order; return $this; }

    public function getCustomer(): ?Customer { return $this->customer; }
    public function setCustomer(?Customer $customer): self { $this->customer = $customer; return $this; }

    public function getReturnReason(): ?ReturnReason { return $this->returnReason; }
    public function setReturnReason(?ReturnReason $reason): self { $this->returnReason = $reason; return $this; }

    public function getReturnAction(): ?ReturnAction { return $this->returnAction; }
    public function setReturnAction(?ReturnAction $action): self { $this->returnAction = $action; return $this; }
}