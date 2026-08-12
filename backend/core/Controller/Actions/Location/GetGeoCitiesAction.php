<?php

namespace Alpha\Controller\Actions\Location;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Controller\Actions\ActionInterface;
use Alpha\Mappers\EntityMappers\GeoCityMapper;
use Alpha\Mappers\MapperFactory;

class GetGeoCitiesAction implements ActionInterface
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $zoneId = (int)($args['zone_id'] ?? 0);
        
        $mapperFactory = MapperFactory::getInstance();
        /** @var GeoCityMapper $cityMapper */
        $cityMapper = $mapperFactory->get(GeoCityMapper::class);
        $cities = $cityMapper->getCitiesByZoneId($zoneId);

        $cityData = [];
        foreach ($cities as $city) {
            $cityData[] = [
                'id'   => $city->getId(),
                'name' => $city->getName()
            ];
        }

        $response->getBody()->write(json_encode([
            'cities' => $cityData
        ]));

        return $response->withHeader('Content-Type', 'application/json');
    }
}
