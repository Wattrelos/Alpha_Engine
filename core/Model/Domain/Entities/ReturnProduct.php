<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\Attributes\ManyToOne;
use Alpha\Model\Domain\Attributes\OneToMany;
use Alpha\Model\Domain\BaseEntity;
use Opencart\catalog\model\domain\entities\Customer;
use Opencart\catalog\model\domain\entities\ReturnReason;
use Opencart\catalog\model\domain\entities\ReturnStatus;
use Opencart\catalog\model\domain\entities\ReturnHistory;

class ReturnProduct extends BaseEntity {
    // @ORM\Column(type="string", length=32, nullable=false)    */
    private $firstname;
    // @ORM\Column(type="string", length=32, nullable=false)
    private $lastname;
    // @ORM\Column(type="string", length=96, nullable=false)
    private $email;
    // @ORM\Column(type="string", length=32, nullable=false)
    private $telephone;
     // @ORM\Column(type="string", length=255, nullable=false)
    private $product;
     // @ORM\Column(type="string", length=64, nullable=false)
    private $model;
     // @ORM\Column(type="integer", nullable=false)
    private $quantity;
     // @ORM\Column(type="boolean", nullable=false)
    private $opened;
    // @ORM\Column(type="text", nullable=false)
    private $comment;
    // @ORM\Column(type="date", nullable=false)
    private $dateOrdered;
     // @ORM\Column(type="date", nullable=false)
    private $dateAdded;
    // @ORM\Column(type="date", nullable=false)
    private $dateModified;
    // @ORM\OneToMany(targetEntity=\ReturnHistory::class, mappedBy="ReturnProduct")
    // private $ReturnHistory;
    #[OneToMany(targetEntity: ReturnHistory::class, foreignKey: 'returnHistoryId')]
    private array $returnHistory = [];

    // @ORM\ManyToOne(targetEntity=\Order::class, inversedBy="ReturnProduct")
    // @ORM\JoinColumn(name="orderId", referencedColumnName="orderId", nullable=false, onDelete="restrict")
    #[ManyToOne(targetEntity: Order::class, foreignKey: 'orderId')]
    private ?Order $order = null;

    // @ORM\ManyToOne(targetEntity=\Product::class, inversedBy="ReturnProduct")
    // @ORM\JoinColumn(name="productId", referencedColumnName="productId", nullable=false, onDelete="restrict")
    #[ManyToOne(targetEntity: Product::class, foreignKey: 'productId')]
    private ?Product $productEntity = null;

    // @ORM\ManyToOne(targetEntity=\Customer::class, inversedBy="ReturnProductss")
    // @ORM\JoinColumn(name="customerId", referencedColumnName="customerId", nullable=false, onDelete="restrict")
    // private $Customer;
     #[ManyToOne(targetEntity: Customer::class, foreignKey: 'customerId')]
    private ?Customer $customer = null;

    // @ORM\ManyToOne(targetEntity=\ReturnReason::class, inversedBy="ReturnProductss")
    // @ORM\JoinColumn(name="returnReasonId",referencedColumnName="returnReasonId",nullable=false,onDelete="restrict")
    // private $ReturnReason;
    #[ManyToOne(targetEntity: ReturnReason::class, foreignKey: 'returnReasonId')]
    private ?ReturnReason $returnReason = null;

    // @ORM\ManyToOne(targetEntity=\ReturnAction::class, inversedBy="ReturnProductss")
    // @ORM\JoinColumn(name="returnActionId",referencedColumnName="returnActionId",nullable=false,onDelete="restrict")
     #[ManyToOne(targetEntity: ReturnAction::class, foreignKey: 'returnActionId')]
    private ?ReturnAction $returnAction = null;

    // @ORM\ManyToOne(targetEntity=\ReturnStatus::class, inversedBy="ReturnProductss")
    // @ORM\JoinColumn(name="returnStatusId",referencedColumnName="returnStatusId",nullable=false,onDelete="restrict")
    // private $ReturnStatus;
    #[ManyToOne(targetEntity: ReturnStatus::class, foreignKey: 'returnStatusId')]
    private ?ReturnStatus $returnStatus = null;

    public function __construct() {
        parent::__construct();
        // Apenas inicializamos as COLEÇÕES (OneToMany) como arrays vazios
        $this->returnHistory = [];
    }


    public function getFirstname()
    {
        return $this->firstname;
    }
    public function setFirstname($value)
    {
        $this->firstname = $value;
        return $this;
    }
    public function getLastname()
    {
        return $this->lastname;
    }
    public function setLastname($value)
    {
        $this->lastname = $value;
        return $this;
    }
    public function getEmail()
    {
        return $this->email;
    }
    public function setEmail($value)
    {
        $this->email = $value;
        return $this;
    }
    public function getTelephone()
    {
        return $this->telephone;
    }
    public function setTelephone($value)
    {
        $this->telephone = $value;
        return $this;
    }
    public function getProduct()
    {
        return $this->product;
    }
    public function setProduct($value)
    {
        $this->product = $value;
        return $this;
    }
    public function getModel()
    {
        return $this->model;
    }
    public function setModel($value)
    {
        $this->model = $value;
        return $this;
    }
    public function getQuantity()
    {
        return $this->quantity;
    }
    public function setQuantity($value)
    {
        $this->quantity = $value;
        return $this;
    }
    public function getOpened()
    {
        return $this->opened;
    }
    public function setOpened($value)
    {
        $this->opened = $value;
        return $this;
    }
    public function getComment()
    {
        return $this->comment;
    }
    public function setComment($value)
    {
        $this->comment = $value;
        return $this;
    }
    public function getDateOrdered()
    {
        return $this->dateOrdered;
    }
    public function setDateOrdered($value)
    {
        $this->dateOrdered = $value;
        return $this;
    }
    public function getDateAdded()
    {
        return $this->dateAdded;
    }
    public function setDateAdded($value)
    {
        $this->dateAdded = $value;
        return $this;
    }
    public function getDateModified()
    {
        return $this->dateModified;
    }
    public function setDateModified($value)
    {
        $this->dateModified = $value;
        return $this;
    }

    public function getReturnHistory(): array
    {
        return $this->returnHistory;
    }
    public function setReturnHistory(array $value): self
    {
        $this->returnHistory = $value;
        return $this;
    }
    public function getOrder(): ?Order
    {
        return $this->order;
    }
    public function setOrder(?Order $value): self
    {
        $this->order = $value;
        return $this;
    }
    public function getProductEntity(): ?Product
    {
        return $this->productEntity;
    }
    public function setProductEntity(?Product $value): self
    {
        $this->productEntity = $value;
        return $this;
    }
    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }
    public function setCustomer(?Customer $value): self
    {
        $this->customer = $value;
        return $this;
    }
    public function getReturnReason(): ?ReturnReason
    {
        return $this->returnReason;
    }
    public function setReturnReason(?ReturnReason $value): self
    {
        $this->returnReason = $value;
        return $this;
    }
    public function getReturnAction(): ?ReturnAction
    {
        return $this->returnAction;
    }
    public function setReturnAction(?ReturnAction $value): self
    {
        $this->returnAction = $value;
        return $this;
    }
    public function getReturnStatus(): ?ReturnStatus
    {
        return $this->returnStatus;
    }
    public function setReturnStatus(?ReturnStatus $value): self
    {
        $this->returnStatus = $value;
        return $this;
    }
}
