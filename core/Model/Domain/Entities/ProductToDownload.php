<?php
namespace Alpha\Model\Domain\Entities;

 use Alpha\Model\Domain\BaseEntity;
 use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade ProductToDownload
 * Vincula arquivos de download a produtos digitais.
 * 
 * @Table(name="product_to_download")
 */
class ProductToDownload extends BaseEntity
{
    #[ManyToOne(targetEntity: Product::class, foreignKey: 'productId')]
    private ?Product $product = null;

    #[ManyToOne(targetEntity: Download::class, foreignKey: 'downloadId')]
    private ?Download $download = null;

    public function getProductId(): int
    {
        return $this->product ? (int)$this->product->getId() : 0;
    }

    public function setProductId(int $productId): self
    {
        if (!$this->product) $this->product = new Product();
        $this->product->setId($productId);
        return $this;
    }

    public function getDownloadId(): int
    {
        return $this->download ? (int)$this->download->getId() : 0;
    }

    public function setDownloadId(int $downloadId): self
    {
        if (!$this->download) $this->download = new Download();
        $this->download->setId($downloadId);
        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;
        return $this;
    }

    public function getDownload(): ?Download
    {
        return $this->download;
    }

    public function setDownload(?Download $download): self
    {
        $this->download = $download;
        return $this;
    }
}
