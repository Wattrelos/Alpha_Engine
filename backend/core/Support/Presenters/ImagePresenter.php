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
        $this->imageDir = rtrim($imageDir ?: (defined('DIR_IMAGE') ? DIR_IMAGE : ''), '/') . '/';
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
        $dstPath = $this->processAndGetPath($filename, $width, $height, $fallback);
        if (!$dstPath || !is_file($dstPath)) {
            return '';
        }

        $cacheRelPath = substr($dstPath, strlen($this->imageDir));
        return $this->baseUrl . 'image/' . str_replace(' ', '%20', str_replace('\\', '/', $cacheRelPath));
    }

    /**
     * Processa o redimensionamento e retorna o caminho físico absoluto do arquivo no disco.
     */
    public function processAndGetPath(?string $filename, int $width, int $height, bool $fallback = true): ?string
    {
        $filename = $filename ? html_entity_decode($filename, ENT_QUOTES, 'UTF-8') : '';
        $filename = ltrim($filename, '/');

        // Se o caminho salvo no BD incluir o prefixo 'image/', normaliza removendo-o
        if (str_starts_with($filename, 'image/')) {
            $filename = substr($filename, 6);
        }

        // Verifica existência e segurança do caminho
        if (!$this->isValidImage($filename)) {
            if (!$fallback) {
                return null;
            }
            $filename = $this->getFallbackImage();
            if (!$this->isValidImage($filename)) {
                return null;
            }
        }

        $width  = max(1, $width);
        $height = max(1, $height);

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
                return $srcPath;
            }

            // Cria diretório de cache se necessário com permissões completas
            $cacheDir = dirname($dstPath);
            if (!is_dir($cacheDir)) {
                @mkdir($cacheDir, 0777, true);
            }
            if (is_dir($cacheDir)) {
                @chmod($cacheDir, 0777);
            }

            if ($origW === $width && $origH === $height) {
                @copy($srcPath, $dstPath);
            } else {
                $this->resizeWithGd($srcPath, $dstPath, $imageType, $origW, $origH, $width, $height);
            }

            if (is_file($dstPath)) {
                @chmod($dstPath, 0666);
            }
        }

        return is_file($dstPath) ? $dstPath : (is_file($srcPath) ? $srcPath : null);
    }

    /**
     * Analisa um caminho relativo de cache (ex: 'catalog/demo-228x228.jpg') e extrai o arquivo de origem e dimensões.
     * Retorna array com ['source' => string, 'width' => int, 'height' => int, 'ext' => string] ou null.
     */
    public function resolveSourceAndDimensions(string $cachePath): ?array
    {
        $cachePath = ltrim(str_replace('\\', '/', $cachePath), '/');
        if (str_starts_with($cachePath, 'cache/')) {
            $cachePath = substr($cachePath, 6);
        }

        if (preg_match('/^(.*)-([0-9]+)x([0-9]+)\.([a-zA-Z0-9]+)$/i', $cachePath, $matches)) {
            $baseName = $matches[1];
            $width    = (int)$matches[2];
            $height   = (int)$matches[3];
            $ext      = strtolower($matches[4]);

            // Candidatos de arquivo original
            $candidateExtensions = [$ext, 'jpg', 'jpeg', 'png', 'webp', 'gif'];
            $foundSource = null;

            foreach (array_unique($candidateExtensions) as $cExt) {
                $testPath = $baseName . '.' . $cExt;
                if ($this->isValidImage($testPath)) {
                    $foundSource = $testPath;
                    break;
                }
            }

            if ($foundSource === null) {
                $foundSource = $this->getFallbackImage();
            }

            return [
                'source' => $foundSource,
                'width'  => $width,
                'height' => $height,
                'ext'    => $ext,
                'cache'  => 'cache/' . $cachePath
            ];
        }

        // Se não tiver padrão -WxH.ext mas o arquivo existir diretamente em image/
        if ($this->isValidImage($cachePath)) {
            [$w, $h] = @getimagesize($this->imageDir . $cachePath) ?: [0, 0];
            return [
                'source' => $cachePath,
                'width'  => $w ?: 500,
                'height' => $h ?: 500,
                'ext'    => strtolower(pathinfo($cachePath, PATHINFO_EXTENSION)),
                'cache'  => 'cache/' . $cachePath
            ];
        }

        return null;
    }

    /**
     * Localiza ou gera uma imagem de fallback segura (placeholder / no-image).
     */
    public function getFallbackImage(): string
    {
        $candidates = ['placeholder.png', 'no-image.png', 'no_image.png', 'placeholder.jpg'];
        foreach ($candidates as $cand) {
            if ($this->isValidImage($cand)) {
                return $cand;
            }
        }

        // Se nenhuma existir fisicamente, cria um placeholder elegante dinâmico no disco
        $defaultPlaceholder = 'placeholder.png';
        $fullPath = $this->imageDir . $defaultPlaceholder;
        $img = @imagecreatetruecolor(300, 300);
        if ($img) {
            $bg = imagecolorallocate($img, 240, 242, 245);
            $border = imagecolorallocate($img, 210, 215, 220);
            $text = imagecolorallocate($img, 140, 150, 160);
            imagefilledrectangle($img, 0, 0, 300, 300, $bg);
            imagerectangle($img, 0, 0, 299, 299, $border);
            imagestring($img, 4, 95, 140, 'SEM IMAGEM', $text);
            imagepng($img, $fullPath);
            imagedestroy($img);
            @chmod($fullPath, 0666);
            return $defaultPlaceholder;
        }

        return 'no-image.png';
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
        // Proteção path traversal com suporte a symlinks
        $real = str_replace('\\', '/', (string)realpath($fullPath));
        $realDir = str_replace('\\', '/', (string)(realpath($this->imageDir) ?: $this->imageDir));
        $realDir = rtrim($realDir, '/') . '/';
        return str_starts_with($real, $realDir);
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
