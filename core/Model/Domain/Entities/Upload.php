<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;

/**
 * Entidade Upload - Rastreia arquivos enviados por clientes (ex: personalização de produtos).
 */
class Upload extends BaseEntity
{
    private string $name = '';
    private string $filename = '';
    private string $code = '';
    private string $dateAdded = '';

    public function getName(): string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }

    public function getFilename(): string { return $this->filename; }
    public function setFilename(string $filename): self { $this->filename = $filename; return $this; }

    public function getCode(): string { return $this->code; }
    public function setCode(string $code): self { $this->code = $code; return $this; }

    public function getDateAdded(): string
    {
        return $this->dateAdded;
    }

    public function setDateAdded(string $date): self { $this->dateAdded = $date; return $this; }
}