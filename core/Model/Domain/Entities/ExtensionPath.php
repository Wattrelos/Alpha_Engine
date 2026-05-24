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
 * - Relacionamentos: #[ManyToOne] para vincular à entidade ExtensionInstall pai.
 */
class ExtensionPath extends BaseEntity
{
    private int $extensionInstallId = 0;
    private string $path = '';

    #[ManyToOne(targetEntity: ExtensionInstall::class, foreignKey: 'extensionInstallId')]
    private ?ExtensionInstall $extensionInstall = null;

    public function getExtensionInstallId(): int { return $this->extensionInstallId; }
    public function setExtensionInstallId(int $id): self { $this->extensionInstallId = $id; return $this; }

    public function getPath(): string { return $this->path; }
    public function setPath(string $path): self { $this->path = $path; return $this; }

    public function getExtensionInstall(): ?ExtensionInstall
    {
        return $this->extensionInstall;
    }

    public function setExtensionInstall(?ExtensionInstall $extensionInstall): self
    {
        $this->extensionInstall = $extensionInstall;
        return $this;
    }
}