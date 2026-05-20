<?php

namespace Alpha\Model\DataTransferObject;

/**
 * ProductShowcaseDTO - Padroniza os dados de produtos para vitrines e carrosséis.
 * 
 * Melhoras Alpha Engine:
 * - Interface Fluida: Permite encadeamento de métodos no Repository.
 * - Normalização: Garante que valores nulos ou vazios sejam tratados antes da View.
 * - Tipagem Estrita: Protege o template contra erros de tipo em tempo de execução.
 */
class ProductShowcaseDTO
{
    private int $id = 0;
    private string $thumb = '';
    private string $name = '';
    private string $description = '';
    private string $price = '';
    private ?string $special = null;
    private string $href = '';
    private bool $isNew = false;
    private bool $isSale = false;
    private string $discountPercentage = '';

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function setThumb(string $thumb): self
    {
        $this->thumb = $thumb;
        return $this;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;        
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function setPrice(string $price): self
    {
        $this->price = $price;
        return $this;
    }

    public function setSpecial(?string $special): self
    {
        $this->special = $special;
        return $this;
    }

    public function setHref(string $href): self
    {
        $this->href = $href;
        return $this;
    }

    public function setIsNew(bool $isNew): self
    {
        $this->isNew = $isNew;
        return $this;
    }

    public function setIsSale(bool $isSale): self
    {
        $this->isSale = $isSale;
        return $this;
    }

    public function setDiscountPercentage(string $label): self
    {
        $this->discountPercentage = $label;
        return $this;
    }

    /**
     * Converte o DTO para um array associativo compatível com o Twig.
     * 
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id'                  => $this->id,
            'product_id'          => $this->id, // Compatibilidade com componentes legados
            'thumb'               => $this->thumb,
            'name'                => $this->name,
            'description'         => $this->description,
            'price'               => $this->price,
            'special'             => $this->special,
            'href'                => $this->href,
            'is_new'              => $this->isNew,
            'is_sale'             => $this->isSale,
            'discount_percentage' => $this->discountPercentage
        ];
    }
}