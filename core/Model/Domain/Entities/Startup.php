<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;

/**
 * Entidade Startup
 * Controla injeções de middlewares e classes autoloader essenciais na inicialização.
 * 
 * @Table(name="startup")
 */
class Startup extends BaseEntity
{
    private string $description = '';
    private string $code = '';
    private string $action = '';
    private bool $status = false;
    private int $sortOrder = 0;

    public function getDescription(): string { return $this->description; }
    public function setDescription(string $val): self { $this->description = $val; return $this; }

    public function getCode(): string { return $this->code; }
    public function setCode(string $val): self { $this->code = $val; return $this; }

    public function getAction(): string { return $this->action; }
    public function setAction(string $val): self { $this->action = $val; return $this; }

    public function isStatus(): bool { return $this->status; }
    public function setStatus(bool $val): self { $this->status = $val; return $this; }

    public function getSortOrder(): int { return $this->sortOrder; }
    public function setSortOrder(int $val): self { $this->sortOrder = $val; return $this; }
}