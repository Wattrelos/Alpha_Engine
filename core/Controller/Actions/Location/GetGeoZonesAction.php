<?php

namespace Alpha\Controller\Actions\Location;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Controller\Actions\ActionInterface;
use Alpha\Mappers\EntityMappers\GeoZoneMapper;
use Alpha\Mappers\MapperFactory;

class GetGeoZonesAction implements ActionInterface
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $countryId = (int)($args['country_id'] ?? 0);
        
        $mapperFactory = MapperFactory::getInstance();
        /** @var GeoZoneMapper $zoneMapper */
        $zoneMapper = $mapperFactory->get(GeoZoneMapper::class);
        $zones = $zoneMapper->getZonesByCountryId($countryId);

        $zoneData = [];
        foreach ($zones as $zone) {
            $zoneData[] = [
                'id'   => $zone->getId(),
                'name' => $zone->getName(),
                'code' => $zone->getIsoCode()
            ];
        }

        $response->getBody()->write(json_encode([
            'zones' => $zoneData
        ]));

        return $response->withHeader('Content-Type', 'application/json');
    }
}
