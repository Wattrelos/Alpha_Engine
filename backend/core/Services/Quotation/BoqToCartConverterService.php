<?php

declare(strict_types=1);

namespace Alpha\Services\Quotation;

use Alpha\Model\Domain\Entities\Quotation\ProjectBoq;
use Alpha\Model\Domain\Entities\Quotation\ProjectBoqItem;
use Alpha\Model\Domain\Repositories\CartRepository;
use Alpha\Model\Domain\Repositories\ProductRepository;
use Alpha\Model\Domain\Repositories\ProjectBoqRepository;
use Alpha\Model\Domain\Repositories\ProductDiscountRepository;

/**
 * BoqToCartConverterService - Converte itens de um Bill of Quantities (BoQ) em itens no carrinho de compras
 * aplicando regras de desconto progressivo por volume (RN015 e RN017).
 */
class BoqToCartConverterService
{
    public function __construct(
        private CartRepository $cartRepository,
        private ?ProductRepository $productRepository = null,
        private ?ProjectBoqRepository $boqRepository = null,
        private ?ProductDiscountRepository $discountRepository = null
    ) {}

    /**
     * Calcula os descontos progressivos por volume (RN015) aplicáveis aos itens do BoQ.
     *
     * @param ProjectBoq $boq
     * @param int $customerGroupId
     * @return array{
     *     original_subtotal: float,
     *     discounted_subtotal: float,
     *     total_savings: float,
     *     discount_percentage: float,
     *     items_summary: array
     * }
     */
    public function calculateVolumeDiscounts(ProjectBoq $boq, int $customerGroupId = 1): array
    {
        $items = $boq->getItems();
        if (empty($items) && $this->boqRepository) {
            $items = $this->boqRepository->findItemsByBoqId($boq->getId());
        }

        $originalSubtotal = 0.0;
        $discountedSubtotal = 0.0;
        $itemsSummary = [];

        foreach ($items as $item) {
            $qty = $item->getQuantity();
            $unitPrice = $item->getUnitPrice();
            $itemOriginalTotal = round($qty * $unitPrice, 2);
            $originalSubtotal += $itemOriginalTotal;

            $discountRate = 0.0;
            $appliedTier = 'standard';

            // 1. Verifica descontos específicos cadastrados no banco para o produto
            if ($item->getProductId() && $this->discountRepository) {
                $discounts = $this->discountRepository->getActiveDiscounts($item->getProductId(), $customerGroupId);
                foreach ($discounts as $d) {
                    if ($qty >= $d->getQuantity() && $d->getPrice() < $unitPrice && $d->getPrice() > 0) {
                        $unitPrice = $d->getPrice();
                        $appliedTier = 'custom_product_discount';
                    }
                }
            }

            // 2. Se não houver desconto específico por produto, aplica tabela progressiva de volume (RN015)
            if ($appliedTier === 'standard') {
                if ($qty >= 250) {
                    $discountRate = 0.20; // 20% de desconto
                    $appliedTier = 'volume_tier_4 (250+)';
                } elseif ($qty >= 100) {
                    $discountRate = 0.15; // 15% de desconto
                    $appliedTier = 'volume_tier_3 (100+)';
                } elseif ($qty >= 50) {
                    $discountRate = 0.10; // 10% de desconto
                    $appliedTier = 'volume_tier_2 (50+)';
                } elseif ($qty >= 10) {
                    $discountRate = 0.05; // 5% de desconto
                    $appliedTier = 'volume_tier_1 (10+)';
                }
                $unitPrice = round($unitPrice * (1.0 - $discountRate), 2);
            }

            $itemDiscountedTotal = round($qty * $unitPrice, 2);
            $discountedSubtotal += $itemDiscountedTotal;
            $itemSavings = round($itemOriginalTotal - $itemDiscountedTotal, 2);

            $itemsSummary[] = [
                'item_id' => $item->getId(),
                'name' => $item->getItemName(),
                'quantity' => $qty,
                'unit' => $item->getUnit(),
                'original_unit_price' => $item->getUnitPrice(),
                'effective_unit_price' => $unitPrice,
                'original_total' => $itemOriginalTotal,
                'discounted_total' => $itemDiscountedTotal,
                'savings' => $itemSavings,
                'applied_tier' => $appliedTier
            ];
        }

        $totalSavings = round($originalSubtotal - $discountedSubtotal, 2);
        $discountPercentage = $originalSubtotal > 0 ? round(($totalSavings / $originalSubtotal) * 100, 2) : 0.0;

        return [
            'original_subtotal' => $originalSubtotal,
            'discounted_subtotal' => $discountedSubtotal,
            'total_savings' => max(0.0, $totalSavings),
            'discount_percentage' => $discountPercentage,
            'items_summary' => $itemsSummary
        ];
    }

    /**
     * Converte o BoQ aprovado em itens no carrinho de compras do cliente aplicando descontos por volume.
     *
     * @param ProjectBoq $boq
     * @param int $customerGroupId
     * @return array{
     *     success: bool,
     *     items_added: int,
     *     unmapped_items: array,
     *     total_items: int,
     *     volume_discounts: array
     * }
     */
    public function convertBoqToCart(ProjectBoq $boq, int $customerGroupId = 1): array
    {
        $this->cartRepository->initializeContext();

        $items = $boq->getItems();
        if (empty($items) && $this->boqRepository) {
            $items = $this->boqRepository->findItemsByBoqId($boq->getId());
        }

        $volumeAnalysis = $this->calculateVolumeDiscounts($boq, $customerGroupId);

        $itemsAdded = 0;
        $unmappedItems = [];

        foreach ($items as $item) {
            $productId = $item->getProductId();
            $qty = (int)ceil($item->getQuantity());

            if ($productId && $productId > 0) {
                $this->cartRepository->add($productId, max(1, $qty), []);
                $itemsAdded++;
            } else {
                $unmappedItems[] = [
                    'name' => $item->getItemName(),
                    'unit' => $item->getUnit(),
                    'quantity' => $item->getQuantity(),
                    'unit_price' => $item->getUnitPrice()
                ];
            }
        }

        if ($this->boqRepository && $boq->getId() > 0) {
            $boq->setStatus('converted_to_cart');
            $this->boqRepository->save($boq);
        }

        return [
            'success' => $itemsAdded > 0 || !empty($unmappedItems),
            'items_added' => $itemsAdded,
            'unmapped_items' => $unmappedItems,
            'total_items' => count($items),
            'volume_discounts' => $volumeAnalysis
        ];
    }
}
