<?php

declare(strict_types=1);

namespace Alpha\Support;

/**
 * UploadSecurityHelper - Utilitário de Segurança para Uploads e Gestão de Mídias na Alpha Engine.
 * 
 * Protege a aplicação contra ataques de Path Traversal, substituição de extensões (double-extension)
 * e execução remota de código (RCE) através da inspeção do MIME-type real dos arquivos.
 */
class UploadSecurityHelper
{
    /** @var string[] Extensões de imagem estritamente permitidas */
    private const ALLOWED_EXTENSIONS = [
        'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'ico', 'avif'
    ];

    /** @var string[] MIME-types reais correspondentes às imagens permitidas */
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'image/svg+xml',
        'image/x-icon',
        'image/vnd.microsoft.icon',
        'image/avif'
    ];

    /**
     * Valida se um arquivo enviado é verdadeiramente uma imagem segura.
     * 
     * @param string $tmpPath Caminho temporário do arquivo (ex: $_FILES['file']['tmp_name'])
     * @param string $originalFilename Nome original do arquivo enviado pelo usuário
     * @return bool Retorna true se o arquivo for 100% seguro.
     */
    public static function isSafeImage(string $tmpPath, string $originalFilename): bool
    {
        if (empty($tmpPath) || !file_exists($tmpPath) || filesize($tmpPath) === 0) {
            return false;
        }

        // 1. Sanitização e verificação de dupla extensão (ex: script.php.jpg)
        $cleanFilename = self::sanitizeFilename($originalFilename);
        $ext = strtolower(pathinfo($cleanFilename, PATHINFO_EXTENSION));

        if (!in_array($ext, self::ALLOWED_EXTENSIONS, true)) {
            return false;
        }

        // Previne dupla extensão verificando se alguma parte do nome contém extensões executáveis
        if (preg_match('/\.(php|phtml|phar|inc|sh|cgi|pl|py)\./i', $cleanFilename)) {
            return false;
        }

        // 2. Inspeção do MIME-type real via finfo (Magic Bytes do arquivo)
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if (!$finfo) {
            return false;
        }

        $detectedMime = finfo_file($finfo, $tmpPath);
        finfo_close($finfo);

        if (!in_array($detectedMime, self::ALLOWED_MIME_TYPES, true)) {
            return false;
        }

        return true;
    }

    /**
     * Sanitiza o nome do arquivo prevenindo ataques de Path Traversal (ex: ../../etc/passwd)
     * e remoção de caracteres nulos (Null Byte Injection).
     */
    public static function sanitizeFilename(string $filename): string
    {
        // Remove Null Bytes
        $filename = str_replace(chr(0), '', $filename);
        
        // Extrai apenas o nome base (ignora diretórios enviados no caminho)
        $filename = basename($filename);

        // Remove caracteres não alfanuméricos exceto hífen, underline e ponto
        $filename = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $filename);

        // Previne nomes compostos apenas por pontos
        return trim($filename, '.');
    }

    /**
     * Gera um novo nome de arquivo aleatório e seguro utilizando UUID v4 / Hash.
     */
    public static function generateUniqueFilename(string $originalFilename): string
    {
        $cleanFilename = self::sanitizeFilename($originalFilename);
        $ext = strtolower(pathinfo($cleanFilename, PATHINFO_EXTENSION));
        
        if (!in_array($ext, self::ALLOWED_EXTENSIONS, true)) {
            $ext = 'jpg';
        }

        $randomHash = bin2hex(random_bytes(16));
        return sprintf('%s_%s.%s', date('Ymd_His'), $randomHash, $ext);
    }
}
