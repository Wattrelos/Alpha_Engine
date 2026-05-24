<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;

/**
 * Entidade Module
 * Armazena instâncias de configurações isoladas para as Extensões/Módulos (ex: "Banner Home").
 * 
 * @Table(name="module")
 */
class Module extends BaseEntity
{
    private string $name = '';
    private string $code = '';
    private string $setting = ''; // JSON codificado

    public function getName(): string { return $this->name; }
    public function setName(string $val): self { $this->name = $val; return $this; }

    public function getCode(): string { return $this->code; }
    public function setCode(string $val): self { $this->code = $val; return $this; }

    public function getSetting(): string { return $this->setting; }
    public function setSetting(string $val): self { $this->setting = $val; return $this; }
    
    public function getSettingArray(): array { return json_decode($this->setting, true) ?: []; }
}