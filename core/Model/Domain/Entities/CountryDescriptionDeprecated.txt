<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;

/**
 * DEPRECATED.Entidade CountryDescription - Traduções e descrições dos Países.
 * 
 * Mapeada para suprir o OneToMany do Country, garantindo
 * o suporte multidioma para nomes de países na Alpha Engine.
 */
class CountryDescription extends BaseEntity
{
    private int $countryId = 0;
    private int $languageId = 0;
    private string $name = '';

    public function getCountryId(): int
    {
        return $this->countryId;
    }

    public function setCountryId(int $countryId): self
    {
        $this->countryId = $countryId;
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

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }
}
