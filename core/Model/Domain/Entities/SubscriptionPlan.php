<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\OneToMany;

/**
 * Entidade SubscriptionPlan - Define as regras de faturamento recorrente.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Gestão de Ciclos: Propriedades de frequência e duração tipadas como int para lógica de cron.
 * - Suporte a Trial: Campos específicos para períodos de teste integrados à lógica principal.
 * - Multi-idioma: Relacionamento OneToMany configurado para carregar nomes e descrições do plano.
 */
class SubscriptionPlan extends BaseEntity
{
    private string $trialFrequency = '';
    private int $trialCycle = 0;
    private int $trialDuration = 0;
    private bool $trialStatus = false;
    private string $frequency = '';
    private int $cycle = 0;
    private int $duration = 0;
    private int $sortOrder = 0;
    private bool $status = false;

    #[OneToMany(targetEntity: SubscriptionPlanDescription::class, foreignKey: 'subscriptionPlanId')]
    private array $descriptions = [];

    public function getTrialFrequency(): string { return $this->trialFrequency; }
    public function setTrialFrequency(string $value): self { $this->trialFrequency = $value; return $this; }

    public function getTrialCycle(): int { return $this->trialCycle; }
    public function setTrialCycle(int $value): self { $this->trialCycle = $value; return $this; }

    public function getTrialDuration(): int { return $this->trialDuration; }
    public function setTrialDuration(int $value): self { $this->trialDuration = $value; return $this; }

    public function isTrialStatus(): bool { return $this->trialStatus; }
    public function setTrialStatus(bool $value): self { $this->trialStatus = $value; return $this; }

    public function getFrequency(): string { return $this->frequency; }
    public function setFrequency(string $value): self { $this->frequency = $value; return $this; }

    public function getCycle(): int { return $this->cycle; }
    public function setCycle(int $value): self { $this->cycle = $value; return $this; }

    public function getDuration(): int { return $this->duration; }
    public function setDuration(int $value): self { $this->duration = $value; return $this; }

    public function getSortOrder(): int { return $this->sortOrder; }
    public function setSortOrder(int $value): self { $this->sortOrder = $value; return $this; }

    public function isStatus(): bool { return $this->status; }
    public function setStatus(bool $value): self { $this->status = $value; return $this; }

    /** @return SubscriptionPlanDescription[] */
    public function getDescriptions(): array { return $this->descriptions; }
    public function setDescriptions(array $descriptions): self { $this->descriptions = $descriptions; return $this; }
}