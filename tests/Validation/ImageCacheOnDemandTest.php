<?php

declare(strict_types=1);

namespace Tests\Validation;

use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Response;
use Alpha\Controller\Actions\Common\ImageCacheAction;
use Alpha\Support\Presenters\ImagePresenter;
use Containers\AppContainer;

class ImageCacheOnDemandTest extends TestCase
{
    private string $imageDir;
    private ServerRequestFactory $requestFactory;

    protected function setUp(): void
    {
        $this->imageDir = defined('DIR_IMAGE') ? DIR_IMAGE : (__DIR__ . '/../../public_html/image/');
        $this->requestFactory = new ServerRequestFactory();
    }

    /**
     * Teste 1: ImagePresenter resolveSourceAndDimensions analisa caminhos com dimensões.
     */
    public function testResolveSourceAndDimensions(): void
    {
        $presenter = new ImagePresenter('http://localhost/', $this->imageDir);

        $resolved = $presenter->resolveSourceAndDimensions('logomark/logo-120x80.png');
        $this->assertNotNull($resolved);
        $this->assertSame(120, $resolved['width']);
        $this->assertSame(80, $resolved['height']);
        $this->assertSame('png', $resolved['ext']);
    }

    /**
     * Teste 2: ImageCacheAction gera imagem sob demanda e retorna HTTP 200 com cabeçalhos de imagem e cache.
     */
    public function testImageCacheActionGeneratesOnDemand(): void
    {
        $container = new AppContainer();
        $presenter = new ImagePresenter('http://localhost/', $this->imageDir);
        $container->bind(ImagePresenter::class, $presenter);

        $action = new ImageCacheAction($container);
        $request = $this->requestFactory->createServerRequest('GET', '/image/cache/logomark/logo-64x64.png');
        $response = new Response();

        $result = $action($request, $response, ['path' => 'logomark/logo-64x64.png']);

        $this->assertSame(200, $result->getStatusCode());
        $this->assertTrue($result->hasHeader('Content-Type'));
        $this->assertSame('image/png', $result->getHeaderLine('Content-Type'));
        $this->assertTrue($result->hasHeader('Cache-Control'));
        $this->assertStringContainsString('public', $result->getHeaderLine('Cache-Control'));
        $this->assertTrue($result->hasHeader('ETag'));

        // Verifica que o arquivo físico foi salvo no disco
        $expectedDiskPath = rtrim($this->imageDir, '/') . '/cache/logomark/logo-64x64.png';
        $this->assertFileExists($expectedDiskPath);
    }

    /**
     * Teste 3: ImageCacheAction com imagem inexistente usa fallback gerando HTTP 200.
     */
    public function testImageCacheActionFallback(): void
    {
        $container = new AppContainer();
        $presenter = new ImagePresenter('http://localhost/', $this->imageDir);
        $container->bind(ImagePresenter::class, $presenter);

        $action = new ImageCacheAction($container);
        $request = $this->requestFactory->createServerRequest('GET', '/image/cache/inexistente/produto_fantasma-100x100.png');
        $response = new Response();

        $result = $action($request, $response, ['path' => 'inexistente/produto_fantasma-100x100.png']);

        $this->assertSame(200, $result->getStatusCode());
        $this->assertSame('image/png', $result->getHeaderLine('Content-Type'));
    }

    /**
     * Teste 4: ETag condicional retorna 304 Not Modified.
     */
    public function testImageCacheActionConditionalEtag(): void
    {
        $container = new AppContainer();
        $presenter = new ImagePresenter('http://localhost/', $this->imageDir);
        $container->bind(ImagePresenter::class, $presenter);

        $action = new ImageCacheAction($container);
        $request = $this->requestFactory->createServerRequest('GET', '/image/cache/logomark/logo-64x64.png');
        $response = new Response();

        $initial = $action($request, $response, ['path' => 'logomark/logo-64x64.png']);
        $etag = $initial->getHeaderLine('ETag');

        $cachedRequest = $this->requestFactory->createServerRequest('GET', '/image/cache/logomark/logo-64x64.png')
            ->withHeader('If-None-Match', $etag);

        $cachedResponse = $action($cachedRequest, new Response(), ['path' => 'logomark/logo-64x64.png']);
        $this->assertSame(304, $cachedResponse->getStatusCode());
    }
}
