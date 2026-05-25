<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;

class Extension extends BaseEntity
{
    private string $extension = '';
    private string $type = '';
    private string $code = '';

    public function getExtension(): string { return $this->extension; }
    public function setExtension(string $extension): self { $this->extension = $extension; return $this; }

    public function getType(): string { return $this->type; }
    public function setType(string $type): self { $this->type = $type; return $this; }

    public function getCode(): string { return $this->code; }
    public function setCode(string $code): self { $this->code = $code; return $this; }
}