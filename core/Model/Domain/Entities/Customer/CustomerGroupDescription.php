<?php
namespace Alpha\Model\Domain\Entities\Customer;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;
use Alpha\Model\Domain\Entities\Language;

/**
 * Entidade CustomerGroupDescription - Traduções e detalhes do grupo de clientes.
 */
class CustomerGroupDescription extends BaseEntity
{
    private int $customerGroupId = 0;
    private int $languageId = 0;
    private string $name = '';
    private string $description = '';

    #[ManyToOne(targetEntity: CustomerGroup::class, foreignKey: 'customerGroupId')]
    private ?CustomerGroup $customerGroup = null;

    #[ManyToOne(targetEntity: Language::class, foreignKey: 'languageId')]
    private ?Language $language = null;

    public function getCustomerGroupId(): int { return $this->customerGroupId; }
    public function setCustomerGroupId(int $customerGroupId): self { $this->customerGroupId = $customerGroupId; return $this; }

    public function getLanguageId(): int { return $this->languageId; }
    public function setLanguageId(int $languageId): self { $this->languageId = $languageId; return $this; }

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