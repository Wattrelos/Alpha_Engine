<?php
namespace Alpha\Model\Domain\Entities;

 use Alpha\Model\Domain\BaseEntity;
 use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade InformationToLayout - Define qual layout uma página CMS utiliza em determinada loja.
 */
class InformationToLayout extends BaseEntity
{
    private int $informationId = 0;
    private int $storeId = 0;
    private int $layoutId = 0;

    #[ManyToOne(targetEntity: Information::class, foreignKey: 'informationId')]
    private ?Information $information = null;

    #[ManyToOne(targetEntity: Store::class, foreignKey: 'storeId')]
    private ?Store $store = null;

    #[ManyToOne(targetEntity: Layout::class, foreignKey: 'layoutId')]
    private ?Layout $layout = null;

    public function getInformationId(): int
    {
        return $this->informationId;
    }

    public function setInformationId(int $value): self
    {
        $this->informationId = $value;
        return $this;
    }

    public function getStoreId(): int
    {
        return $this->storeId;
    }

    public function setStoreId(int $value): self
    {
        $this->storeId = $value;
        return $this;
    }

    public function getLayoutId(): int
    {
        return $this->layoutId;
    }

    public function setLayoutId(int $value): self
    {
        $this->layoutId = $value;
        return $this;
    }

    public function getInformation(): ?Information
    {
        return $this->information;
    }

    public function setInformation(?Information $information): self
    {
        $this->information = $information;
        return $this;
    }

    public function getStore(): ?Store
    {
        return $this->store;
    }

    public function setStore(?Store $store): self
    {
        $this->store = $store;
        return $this;
    }

    public function getLayout(): ?Layout
    {
        return $this->layout;
    }

    public function setLayout(?Layout $layout): self
    {
        $this->layout = $layout;
        return $this;
    }
}
