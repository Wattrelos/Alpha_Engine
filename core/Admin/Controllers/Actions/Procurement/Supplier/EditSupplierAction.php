<?php

namespace Alpha\Admin\Controllers\Actions\Procurement\Supplier;

use Alpha\Controller\BaseController;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Model\Domain\Repositories\SupplierRepository;
use Alpha\Model\Domain\Repositories\RepositoryFactory;
use Alpha\Mappers\MapperFactory;
use Alpha\Mappers\EntityMappers\GeoCountryMapper;
use Alpha\Mappers\EntityMappers\GeoZoneMapper;
use Alpha\Mappers\EntityMappers\GeoCityMapper;

class EditSupplierAction extends BaseController implements \Alpha\Controller\Actions\ActionInterface
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $supplierId = (int)($args['id'] ?? 0);

        /** @var SupplierRepository $supplierRepository */
        $supplierRepository = RepositoryFactory::getInstance()->get(SupplierRepository::class);
        $supplier = $supplierRepository->find($supplierId);

        if (!$supplier) {
            $response->getBody()->write('Fornecedor não encontrado.');
            return $response->withStatus(404);
        }

        $mapperFactory = MapperFactory::getInstance();
        $countries = $mapperFactory->get(GeoCountryMapper::class)->getCountries();
        
        $zones = [];
        $cities = [];
        $address = $supplier->getAddresses();
        
        if ($address) {
            $country = $address->getCountry();
            if ($country) {
                $zones = $mapperFactory->get(GeoZoneMapper::class)->getZonesByCountryId($country->getId());
            }
            $zone = $address->getZone();
            if ($zone) {
                $cities = $mapperFactory->get(GeoCityMapper::class)->getCitiesByZoneId($zone->getId());
            }
        }

        $html = $this->getTemplate('admin/catalog/supplier/edit.html.twig', [
            'title'     => 'Editar Fornecedor | Painel Administrativo',
            'supplier'  => $supplier,
            'address'   => $address,
            'countries' => $countries,
            'zones'     => $zones,
            'cities'    => $cities
        ]);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}
