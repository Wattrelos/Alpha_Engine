<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade MarketingReport - Logs de acesso de campanhas de marketing.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Auditoria de Tráfego: Registro de IP e localização geográfica (ISO 2) do acesso.
 * - Vínculos Relacionais: ManyToOne para Marketing e Store, identificando origem e destino do lead.
 * - Tipagem PHP 8.4: Propriedades rigorosamente tipadas para evitar falhas em joins do DAO.
 * - Interface Fluida: Setters preparados para encadeamento.
 */
class MarketingReport extends BaseEntity
{
    private int $marketingId = 0;
    private int $storeId = 0;
    private string $ip = '';
    private string $country = '';
    private string $dateAdded = '';

    #[ManyToOne(targetEntity: Marketing::class, foreignKey: 'marketingId')]
    private ?Marketing $marketing = null;

    #[ManyToOne(targetEntity: Store::class, foreignKey: 'storeId')]
    private ?Store $store = null;

    public function getMarketingId(): int { return $this->marketingId; }
    public function setMarketingId(int $value): self { $this->marketingId = $value; return $this; }

    public function getStoreId(): int { return $this->storeId; }
    public function setStoreId(int $value): self { $this->storeId = $value; return $this; }

    public function getIp(): string { return $this->ip; }
    public function setIp(string $value): self { $this->ip = $value; return $this; }

    public function getCountry(): string { return $this->country; }
    public function setCountry(string $value): self { $this->country = $value; return $this; }

    public function getDateAdded(): string { return $this->dateAdded; }
    public function setDateAdded(string $value): self { $this->dateAdded = $value; return $this; }

    public function getMarketing(): ?Marketing { return $this->marketing; }
    public function setMarketing(?Marketing $marketing): self { $this->marketing = $marketing; return $this; }

    public function getStore(): ?Store { return $this->store; }
    public function setStore(?Store $store): self { $this->store = $store; return $this; }
}
