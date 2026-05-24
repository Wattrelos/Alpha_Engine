<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;

/**
 * Entidade ExtensionInstall - Registro de metadados de pacotes de extensões instalados.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Integridade de Código: Propriedade 'code' atua como identificador único para disparar scripts de instalação/desinstalação.
 * - Auditoria: 'dateAdded' tipado como string para garantir a persistência correta do timestamp de modificação no sistema.
 * - PHP 8.4 Readiness: Tipos primitivos rigorosos (int, string) para evitar inconsistências no motor de banco de dados.
 */
class ExtensionInstall extends BaseEntity
{
    private int $extensionId = 0;
    private int $extensionDownloadId = 0;
    private string $name = '';
    private string $description = '';
    private string $code = '';
    private string $version = '';
    private string $author = '';
    private string $link = '';
    private bool $status = false;
    private string $dateAdded = '';

    public function getExtensionId(): int { return $this->extensionId; }
    public function setExtensionId(int $extensionId): self { $this->extensionId = $extensionId; return $this; }

    public function getExtensionDownloadId(): int
    {
        return $this->extensionDownloadId;
    }

    public function setExtensionDownloadId(int $extensionDownloadId): self
    {
        $this->extensionDownloadId = $extensionDownloadId;
        return $this;
    }

    public function getName(): string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }

    public function getDescription(): string { return $this->description; }
    public function setDescription(string $description): self { $this->description = $description; return $this; }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;
        return $this;
    }

    public function getVersion(): string { return $this->version; }
    public function setVersion(string $version): self { $this->version = $version; return $this; }

    public function getAuthor(): string { return $this->author; }
    public function setAuthor(string $author): self { $this->author = $author; return $this; }

    public function getLink(): string { return $this->link; }
    public function setLink(string $link): self { $this->link = $link; return $this; }

    public function isStatus(): bool { return $this->status; }
    public function setStatus(bool|int $status): self { $this->status = (bool)$status; return $this; }

    public function getDateAdded(): string
    {
        return $this->dateAdded;
    }

    public function setDateAdded(string $dateAdded): self
    {
        $this->dateAdded = $dateAdded;
        return $this;
    }
}