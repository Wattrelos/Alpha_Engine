<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\OneToMany;

/**
 * Entidade Marketing - Rastreamento de campanhas publicitárias.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Rastreamento de Performance: Propriedade clicks tipada como int para análise de conversão.
 * - Segurança de Campanha: Código de rastreio único tratado como string estrita.
 * - Auditoria: Snapshot de data de criação para controle de ciclo de vida de campanhas.
 * - Relacionamentos: OneToMany configurado para carregar o histórico de acessos (MarketingReport).
 */
class Marketing extends BaseEntity
{
    private string $name = '';
    private string $description = '';
    private string $code = '';
    private int $clicks = 0;
    private string $dateAdded = '';

    /** @var MarketingReport[] */
    #[OneToMany(targetEntity: MarketingReport::class, foreignKey: 'marketingId')]
    private array $reports = [];

    public function getName(): string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }

    public function getDescription(): string { return $this->description; }
    public function setDescription(string $description): self { $this->description = $description; return $this; }

    public function getCode(): string { return $this->code; }
    public function setCode(string $code): self { $this->code = $code; return $this; }

    public function getClicks(): int { return $this->clicks; }
    public function setClicks(int $clicks): self { $this->clicks = $clicks; return $this; }

    public function getDateAdded(): string { return $this->dateAdded; }
    public function setDateAdded(string $dateAdded): self { $this->dateAdded = $dateAdded; return $this; }

    /** @return MarketingReport[] */
    public function getReports(): array { return $this->reports; }
    public function setReports(array $reports): self { $this->reports = $reports; return $this; }
}