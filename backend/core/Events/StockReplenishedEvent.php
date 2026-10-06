<?php

namespace Alpha\Events;

/**
 * StockReplenishedEvent - Disparado quando um produto ou variação tem o saldo de estoque reposto (quantity > 0).
 * 
 * Conforme ADR 0008.
 */
class StockReplenishedEvent implements EventInterface
{
    private int $productId;
    private ?int $variantId;
    private int $newQuantity;
    private int $storeId;

    public function __construct(int $productId, ?int $variantId = null, int $newQuantity = 1, int $storeId = 1)
    {
        $this->productId = $productId;
        $this->variantId = $variantId;
        $this->newQuantity = $newQuantity;
        $this->storeId = $storeId;
    }

    public function getName(): string
    {
        return 'stock.replenished';
    }

    public function getProductId(): int
    {
        return $this->productId;
    }

    public function getVariantId(): ?int
    {
        return $this->variantId;
    }

    public function getNewQuantity(): int
    {
        return $this->newQuantity;
    }

    public function getStoreId(): int
    {
        return $this->storeId;
    }
}
