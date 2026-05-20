<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\OneToMany;

/**
 * Entidade VoucherTheme - Define o layout visual de um cartão de presente.
 */
class VoucherTheme extends BaseEntity
{
    private string $image = '';

    #[OneToMany(targetEntity: VoucherThemeDescription::class, foreignKey: 'voucherThemeId')]
    private array $descriptions = [];

    public function getImage(): string { return $this->image; }
    public function setImage(string $value): self { $this->image = $value; return $this; }

    /** @return VoucherThemeDescription[] */
    public function getDescriptions(): array { return $this->descriptions; }
    public function setDescriptions(array $values): self { $this->descriptions = $values; return $this; }
}