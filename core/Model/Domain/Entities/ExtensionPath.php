<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade ExtensionPath - Mapeia os caminhos físicos de arquivos de extensões.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Roteamento de Extensões: Facilita o autoloading e a localização de controllers/views de terceiros.
 * - Tipagem PHP 8.4: IDs e caminhos tipados.
 * - Relacionamentos: #[ManyToOne] para vincular à entidade Extension pai.
 */
class ExtensionPath extends BaseEntity
{
    private int $extensionId = 0;
    private string $path = '';

    #[ManyToOne(targetEntity: Extension::class, foreignKey: 'extensionId')]
    private ?Extension $extension = null;

    public function getExtensionId(): int { return $this->extensionId; }
    public function setExtensionId(int $id): self { $this->extensionId = $id; return $this; }

    public function getPath(): string { return $this->path; }
    public function setPath(string $path): self { $this->path = $path; return $this; }

    public function getExtension(): ?Extension
    {
        return $this->extension;
    }

    public function setExtension(?Extension $extension): self
    {
        $this->extension = $extension;
        return $this;
    }
}