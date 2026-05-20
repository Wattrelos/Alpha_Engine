<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;

/**
 * Entidade ExtensionInstall - Registro de metadados de pacotes de extensões instalados.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Rastreabilidade: Gerencia o vínculo com downloads oficiais e armazena o nome de arquivo original para fins de suporte.
 * - Integridade de Código: Propriedade 'code' atua como identificador único para disparar scripts de instalação/desinstalação.
 * - Auditoria: 'dateAdded' tipado como string para garantir a persistência correta do timestamp de modificação no sistema.
 * - PHP 8.4 Readiness: Tipos primitivos rigorosos (int, string) para evitar inconsistências no motor de banco de dados.
 */
class ExtensionInstall extends BaseEntity
{
    private int $extensionDownloadId = 0;
    private string $filename = '';
    private string $code = '';
    private string $dateAdded = '';

    public function getExtensionDownloadId(): int
    {
        return $this->extensionDownloadId;
    }

    public function setExtensionDownloadId(int $extensionDownloadId): self
    {
        $this->extensionDownloadId = $extensionDownloadId;
        return $this;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): self
    {
        $this->filename = $filename;
        return $this;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;
        return $this;
    }

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