<?php

declare(strict_types=1);

namespace Tests\Validation;

require_once __DIR__ . '/../../backend/config.php';

use PHPUnit\Framework\TestCase;
use Containers\AppBootstrap;
use Alpha\Mappers\EntityMappers\ProductMapper;
use Alpha\Mappers\MapperFactory;
use Alpha\Model\Domain\Repositories\ProductRepository;
use Alpha\Model\Domain\Repositories\RepositoryFactory;

class FulltextSearchValidationTest extends TestCase
{
    private ProductMapper $mapper;
    private ProductRepository $productRepo;

    protected function setUp(): void
    {
        if (!defined('APPLICATION')) {
            define('APPLICATION', 'catalog');
        }
        AppBootstrap::boot();
        $this->mapper = MapperFactory::getInstance()->get(ProductMapper::class);
        $this->productRepo = RepositoryFactory::getInstance()->get(ProductRepository::class);
    }

    public function testTermSanitizationAndFormatting(): void
    {
        $testCases = [
            'smart tv'         => '+smart* +tv*',
            'cama  casal'      => '+cama* +casal*',
            'smart + tv - red' => '+smart* +tv* +red*',
            'travesseiro'      => '+travesseiro*'
        ];

        foreach ($testCases as $input => $expected) {
            $result = $this->mapper->prepareFullTextQuery($input);
            $this->assertEquals($expected, $result, "Sanitização de '$input' deve produzir '$expected'.");
        }
    }

    public function testFullTextSearchQueryExecution(): void
    {
        $filterData = [
            'filter_name' => 'Adaptador',
            'start'       => 0,
            'limit'       => 10
        ];

        $products = $this->mapper->getProducts($filterData, 2, 1, 1);
        $total = $this->mapper->getTotalProducts($filterData, 2, 1);

        $this->assertIsArray($products);
        $this->assertGreaterThanOrEqual(0, $total);
    }

    public function testShortTermFallback(): void
    {
        $filterShort = [
            'filter_name' => 'TV',
            'start'       => 0,
            'limit'       => 10
        ];

        $productsShort = $this->mapper->getProducts($filterShort, 2, 1, 1);
        $totalShort = $this->mapper->getTotalProducts($filterShort, 2, 1);

        $this->assertIsArray($productsShort);
        $this->assertGreaterThanOrEqual(0, $totalShort);
    }

    public function testProductRepositorySearchDataIntegration(): void
    {
        $viewResponse = $this->productRepo->getSearchData([
            'search' => 'Tubo',
            'limit'  => 5
        ]);

        $this->assertNotNull($viewResponse);
        $data = $viewResponse->getData();
        $this->assertArrayHasKey('products', $data);
        $this->assertArrayHasKey('product_total', $data);
    }
}
