<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;

/**
 * Entidade Module - Define instâncias de módulos instalados com configurações específicas.
 */
class Module extends BaseEntity
{
    private string $name = '';
    private string $code = '';
    private string $setting = '';

    public function getName(): string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }

    public function getCode(): string { return $this->code; }
    public function setCode(string $code): self { $this->code = $code; return $this; }

    public function getSetting(): string
    {
        return $this->setting;
    }

    public function setSetting(string $setting): self
    {
        $this->setting = $setting;
        return $this;
    }
}