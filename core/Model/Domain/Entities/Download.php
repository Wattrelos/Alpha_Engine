<?php
namespace Alpha\Model\Domain\Entities;

 use Alpha\Model\Domain\BaseEntity;
 use Alpha\Model\Domain\Attributes\OneToMany;

/**
 * Entidade Download - Representa os arquivos disponíveis para download (produtos digitais).
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Gestão de Arquivos: Centraliza o caminho real (filename) e o nome exibido (mask) para segurança no download.
 * - Tipagem PHP 8.4: Propriedades rigorosamente tipadas e inicializadas para evitar erros de runtime.
 * - Relacionamentos Ativos: #[OneToMany] configurado para hidratação automática das descrições multi-idioma.
 * - Rastreabilidade: dateAdded tipado para auditoria de criação de conteúdo digital.
 */
class Download extends BaseEntity
{
    private string $filename = '';
    private string $mask = '';
    private string $dateAdded = '';

    /**
     * @var DownloadDescription[]
     */
    #[OneToMany(targetEntity: DownloadDescription::class, mappedBy: "download", foreignKey: "downloadId")]
    private array $descriptions = [];

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function setFilename(string $value): self
    {
        $this->filename = $value;
        return $this;
    }

    public function getMask(): string
    {
        return $this->mask;
    }

    public function setMask(string $value): self
    {
        $this->mask = $value;
        return $this;
    }

    public function getDateAdded(): string
    {
        return $this->dateAdded;
    }

    public function setDateAdded(string $value): self
    {
        $this->dateAdded = $value;
        return $this;
    }

    public function getDescriptions(): array
    {
        return $this->descriptions;
    }

    public function setDescriptions(array $value): self
    {
        $this->descriptions = $value;
        return $this;
    }
}
