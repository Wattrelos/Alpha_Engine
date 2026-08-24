<?php

declare(strict_types=1);

namespace Alpha\Controller\Actions\Common;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Container\ContainerInterface;
use Alpha\Controller\Actions\ActionInterface;
use Alpha\Support\Presenters\ImagePresenter;
use Slim\Psr7\Stream;

/**
 * ImageCacheAction
 *
 * Intercepta requisições a imagens de cache (/image/cache/...) que ainda não
 * existam fisicamente no disco (ex: após limpeza de cache ou URLs pré-calculadas),
 * gera a imagem redimensionada sob demanda com ImagePresenter, grava no disco
 * e entrega a resposta binária diretamente ao cliente com cabeçalhos de alta performance.
 */
class ImageCacheAction implements ActionInterface
{
    private ImagePresenter $imagePresenter;
    private string $imageDir;

    public function __construct(?ContainerInterface $container = null)
    {
        if ($container && $container->has(ImagePresenter::class)) {
            $this->imagePresenter = $container->get(ImagePresenter::class);
        } elseif ($container && $container->has('image_presenter')) {
            $this->imagePresenter = $container->get('image_presenter');
        } else {
            $baseUrl = defined('HTTP_SERVER') ? HTTP_SERVER : '/';
            $imageDir = defined('DIR_IMAGE') ? DIR_IMAGE : (__DIR__ . '/../../../../../public_html/image/');
            $this->imagePresenter = new ImagePresenter($baseUrl, $imageDir);
        }

        $this->imageDir = rtrim(defined('DIR_IMAGE') ? DIR_IMAGE : (__DIR__ . '/../../../../../public_html/image/'), '/') . '/';
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args = []): ResponseInterface
    {
        $path = (string)($args['path'] ?? '');
        $path = rawurldecode(ltrim($path, '/'));

        if (empty($path)) {
            return $response->withStatus(404);
        }

        // Tenta resolver a imagem original e as dimensões desejadas a partir do caminho de cache
        $resolved = $this->imagePresenter->resolveSourceAndDimensions($path);

        $physicalPath = null;
        if ($resolved !== null) {
            $physicalPath = $this->imagePresenter->processAndGetPath(
                $resolved['source'],
                $resolved['width'],
                $resolved['height'],
                true
            );
        }

        // Se ainda não encontrou, tenta fallback direto para placeholder com dimensões padrão
        if (!$physicalPath || !is_file($physicalPath)) {
            $fallback = $this->imagePresenter->getFallbackImage();
            $physicalPath = $this->imagePresenter->processAndGetPath($fallback, 200, 200, true);
        }

        if (!$physicalPath || !is_file($physicalPath)) {
            return $response->withStatus(404);
        }

        // Determina MIME type
        $ext = strtolower(pathinfo($physicalPath, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png'         => 'image/png',
            'gif'         => 'image/gif',
            'webp'        => 'image/webp',
            'svg'         => 'image/svg+xml',
            default       => mime_content_type($physicalPath) ?: 'application/octet-stream',
        };

        $fileMtime = (int)filemtime($physicalPath);
        $fileSize  = (int)filesize($physicalPath);
        $etag      = '"' . md5($physicalPath . $fileMtime . $fileSize) . '"';

        // Validação de Cache HTTP 304 Not Modified
        $ifNoneMatch = $request->getHeaderLine('If-None-Match');
        $ifModifiedSince = $request->getHeaderLine('If-Modified-Since');

        if ($ifNoneMatch === $etag || ($ifModifiedSince && strtotime($ifModifiedSince) >= $fileMtime)) {
            return $response->withStatus(304);
        }

        $fileHandle = fopen($physicalPath, 'rb');
        if ($fileHandle === false) {
            return $response->withStatus(500);
        }

        $stream = new Stream($fileHandle);

        return $response
            ->withHeader('Content-Type', $mime)
            ->withHeader('Content-Length', (string)$fileSize)
            ->withHeader('Cache-Control', 'public, max-age=31536000, immutable')
            ->withHeader('Last-Modified', gmdate('D, d M Y H:i:s', $fileMtime) . ' GMT')
            ->withHeader('ETag', $etag)
            ->withBody($stream);
    }
}
