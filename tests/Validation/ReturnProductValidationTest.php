<?php

declare(strict_types=1);

namespace Tests\Validation;

require_once __DIR__ . '/../../config.php';

use PHPUnit\Framework\TestCase;
use Containers\AppBootstrap;

class ReturnProductValidationTest extends TestCase
{
    private $container;

    protected function setUp(): void
    {
        if (!defined('APPLICATION')) {
            define('APPLICATION', 'catalog');
        }
        $bootstrap = AppBootstrap::boot();
        $this->container = $bootstrap->getContainer();
        $loader = new \Twig\Loader\ArrayLoader();
        $twig = new \Twig\Environment($loader);
        $this->container->bind(\Twig\Environment::class, $twig);
    }

    public function testCustomerReturnActionsCanBeResolved(): void
    {
        $addReturnAction = $this->container->get(\Alpha\Controller\Actions\Customer\Account\AddReturnAction::class);
        $this->assertNotNull($addReturnAction);
        $this->assertInstanceOf(\Alpha\Controller\Actions\Customer\Account\AddReturnAction::class, $addReturnAction);

        $showReturnAction = $this->container->get(\Alpha\Controller\Actions\Customer\Account\ShowReturnAction::class);
        $this->assertNotNull($showReturnAction);
        $this->assertInstanceOf(\Alpha\Controller\Actions\Customer\Account\ShowReturnAction::class, $showReturnAction);
    }

    public function testAdminReturnActionsCanBeResolved(): void
    {
        $listReturnsAction = $this->container->get(\Alpha\Admin\Controllers\Actions\Sales\Return\ListReturnsAction::class);
        $this->assertNotNull($listReturnsAction);

        $showReturnAction = $this->container->get(\Alpha\Admin\Controllers\Actions\Sales\Return\ShowReturnAction::class);
        $this->assertNotNull($showReturnAction);

        $updateReturnStatusAction = $this->container->get(\Alpha\Admin\Controllers\Actions\Sales\Return\UpdateReturnStatusAction::class);
        $this->assertNotNull($updateReturnStatusAction);
    }
}
