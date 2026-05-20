<?php
namespace Alpha\Model\Domain\Entities;

 use Alpha\Model\Domain\BaseEntity;
 use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade DownloadDescription - Traduções para os nomes dos arquivos de download.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Localização: Suporte a nomes de arquivos amigáveis traduzidos para cada idioma, melhorando a UX.
 * - Injeção de Contexto: Propriedade downloadId mapeada para permitir que o DAO vincule automaticamente a tradução ao seu pai.
 * - Relacionamentos Ativos: #[ManyToOne] para Language e para o Download pai, permitindo navegação bidirecional.
 * - Limpeza: Remoção de PK manual redundante, herdando id de BaseEntity conforme as regras de negócio.
 */
class DownloadDescription extends BaseEntity
{
    private int $downloadId = 0;
    private int $languageId = 0;
    private string $name = '';

    #[ManyToOne(targetEntity: Download::class, foreignKey: 'downloadId')]
    private ?Download $download = null;

    #[ManyToOne(targetEntity: Language::class, foreignKey: 'languageId')]
    private ?Language $language = null;

    public function getDownloadId(): int { return $this->downloadId; }
    public function setDownloadId(int $value): self { $this->downloadId = $value; return $this; }

    public function getLanguageId(): int { return $this->languageId; }
    public function setLanguageId(int $value): self { $this->languageId = $value; return $this; }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $value): self
    {
        $this->name = $value;
        return $this;
    }

    public function getDownload(): ?Download
    {
        return $this->download;
    }

    public function setDownload(?Download $value): self
    {
        $this->download = $value;
        return $this;
    }

    public function getLanguage(): ?Language
    {
        return $this->language;
    }

    public function setLanguage(?Language $value): self
    {
        $this->language = $value;
        return $this;
    }
}
