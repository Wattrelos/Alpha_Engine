<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\OneToMany;

/**
 * Entidade Country - Representa as nações para fins de logística e impostos.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Integridade ISO: Campos isoCode2 e isoCode3 tipados para garantir validações precisas em gateways de pagamento.
 * - Flexibilidade Logística: Campo addressFormat tipado como string para suportar templates de impressão de etiquetas.
 * - Cascata Relacional: Atributo #[OneToMany] configurado para que o DAO carregue todas as Zonas (Estados) vinculadas.
 * - Tipagem Estrita: Status e postcodeRequired tratados como booleanos reais.
 */
class Country extends BaseEntity
{
    private string $isoCode2 = '';
    private string $isoCode3 = '';
    private int $addressFormatId = 0;
    private bool $postcodeRequired = false;
    private bool $status = true;

    /**
     * @var Zone[]
     */
    #[OneToMany(targetEntity: Zone::class, mappedBy: "country", foreignKey: "countryId")]
    private array $zones = [];

    /**
     * @var CountryDescription[]
     */
    #[OneToMany(targetEntity: CountryDescription::class, mappedBy: "country", foreignKey: "countryId")]
    private array $descriptions = [];

    public function getIsoCode2(): string
    {
        return $this->isoCode2;
    }

    public function setIsoCode2(string $isoCode2): self
    {
        $this->isoCode2 = $isoCode2;
        return $this;
    }

    public function getIsoCode3(): string
    {
        return $this->isoCode3;
    }

    public function setIsoCode3(string $isoCode3): self
    {
        $this->isoCode3 = $isoCode3;
        return $this;
    }

    public function getAddressFormatId(): int
    {
        return $this->addressFormatId;
    }

    public function setAddressFormatId(int $addressFormatId): self
    {
        $this->addressFormatId = $addressFormatId;
        return $this;
    }

    public function getPostcodeRequired(): bool
    {
        return $this->postcodeRequired;
    }

    public function setPostcodeRequired(bool|int $postcodeRequired): self
    {
        $this->postcodeRequired = (bool)$postcodeRequired;
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
     * Retorna a coleção de estados/províncias.
     * @return Zone[]
     */
    public function getZones(): array
    {
        return $this->zones;
    }

    /**
     * Define a coleção de estados.
     * @param Zone[] $zones
     * @return self
     */
    public function setZones(array $zones): self
    {
        $this->zones = $zones;
        return $this;
    }

    /**
     * Retorna as traduções do país.
     * @return CountryDescription[]
     */
    public function getDescriptions(): array
    {
        return $this->descriptions;
    }

    /**
     * Define a coleção de traduções.
     * @param CountryDescription[] $descriptions
     * @return self
     */
    public function setDescriptions(array $descriptions): self
    {
        $this->descriptions = $descriptions;
        return $this;
    }
}