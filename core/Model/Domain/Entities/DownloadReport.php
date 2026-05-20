<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade DownloadReport - Rastreia o histórico de downloads de arquivos digitais.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Auditoria de Ativos: Registro de IPs para controle de abusos em produtos digitais.
 * - Relacionamentos: #[ManyToOne] para vincular o relatório ao objeto Download.
 * - Tipagem PHP 8.4: Uso de int para IDs e strings para metadados de rede.
 */
class DownloadReport extends BaseEntity
{
    private int $downloadId = 0;
    private string $ip = '';
    private string $dateAdded = '';

    #[ManyToOne(targetEntity: Download::class, foreignKey: 'downloadId')]
    private ?Download $download = null;

    public function getDownloadId(): int { return $this->downloadId; }
    public function setDownloadId(int $id): self { $this->downloadId = $id; return $this; }

    public function getIp(): string { return $this->ip; }
    public function setIp(string $ip): self { $this->ip = $ip; return $this; }

    public function getDateAdded(): string
    {
        return $this->dateAdded;
    }

    public function setDateAdded(string $date): self { $this->dateAdded = $date; return $this; }

    public function getDownload(): ?Download
    {
        return $this->download;
    }

    public function setDownload(?Download $download): self { $this->download = $download; return $this; }
}