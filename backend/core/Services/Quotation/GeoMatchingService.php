<?php

namespace Alpha\Services\Quotation;

use Alpha\Model\Domain\Entities\Quotation\ProjectRfq;
use Alpha\Model\Domain\Entities\Quotation\ServiceProviderProfile;
use Alpha\Model\Domain\Repositories\ServiceProviderProfileRepository;

/**
 * GeoMatchingService - Serviço de geolocalização e matching por raio geográfico.
 * Atende ao requisito funcional RF034.
 */
class GeoMatchingService
{
    private const EARTH_RADIUS_KM = 6371.0;

    public function __construct(
        private ?ServiceProviderProfileRepository $providerRepo = null
    ) {}

    /**
     * Calcula a distância em quilômetros entre duas coordenadas (Fórmula de Haversine).
     */
    public function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo   = deg2rad($lat2);
        $lonTo   = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return round($angle * self::EARTH_RADIUS_KM, 2);
    }

    /**
     * Verifica se um prestador atende à localização de um projeto específico.
     */
    public function isProviderEligibleForProject(ServiceProviderProfile $provider, ProjectRfq $project): bool
    {
        if (!$provider->isStatus()) {
            return false;
        }

        // Se ambos tiverem coordenadas, calcula Haversine direto
        if ($provider->getLatitude() !== null && $provider->getLongitude() !== null &&
            $project->getLatitude() !== null && $project->getLongitude() !== null) {
            
            $distance = $this->calculateDistance(
                $provider->getLatitude(),
                $provider->getLongitude(),
                $project->getLatitude(),
                $project->getLongitude()
            );

            return $distance <= $provider->getServiceRadiusKm();
        }

        // Fallback: Se estiver na mesma cidade/estado ou mesma faixa de CEP
        if (!empty($provider->getAddressCity()) && !empty($project->getAddressCity())) {
            if (mb_strtolower(trim($provider->getAddressCity())) === mb_strtolower(trim($project->getAddressCity()))) {
                return true;
            }
        }

        // Fallback por prefixo do CEP (primeiros 5 dígitos)
        if (!empty($provider->getAddressCep()) && !empty($project->getAddressCep())) {
            $pCepPrefix = substr(preg_replace('/\D/', '', $provider->getAddressCep()), 0, 5);
            $rCepPrefix = substr(preg_replace('/\D/', '', $project->getAddressCep()), 0, 5);
            if ($pCepPrefix === $rCepPrefix) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retorna a lista de prestadores credenciados elegíveis para atender o projeto.
     * @return array<int, array{provider: ServiceProviderProfile, distance: ?float}>
     */
    public function findEligibleProviders(ProjectRfq $project): array
    {
        if (!$this->providerRepo) {
            return [];
        }

        $allProviders = $this->providerRepo->findActiveProviders();
        $eligible = [];

        foreach ($allProviders as $provider) {
            if ($this->isProviderEligibleForProject($provider, $project)) {
                $dist = null;
                if ($provider->getLatitude() !== null && $provider->getLongitude() !== null &&
                    $project->getLatitude() !== null && $project->getLongitude() !== null) {
                    $dist = $this->calculateDistance(
                        $provider->getLatitude(),
                        $provider->getLongitude(),
                        $project->getLatitude(),
                        $project->getLongitude()
                    );
                }

                $eligible[] = [
                    'provider' => $provider,
                    'distance' => $dist
                ];
            }
        }

        return $eligible;
    }

    /**
     * Tenta inferir coordenadas geográficas aproximadas a partir de um CEP ou Cidade.
     * @return array{lat: float, lng: float}|null
     */
    public function resolveApproximateCoordinates(string $cep, string $city = '', string $state = ''): ?array
    {
        $cleanCep = preg_replace('/\D/', '', $cep);
        if (empty($cleanCep) && empty($city)) {
            return null;
        }

        // Coordenadas das principais capitais e regiões de referência para geolocalização rápida
        $knownCoordinates = [
            'sao paulo' => ['lat' => -23.5505, 'lng' => -46.6333],
            'campinas' => ['lat' => -22.9099, 'lng' => -47.0626],
            'rio de janeiro' => ['lat' => -22.9068, 'lng' => -43.1729],
            'curitiba' => ['lat' => -25.4284, 'lng' => -49.2733],
            'belo horizonte' => ['lat' => -19.9167, 'lng' => -43.9345],
            'porto alegre' => ['lat' => -30.0346, 'lng' => -51.2177],
            'brasilia' => ['lat' => -15.7975, 'lng' => -47.8919]
        ];

        $normalizedCity = mb_strtolower(trim($city));
        if (isset($knownCoordinates[$normalizedCity])) {
            return $knownCoordinates[$normalizedCity];
        }

        // Fallback baseado no primeiro dígito do CEP brasileiro
        $firstDigit = $cleanCep[0] ?? '';
        $regionMap = [
            '0' => ['lat' => -23.5505, 'lng' => -46.6333], // Grande SP
            '1' => ['lat' => -22.9099, 'lng' => -47.0626], // Interior SP
            '2' => ['lat' => -22.9068, 'lng' => -43.1729], // RJ / ES
            '3' => ['lat' => -19.9167, 'lng' => -43.9345], // MG
            '4' => ['lat' => -12.9777, 'lng' => -38.5016], // BA / SE
            '5' => ['lat' => -8.0476,  'lng' => -34.8770], // PE / AL / PB / RN
            '6' => ['lat' => -3.7319,  'lng' => -38.5267], // CE / PI / MA / PA / AP / AM / RR / AC
            '7' => ['lat' => -15.7975, 'lng' => -47.8919], // DF / GO / TO / MT / RO / MS
            '8' => ['lat' => -25.4284, 'lng' => -49.2733], // PR / SC
            '9' => ['lat' => -30.0346, 'lng' => -51.2177], // RS
        ];

        return $regionMap[$firstDigit] ?? ['lat' => -23.5505, 'lng' => -46.6333];
    }
}
