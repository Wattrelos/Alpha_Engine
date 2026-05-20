<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade BannerImage - Detalhamento individual de cada slide ou banner.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Localização Nativa: Vinculação direta com Language para permitir banners específicos por idioma.
 * - Integridade Logística: Link e imagem tratados como strings inicializadas para evitar quebras em templates.
 * - Ordenação: sortOrder tipado como int para garantir a sequência correta definida no admin.
 * - Injeção Relacional: Atributos #[ManyToOne] para que o DAO resolva o objeto Banner pai e o Language automaticamente.
 */
class BannerImage extends BaseEntity
{
    private string $title = '';
    private string $link = '';
    private string $image = '';
    private int $sortOrder = 0;

    #[ManyToOne(targetEntity: Banner::class, foreignKey: 'bannerId')]
    private ?Banner $banner = null;

    #[ManyToOne(targetEntity: Language::class, foreignKey: 'languageId')]
    private ?Language $language = null;

    public function getBannerId(): int
    {
        return $this->banner ? (int)$this->banner->getId() : 0;
    }

    public function setBannerId(int $bannerId): self
    {
        if (!$this->banner) {
            $this->banner = new Banner();
        }
        $this->banner->setId($bannerId);
        return $this;
    }

    public function getLanguageId(): int
    {
        return $this->language ? (int)$this->language->getId() : 0;
    }

    public function setLanguageId(int $languageId): self
    {
        if (!$this->language) {
            $this->language = new Language();
        }
        $this->language->setId($languageId);
        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getLink(): string
    {
        return $this->link;
    }

    public function setLink(string $link): self
    {
        $this->link = $link;
        return $this;
    }

    public function getImage(): string
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;
        return $this;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): self
    {
        $this->sortOrder = $sortOrder;
        return $this;
    }

    public function getBanner(): ?Banner { return $this->banner; }
    public function setBanner(?Banner $banner): self { $this->banner = $banner; return $this; }

    public function getLanguage(): ?Language { return $this->language; }
    public function setLanguage(?Language $language): self { $this->language = $language; return $this; }
}