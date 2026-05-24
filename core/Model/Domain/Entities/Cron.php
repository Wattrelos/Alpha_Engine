<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;

/**
 * Entidade Cron - Gerencia tarefas agendadas do sistema.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Automação Segura: Controle de execução e periodicidade (ciclos).
 * - Auditoria Temporal: Rastreio de criação e modificação para controle de concorrência.
 * - Tipagem Estrita: Booleano real para status e strings para códigos.
 */
class Cron extends BaseEntity
{
    private string $code = '';
    private string $description = '';
    private string $cycle = '';
    private string $action = '';
    private bool $status = false;
    private string $dateAdded = '';
    private string $dateModified = '';

    public function getCode(): string { return $this->code; }
    public function setCode(string $code): self { $this->code = $code; return $this; }

    public function getDescription(): string { return $this->description; }
    public function setDescription(string $description): self { $this->description = $description; return $this; }

    public function getCycle(): string { return $this->cycle; }
    public function setCycle(string $cycle): self { $this->cycle = $cycle; return $this; }

    public function getAction(): string { return $this->action; }
    public function setAction(string $action): self { $this->action = $action; return $this; }

    public function getStatus(): bool { return $this->status; }
    public function setStatus(bool|int $status): self { $this->status = (bool)$status; return $this; }

    public function getDateAdded(): string
    {
        return $this->dateAdded;
    }

    public function setDateAdded(string $date): self { $this->dateAdded = $date; return $this; }

    public function getDateModified(): string { return $this->dateModified; }
    public function setDateModified(string $date): self { $this->dateModified = $date; return $this; }
}