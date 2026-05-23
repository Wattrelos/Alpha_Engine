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
        // Alpha Engine: Carrega sob demanda (Lazy Load) o model legado de imagem 
        // para não quebrar a arquitetura base do OpenCart
        if (!$this->registry->has('model_tool_image')) {
            $this->registry->get('load')->model('tool/image');
        }

        $imageModel = $this->registry->get('model_tool_image');

        if (!empty($filename) && is_file(DIR_IMAGE . html_entity_decode($filename, ENT_QUOTES, 'UTF-8'))) {
            return $imageModel->resize($filename, $width, $height);
        }

        if ($fallback) {
            return $imageModel->resize('placeholder.png', $width, $height);
        }

        return '';
    }
}