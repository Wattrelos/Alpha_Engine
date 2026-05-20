<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\OneToMany;

/**
 * Entidade Topic - Agrupador de artigos de blog ou CMS.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Organização de Conteúdo: Permite categorizar artigos para facilitar a navegação e o SEO.
 * - Tipagem PHP 8.4: Propriedades como sortOrder (int) e status (bool) garantem a ordenação e visibilidade.
 * - Relacionamentos Ativos: #[OneToMany] configurado para hidratação automática das descrições do tópico.
 * - Interface Fluida: Setters retornando 'self' para encadeamento de métodos.
 */
class Topic extends BaseEntity
{
    private int $sortOrder = 0;
    private bool $status = true;

    /**
     * @var TopicDescription[]
     */
    #[OneToMany(targetEntity: TopicDescription::class, mappedBy: "topic", foreignKey: "topicId")]
    private array $descriptions = [];

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): self
    {
        $this->sortOrder = $sortOrder;
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

    public function getDescriptions(): array
    {
        return $this->descriptions;
    }

    public function setDescriptions(array $descriptions): self
    {
        $this->descriptions = $descriptions;
        return $this;
    }
}