<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade CustomerGroupDescription - Traduções e detalhes do grupo de clientes.
 */
class CustomerGroupDescription extends BaseEntity
{
    private string $name = '';
    private string $description = '';

    #[ManyToOne(targetEntity: CustomerGroup::class, foreignKey: 'customerGroupId')]
    private ?CustomerGroup $customerGroup = null;

    #[ManyToOne(targetEntity: Language::class, foreignKey: 'languageId')]
    private ?Language $language = null;

    public function getCustomerGroupId(): int 
    { 
        return $this->customerGroup ? (int)$this->customerGroup->getId() : 0; 
    }
    
    public function setCustomerGroupId(int $id): self 
    { 
        if (!$this->customerGroup) {
            $this->customerGroup = new CustomerGroup();
        }
        $this->customerGroup->setId($id);
        return $this; 
    }

    public function getLanguageId(): int { return $this->language ? (int)$this->language->getId() : 0; }
    public function setLanguageId(int $id): self { 
        if (!$this->language) $this->language = new Language();
        $this->language->setId($id); return $this; 
    }

    public function getName(): string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }

    public function getDescription(): string { return $this->description; }
    public function setDescription(string $description): self { $this->description = $description; return $this; }

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