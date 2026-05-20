<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade VoucherThemeDescription - Nome localizado para o tema do presente.
 */
class VoucherThemeDescription extends BaseEntity
{
    private int $voucherThemeId = 0;
    private int $languageId = 0;
    private string $name = '';

    #[ManyToOne(targetEntity: Language::class, foreignKey: 'languageId')]
    private ?Language $language = null;

    public function getVoucherThemeId(): int { return $this->voucherThemeId; }
    public function setVoucherThemeId(int $value): self { $this->voucherThemeId = $value; return $this; }

    public function getLanguageId(): int { return $this->languageId; }
    public function setLanguageId(int $value): self { $this->languageId = $value; return $this; }

    public function getName(): string { return $this->name; }
    public function setName(string $value): self { $this->name = $value; return $this; }

    public function getLanguage(): ?Language { return $this->language; }
    public function setLanguage(?Language $lang): self { $this->language = $lang; return $this; }
}