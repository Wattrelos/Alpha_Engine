<?php
namespace Alpha\Support\Presenters;

use Opencart\System\Engine\Registry;

/**
 * ImagePresenter
 * 
 * Responsável por padronizar o processamento e exibição de imagens em toda a loja.
 * Centraliza a verificação física do arquivo e o fallback (placeholder) de forma segura.
 */
class ImagePresenter 
{
    private Registry $registry;

    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Redimensiona uma imagem, retornando um placeholder caso não exista.
     * 
     * @param string|null $filename Caminho relativo da imagem no diretório image/
     * @param int $width Largura do redimensionamento
     * @param int $height Altura do redimensionamento
     * @param bool $fallback Se true, retorna 'placeholder.png' em caso de falha. Se false, retorna string vazia.
     * @return string URL absoluta da imagem redimensionada
     */
    public function resize(?string $filename, int $width, int $height, bool $fallback = true): string
    {
        if (empty($filename) || !is_file(DIR_IMAGE . html_entity_decode($filename, ENT_QUOTES, 'UTF-8'))) {
            if ($fallback) {
                $filename = 'placeholder.png';
            } else {
                return '';
            }
        }

        $filename = html_entity_decode($filename, ENT_QUOTES, 'UTF-8');

        if (!is_file(DIR_IMAGE . $filename) || substr(str_replace('\\', '/', realpath(DIR_IMAGE . $filename)), 0, strlen(DIR_IMAGE)) != DIR_IMAGE) {
            return '';
        }

        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        $image_old = $filename;
        $image_new = 'cache/' . oc_substr($filename, 0, oc_strrpos($filename, '.')) . '-' . (int)$width . 'x' . (int)$height . '.' . $extension;

        if (!is_file(DIR_IMAGE . $image_new) || (filemtime(DIR_IMAGE . $image_old) > filemtime(DIR_IMAGE . $image_new))) {
            [$width_orig, $height_orig, $image_type] = getimagesize(DIR_IMAGE . $image_old);

            if (!in_array($image_type, [IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF, IMAGETYPE_WEBP])) {
                return $this->registry->get('config')->get('config_url') . 'image/' . $image_old;
            }

            $path = '';
            $directories = explode('/', dirname($image_new));

            foreach ($directories as $directory) {
                if (!$path) {
                    $path = $directory;
                } else {
                    $path = $path . '/' . $directory;
                }

                if (!is_dir(DIR_IMAGE . $path)) {
                    @mkdir(DIR_IMAGE . $path, 0777);
                }
            }

            if ($width_orig != $width || $height_orig != $height) {
                $image = new \Opencart\System\Library\Image(DIR_IMAGE . $image_old);
                $image->resize($width, $height);
                $image->save(DIR_IMAGE . $image_new);
            } else {
                copy(DIR_IMAGE . $image_old, DIR_IMAGE . $image_new);
            }
        }

        $image_new = str_replace(' ', '%20', $image_new);

        return $this->registry->get('config')->get('config_url') . 'image/' . $image_new;
    }
}