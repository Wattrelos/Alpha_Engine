<?php

namespace Alpha\Admin\Controllers\Actions\Setting\StoreSetting;

use Alpha\Controller\BaseController;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Model\Domain\Repositories\SettingRepository;

class EditStoreSettingAction extends BaseController implements \Alpha\Controller\Actions\ActionInterface
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        /** @var SettingRepository $settingRepo */
        $settingRepo = $this->getRepository(SettingRepository::class);
        $settings = $settingRepo->getSetting('config', 1);

        // Parse localized descriptions (meta title, description, keywords) for language ID 2 (Portuguese)
        $descArr = [];
        if (!empty($settings['config_description'])) {
            $descValue = $settings['config_description'];
            if (is_string($descValue)) {
                $descArr = json_decode($descValue, true);
            } elseif (is_array($descValue)) {
                $descArr = $descValue;
            }
        }
        
        $metaTitle = $descArr[2]['meta_title'] ?? $settings['config_meta_title'] ?? '';
        $metaDescription = $descArr[2]['meta_description'] ?? $settings['config_meta_description'] ?? '';
        $metaKeyword = $descArr[2]['meta_keyword'] ?? $settings['config_meta_keyword'] ?? '';

        // Format logo and icon URLs for rendering current previews
        $logoUrl = '';
        if (!empty($settings['config_logo'])) {
            $logoUrl = HTTP_SERVER . (str_starts_with($settings['config_logo'], 'image/') ? '' : 'image/') . $settings['config_logo'];
        }
        
        $iconUrl = '';
        if (!empty($settings['config_icon'])) {
            $iconUrl = HTTP_SERVER . (str_starts_with($settings['config_icon'], 'image/') ? '' : 'image/') . $settings['config_icon'];
        }

        /** @var \Alpha\Model\Domain\Repositories\LanguageRepository $langRepo */
        $langRepo = $this->getRepository(\Alpha\Model\Domain\Repositories\LanguageRepository::class);
        $rawLanguages = $langRepo->findAll();
        $languages = array_map(function($l) {
            return [
                'id'    => $l->getId(),
                'name'  => method_exists($l, 'getName') ? $l->getName() : ($l->name ?? ''),
                'code'  => method_exists($l, 'getCode') ? $l->getCode() : ($l->code ?? ''),
                'image' => method_exists($l, 'getImage') ? $l->getImage() : ($l->image ?? '')
            ];
        }, $rawLanguages);

        $queryParams = $request->getQueryParams();
        $currentInfoLangId = (int)($queryParams['lang_id'] ?? 2);

        /** @var \Alpha\Model\Domain\Repositories\InformationRepository $infoRepo */
        $infoRepo = $this->getRepository(\Alpha\Model\Domain\Repositories\InformationRepository::class);
        $informationPages = $infoRepo->getAllInformationsAdmin($currentInfoLangId);

        // Session notifications
        $success = $_SESSION['success'] ?? '';
        $error = $_SESSION['error'] ?? '';
        unset($_SESSION['success'], $_SESSION['error']);

        $html = $this->getTemplate('admin/setting/store_setting/edit.html.twig', [
            'title'                     => 'Configurações da Loja | Painel Administrativo',
            'settings'                  => $settings,
            'meta_title'                => $metaTitle,
            'meta_description'          => $metaDescription,
            'meta_keyword'              => $metaKeyword,
            'logo_url'                  => $logoUrl,
            'icon_url'                  => $iconUrl,
            'information_pages'         => $informationPages,
            'languages'                 => $languages,
            'current_info_language_id'  => $currentInfoLangId,
            'success'                   => $success,
            'error'                     => $error
        ]);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}
