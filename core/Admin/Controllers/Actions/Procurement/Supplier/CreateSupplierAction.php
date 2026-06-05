<?php

namespace Alpha\Admin\Controllers\Actions\Procurement\Supplier;

use Alpha\Controller\BaseController;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Mappers\MapperFactory;
use Alpha\Mappers\EntityMappers\GeoCountryMapper;

class CreateSupplierAction extends BaseController implements \Alpha\Controller\Actions\ActionInterface
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $mapperFactory = MapperFactory::getInstance();
        /** @var GeoCountryMapper $countryMapper */
        $countryMapper = $mapperFactory->get(GeoCountryMapper::class);
        $countries = $countryMapper->getCountries();

        $html = $this->getTemplate('admin/catalog/supplier/create.html.twig', [
            'title'     => 'Adicionar Fornecedor | Painel Administrativo',
            'countries' => $countries
        ]);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}
