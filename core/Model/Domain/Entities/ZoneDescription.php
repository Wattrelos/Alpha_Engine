<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade ZoneDescription - Traduções e nomes regionalizados de estados/províncias.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Suporte a Multi-idiomas: Estruturação para nomes traduzidos de Zonas (Estados/Departamentos).
 * - Tipagem Estrita PHP 8.4: Propriedades tipadas e inicializadas para prevenir NullPointer.
 * - Injeção Relacional: Atributos #[ManyToOne] para resolução automática via DAO.
 */
class ZoneDescription extends BaseEntity
{
    private int $zoneId = 0;
    private int $languageId = 0;
    private string $name = '';

    #[ManyToOne(targetEntity: Zone::class, foreignKey: 'zoneId')]
    private ?Zone $zone = null;

    #[ManyToOne(targetEntity: Language::class, foreignKey: 'languageId')]
    private ?Language $language = null;

    public function getZoneId(): int
    {
        return $this->zone ? (int)$this->zone->getId() : $this->zoneId;
    }

    public function setZoneId(int $zoneId): self
    {
        $this->zoneId = $zoneId;
        if ($this->zone) {
            $this->zone->setId($zoneId);
        }
        return $this;
    }

    public function getLanguageId(): int
    {
        return $this->language ? (int)$this->language->getId() : $this->languageId;
    }

    public function setLanguageId(int $languageId): self
    {
        $this->languageId = $languageId;
        if ($this->language) {
            $this->language->setId($languageId);
        }
        return $this;
    }

    public function getName(): string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }

    public function getZone(): ?Zone { return $this->zone; }
    public function setZone(?Zone $zone): self
    {
        $this->zone = $zone;
        if ($zone) {
            $this->zoneId = $zone->getId();
        }
        return $this;
    }

    public function getLanguage(): ?Language { return $this->language; }
    public function setLanguage(?Language $language): self
    {
        $this->language = $language;
        if ($language) {
            $this->languageId = $language->getId();
        }
        return $this;
    }
}