<?php

namespace Alpha\Admin\Controllers\Actions\Procurement\Supplier;

use Alpha\Controller\BaseController;
use Alpha\Mappers\EntityMappers\GeoCountryMapper;
use Alpha\Mappers\MapperFactory;
use Alpha\Model\Domain\Repositories\ManufacturerRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class CreateSupplierAction extends BaseController implements \Alpha\Controller\Actions\ActionInterface
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $mapperFactory = MapperFactory::getInstance();
        /** @var GeoCountryMapper $countryMapper */
        $countryMapper = $mapperFactory->get(GeoCountryMapper::class);
        $countries = $countryMapper->getCountries();

        /** @var ManufacturerRepository $manufacturerRepo */
        $manufacturerRepo = $this->getRepository(ManufacturerRepository::class);
        $manufacturers = $manufacturerRepo->getManufacturers();

        $html = $this->getTemplate('admin/catalog/supplier/create.html.twig', [
            'title'         => 'Adicionar Fornecedor | Painel Administrativo',
            'countries'     => $countries,
            'manufacturers' => $manufacturers
        ]);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}

