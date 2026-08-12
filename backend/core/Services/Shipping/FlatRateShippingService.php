<?php

namespace Alpha\Services\Shipping;

use Alpha\Model\Domain\Repositories\WeightClassRepository;
use Alpha\Model\Domain\Repositories\LengthClassRepository;
use Alpha\Model\Domain\Repositories\GeoZoneRepository;

/**
 * FlatRateShippingService - Lógica de cálculo para o método de frete fixo.
 *
 * Valida zona geográfica e limite de peso usando os repositórios Alpha,
 * retornando dados brutos para que a camada de apresentação (Action/Twig)
 * aplique a formatação de moeda e impostos.
 */
class FlatRateShippingService
{
    private WeightClassRepository $weightClassRepository;
    private LengthClassRepository $lengthClassRepository;
    private GeoZoneRepository $geoZoneRepository;

    public function __construct()
    {
        $repositoryFactory = \Alpha\Model\Domain\Repositories\RepositoryFactory::getInstance();
        $this->weightClassRepository = $repositoryFactory->get(WeightClassRepository::class);
        $this->lengthClassRepository = $repositoryFactory->get(LengthClassRepository::class);
        $this->geoZoneRepository     = $repositoryFactory->get(GeoZoneRepository::class);
    }

    /**
     * Verifica se o frete fixo é aplicável e retorna os dados brutos da cotação.
     *
     * @param array $address          Endereço de entrega.
     * @param float $totalWeight      Peso total do carrinho.
     * @param int   $weightClassId    ID da classe de peso do carrinho.
     * @param int   $geoZoneId        ID da zona geográfica configurada (0 = sem restrição).
     * @param int   $storeWeightClassId ID da classe de peso padrão da loja.
     * @param float $cost             Custo fixo do frete.
     * @param float $maxWeight        Peso máximo aceito (0 = sem limite).
     * @param int   $taxClassId       ID da classe de imposto do frete.
     * @param int   $sortOrder        Ordem de exibição.
     * @return array|null Dados brutos da cotação ou null se não aplicável.
     */
    public function getQuote(
        array $address,
        float $totalWeight,
        int   $weightClassId,
        int   $geoZoneId,
        int   $storeWeightClassId,
        float $cost,
        float $maxWeight = 0,
        int   $taxClassId = 0,
        int   $sortOrder = 0
    ): ?array {
        // Validação de Geo Zone via Alpha Engine
        if ($geoZoneId > 0 && !$this->geoZoneRepository->isAddressInGeoZone($geoZoneId, $address)) {
            return null;
        }

        // Normalização do peso para a unidade padrão da loja
        $normalizedWeight = $this->weightClassRepository->convert($totalWeight, $weightClassId, $storeWeightClassId);

        // Regra Alpha: Limite máximo de peso para aceitar frete fixo
        if ($maxWeight > 0 && $normalizedWeight > $maxWeight) {
            return null;
        }

        return [
            'code'         => 'flat.flat',
            'cost'         => $cost,
            'tax_class_id' => $taxClassId,
            'sort_order'   => $sortOrder,
        ];
    }
}