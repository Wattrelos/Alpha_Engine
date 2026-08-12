<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\OneToMany;

/**
 * Entidade Information - Representa as páginas institucionais e conteúdos do CMS.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Multi-Loja e Design: Ativação dos mapeamentos para Lojas e Layouts, garantindo flexibilidade na exibição de conteúdo.
 * - Interface Fluida: Implementação de setters que retornam 'self' para facilitar a construção de objetos em cascata.
 * - Normalização de PK: Utiliza o padrão 'id' herdado de BaseEntity, facilitando a automação no DataAccessObject.
 * - Tipagem PHP 8.4: Propriedades booleanas tratadas explicitamente para consistência com o motor PDO.
 */
class Information extends BaseEntity
{
    private int $sortOrder = 0;
    private bool $status = true;

    /**
     * @var InformationDescription[]
     */
    #[OneToMany(targetEntity: InformationDescription::class, foreignKey: "informationId")]
    private array $descriptions = [];

    /**
     * @var InformationToLayout[]
     */
    #[OneToMany(targetEntity: InformationToLayout::class, mappedBy: "information", foreignKey: "informationId")]
    private array $informationToLayouts = [];

    /**
     * @var InformationToStore[]
     */
    #[OneToMany(targetEntity: InformationToStore::class, mappedBy: "information", foreignKey: "informationId")]
    private array $informationToStores = [];

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $value): self
    {
        $this->sortOrder = $value;
        return $this;
    }

    public function getStatus(): bool
    {
        return $this->status;
    }

    public function setStatus(bool|int $value): self
    {
        $this->status = (bool)$value;
        return $this;
    }

    public function getDescriptions(): array
    {
        return $this->descriptions;
    }

    public function setDescriptions(array $descriptions): self
    {
        $this->descriptions = $descriptions;
        return $this;
    }

    public function getInformationToLayouts(): array
    {
        return $this->informationToLayouts;
    }

    public function setInformationToLayouts(array $value): self
    {
        $this->informationToLayouts = $value;
        return $this;
    }

    public function getInformationToStores(): array
    {
        return $this->informationToStores;
    }

    public function setInformationToStores(array $value): self
    {
        $this->informationToStores = $value;
        return $this;
    }
}
