<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade Zone - Representa estados, províncias ou regiões.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Injeção Relacional: Atributo #[ManyToOne] para que o DAO resolva o objeto Country automaticamente.
 * - Tipagem Estrita: countryId tipado como int para integridade referencial com chaves estrangeiras.
 * - Interface Fluida: Setters retornando self para construção ágil de objetos geográficos.
 */
class Zone extends BaseEntity
{
    private string $name = '';
    private string $code = '';
    private bool $status = true;

    #[ManyToOne(targetEntity: Country::class, foreignKey: 'countryId')]
    private ?Country $country = null;

    public function getCountryId(): int
    {
        return $this->country ? (int)$this->country->getId() : 0;
    }

    public function setCountryId(int $id): self
    {
        if (!$this->country) {
            $this->country = new Country();
        }
        $this->country->setId($id);
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

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;
        return $this;
    }

    public function getStatus(): bool
    {
        return $this->status;
    }

    public function setStatus(bool|int $status): self
    {
        $this->status = (bool)$status;
        return $this;
    }

    /**
     * Retorna o objeto País pai.
     */
    public function getCountry(): ?Country
    {
        return $this->country;
    }

    /**
     * Injeta o objeto País pai.
     */
    public function setCountry(?Country $country): self
    {
        $this->country = $country;
        return $this;
    }
}