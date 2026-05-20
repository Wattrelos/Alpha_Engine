<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;

/**
 * Entidade Event - Gerencia os gatilhos e ações (hooks) do sistema.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Arquitetura de Eventos: Permite injeção de lógica (triggers) sem alterar o core.
 * - Performance: Ordenação numérica (sortOrder) para prioridade de execução.
 * - Tipagem Estrita: Booleano real para controle de ativação.
 */
class Event extends BaseEntity
{
    private string $code = '';
    private string $trigger = '';
    private string $action = '';
    private bool $status = false;
    private int $sortOrder = 0;

    public function getCode(): string { return $this->code; }
    public function setCode(string $code): self { $this->code = $code; return $this; }

    public function getTrigger(): string { return $this->trigger; }
    public function setTrigger(string $trigger): self { $this->trigger = $trigger; return $this; }

    public function getAction(): string { return $this->action; }
    public function setAction(string $action): self { $this->action = $action; return $this; }

    public function isStatus(): bool { return $this->status; }
    public function setStatus(bool $status): self { $this->status = $status; return $this; }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $order): self { $this->sortOrder = $order; return $this; }
}