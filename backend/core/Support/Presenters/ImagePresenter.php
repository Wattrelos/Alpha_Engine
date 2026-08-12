<?php

namespace Alpha\Support\Presenters;

/**
 * ImagePresenter
 *
 * Padroniza o processamento e exibição de imagens em toda a loja.
 * Centraliza a verificação física do arquivo, o resize via GD nativo
 * e o fallback (placeholder) de forma segura, sem dependênciasdo código legado.
 */
class ImagePresenter
{
    private string $baseUrl;
    private string $imageDir;

    /**
     * @param string $baseUrl  URL base da aplicação (ex: 'https://loja.com.br/').
     * @param string $imageDir Caminho absoluto do diretório de imagens, com barra final.
     */
    public function __construct(string $baseUrl, string $imageDir = '')
    {
        $this->baseUrl  = rtrim($baseUrl, '/') . '/';
        $this->imageDir = $imageDir ?: (defined('DIR_IMAGE') ? DIR_IMAGE : '');
    }

    /**
     * Redimensiona uma imagem, retornando um placeholder caso não exista.
     *
     * @param string|null $filename Caminho relativo da imagem dentro de imageDir.
     * @param int  $width    Largura desejada.
     * @param int  $height   Altura desejada.
     * @param bool $fallback Se true retorna a URL do placeholder em caso de falha;
     *                       se false retorna string vazia.
     * @return string URL absoluta da imagem (original ou redimensionada).
     */
    public function resize(?string $filename, int $width, int $height, bool $fallback = true): string
    {
        $filename = $filename ? html_entity_decode($filename, ENT_QUOTES, 'UTF-8') : '';

        // Verifica existência e segurança do caminho
        if (!$this->isValidImage($filename)) {
            if (!$fallback) {
                return '';
            }
            $filename = 'placeholder.png';
            if (!$this->isValidImage($filename)) {
                return '';
            }
        }

        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $cacheName = \Alpha\Support\AlphaString::substr($filename, 0, \Alpha\Support\AlphaString::strrpos($filename, '.'))
                   . '-' . (int)$width . 'x' . (int)$height . '.' . $extension;
        $cachePath = 'cache/' . $cacheName;

        $srcPath   = $this->imageDir . $filename;
        $dstPath   = $this->imageDir . $cachePath;

        // Usa cache se já existir e estiver atualizado
        if (!is_file($dstPath) || filemtime($srcPath) > filemtime($dstPath)) {
            [$origW, $origH, $imageType] = @getimagesize($srcPath) ?: [0, 0, 0];

            $supportedTypes = [IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF, IMAGETYPE_WEBP];
            if (!in_array($imageType, $supportedTypes, true)) {
                return $this->baseUrl . 'image/' . $filename;
            }

            // Cria diretório de cache se necessário
            $cacheDir = dirname($dstPath);
            if (!is_dir($cacheDir)) {
                @mkdir($cacheDir, 0755, true);
            }

            if ($origW === $width && $origH === $height) {
                copy($srcPath, $dstPath);
            } else {
                $this->resizeWithGd($srcPath, $dstPath, $imageType, $origW, $origH, $width, $height);
            }
        }

        return $this->baseUrl . 'image/' . str_replace(' ', '%20', $cachePath);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Helpers privados
    // ──────────────────────────────────────────────────────────────────────────

    private function isValidImage(string $filename): bool
    {
        if (empty($filename)) {
            return false;
        }
        $fullPath = $this->imageDir . $filename;
        if (!is_file($fullPath)) {
            return false;
        }
        // Protecção path traversal
        $real = str_replace('\\', '/', (string)realpath($fullPath));
        return str_starts_with($real, $this->imageDir);
    }

    /**
     * Resize proporcional com preenchimento (letterbox) usando GD nativo.
     */
    private function resizeWithGd(
        string $src,
        string $dst,
        int    $imageType,
        int    $origW,
        int    $origH,
        int    $targetW,
        int    $targetH
    ): void {
        $source = match ($imageType) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($src),
            IMAGETYPE_PNG  => @imagecreatefrompng($src),
            IMAGETYPE_GIF  => @imagecreatefromgif($src),
            IMAGETYPE_WEBP => @imagecreatefromwebp($src),
            default        => false,
        };

        if (!$source) {
            return;
        }

        // Escala proporcional dentro do box target (letterbox transparente)
        $scale = min($targetW / $origW, $targetH / $origH);
        $newW  = (int)round($origW * $scale);
        $newH  = (int)round($origH * $scale);
        $offX  = (int)round(($targetW - $newW) / 2);
        $offY  = (int)round(($targetH - $newH) / 2);

        $canvas = imagecreatetruecolor($targetW, $targetH);

        // Fundo transparente para PNG/WEBP, branco para JPEG/GIF
        if (in_array($imageType, [IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) {
            imagealphablending($canvas, false);
            imagesavealpha($canvas, true);
            $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
            imagefill($canvas, 0, 0, $transparent);
        } else {
            $white = imagecolorallocate($canvas, 255, 255, 255);
            imagefill($canvas, 0, 0, $white);
        }

        imagecopyresampled($canvas, $source, $offX, $offY, 0, 0, $newW, $newH, $origW, $origH);

        match ($imageType) {
            IMAGETYPE_JPEG => imagejpeg($canvas, $dst, 90),
            IMAGETYPE_PNG  => imagepng($canvas, $dst, 8),
            IMAGETYPE_GIF  => imagegif($canvas, $dst),
            IMAGETYPE_WEBP => imagewebp($canvas, $dst, 90),
            default        => null,
        };

        imagedestroy($source);
        imagedestroy($canvas);
    }
}
