<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade StockStatus - Define as mensagens de disponibilidade de estoque.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Localização: Vínculo ManyToOne com Language para suporte multi-idioma nativo.
 * - Tipagem PHP 8.4: Propriedades tipadas e interface fluida (self-return).
 * - Integridade: Nome inicializado para prevenir erros de renderização no frontend.
 */
class StockStatus extends BaseEntity
{
    private int $stockStatusId = 0;
    private int $languageId = 0;
    private string $name = '';

    #[ManyToOne(targetEntity: Language::class, foreignKey: 'languageId')]
    private ?Language $language = null;

    public function getStockStatusId(): int
    {
        return $this->stockStatusId;
    }

    public function setStockStatusId(int $stockStatusId): self
    {
        $this->stockStatusId = $stockStatusId;
        return $this;
    }

    public function getLanguageId(): int
    {
        return $this->languageId;
    }

    public function setLanguageId(int $id): self
    {
        $this->languageId = $id;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

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
