<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;

/**
 * Entidade WeightClassDescription
 * Armazena as traduções e símbolos das classes de peso.
 * 
 * @Table(name="weight_class_description")
 */
class WeightClassDescription extends BaseEntity
{
    private int $weightClassId;
    private int $languageId;
    private string $title = '';
    private string $unit = '';

    #[ManyToOne(targetEntity: Language::class, foreignKey: 'languageId')]
    private ?Language $language = null;

    /**
     * Apontamentos Técnicos:
     * 1. Unidade (Unit): Este campo armazena o símbolo (ex: 'kg', 'lb'). É o que o cliente vê no carrinho.
     * 2. Vínculo Idiomático: O ManyToOne com Language garante que o sistema saiba qual tradução exibir 
     *    com base na sessão do usuário.
     */

    public function getWeightClassId(): int
    {
        return $this->weightClassId;
    }

    public function setWeightClassId(int $weightClassId): self
    {
        $this->weightClassId = $weightClassId;
        return $this;
    }

    public function getLanguageId(): int
    {
        return $this->languageId;
    }

    public function setLanguageId(int $languageId): self
    {
        $this->languageId = $languageId;
        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getUnit(): string
    {
        return $this->unit;
    }

    public function setUnit(string $unit): self
    {
        $this->unit = $unit;
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