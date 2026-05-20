<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;

/**
 * Entidade Statistics - Armazena métricas e contadores globais do sistema.
 */
class Statistics extends BaseEntity
{
    private string $code = '';
    private float $value = 0.0;

    public function getCode(): string { return $this->code; }
    public function setCode(string $code): self { $this->code = $code; return $this; }

    public function getValue(): float
    {
        return $this->value;
    }

    public function setValue(float $value): self { $this->value = $value; return $this; }
}