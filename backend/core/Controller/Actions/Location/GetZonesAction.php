<?php

namespace Alpha\Controller\Actions\Location;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Controller\Actions\ActionInterface;
use Alpha\Model\Domain\Repositories\GeoZoneRepository;
use Slim\Routing\RouteContext;

/**
 * GetZonesAction - Retorna a lista de estados de um país em formato JSON.
 */
class GetZonesAction implements ActionInterface
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $countryId = (int)($args['country_id'] ?? 0);
        
        /** @var GeoZoneRepository $zoneRepository */
        $zoneRepository = \Alpha\Model\Domain\Repositories\RepositoryFactory::getInstance()->get(GeoZoneRepository::class);
        $zones = $zoneRepository->getZonesByCountryId($countryId);

        $zoneData = [];
        foreach ($zones as $zone) {
            $isoCode = $zone->getIsoCode();
            $zoneData[] = [
                'id'   => $zone->getId(),
                'name' => $zone->getName(),
                'code' => str_contains($isoCode, '-') ? explode('-', $isoCode)[1] : $isoCode
            ];
        }

        $response->getBody()->write(json_encode([
            'zones' => $zoneData
        ]));

        return $response->withHeader('Content-Type', 'application/json');
    }
}
