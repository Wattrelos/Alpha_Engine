<?php

namespace Alpha\Admin\Controllers\Actions\Setting\StoreSetting;

use Alpha\Controller\BaseController;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Model\Domain\Repositories\SettingRepository;
use Alpha\Model\DataAccessObject\DataAccessObject;
use Alpha\Model\DataAccessObject\QueryBuilder;
use Alpha\Mappers\EntityMappers\GeoCountryMapper;

class UpdateStoreSettingAction extends BaseController implements \Alpha\Controller\Actions\ActionInterface
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        /** @var SettingRepository $settingRepo */
        $settingRepo = $this->getRepository(SettingRepository::class);
        $settings = $settingRepo->getSetting('config', 1);

        $formData = $request->getParsedBody();
        $errors = [];

        // Validation
        $configName = trim($formData['config_name'] ?? '');
        $configOwner = trim($formData['config_owner'] ?? '');
        $configAddress = trim($formData['config_address'] ?? '');
        $configEmail = trim($formData['config_email'] ?? '');
        $configTelephone = trim($formData['config_telephone'] ?? '');

        $metaTitle = trim($formData['meta_title'] ?? '');
        $metaDescription = trim($formData['meta_description'] ?? '');
        $metaKeyword = trim($formData['meta_keyword'] ?? '');

        if (mb_strlen($configName) < 1 || mb_strlen($configName) > 32) {
            $errors['config_name'] = 'O nome da loja deve ter entre 1 e 32 caracteres.';
        }

        if (mb_strlen($configOwner) < 1 || mb_strlen($configOwner) > 64) {
            $errors['config_owner'] = 'O proprietário da loja deve ter entre 1 e 64 caracteres.';
        }

        if (mb_strlen($configAddress) < 5 || mb_strlen($configAddress) > 256) {
            $errors['config_address'] = 'O endereço deve ter entre 5 e 256 caracteres.';
        }

        if (!filter_var($configEmail, FILTER_VALIDATE_EMAIL)) {
            $errors['config_email'] = 'O e-mail informado não é válido.';
        }

        if (mb_strlen($configTelephone) < 3 || mb_strlen($configTelephone) > 32) {
            $errors['config_telephone'] = 'O telefone deve ter entre 3 e 32 caracteres.';
        }

        if (empty($metaTitle)) {
            $errors['meta_title'] = 'O título meta (SEO) é obrigatório.';
        }

        // Handle image uploads
        $configLogo = $settings['config_logo'] ?? '';
        $configIcon = $settings['config_icon'] ?? '';

        if (!empty($formData['remove_logo'])) {
            $configLogo = '';
        }
        if (!empty($formData['remove_icon'])) {
            $configIcon = '';
        }

        $uploadedFiles = $request->getUploadedFiles();

        // 1. Logo File Upload
        /** @var \Psr\Http\Message\UploadedFileInterface|null $logoFile */
        $logoFile = $uploadedFiles['config_logo'] ?? null;
        if ($logoFile && $logoFile->getError() === UPLOAD_ERR_OK) {
            $clientFilename = $logoFile->getClientFilename();
            $extension = strtolower(pathinfo($clientFilename, PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'ico'];

            if (in_array($extension, $allowedExtensions, true)) {
                $safeFilename = sprintf('logo_%d_%s.%s', time(), uniqid(), $extension);
                $targetPathRel = 'image/catalog/' . $safeFilename;
                $targetPathAbs = DIR_IMAGE . $targetPathRel;

                $targetDir = dirname($targetPathAbs);
                if (!is_dir($targetDir)) {
                    @mkdir($targetDir, 0755, true);
                }

                try {
                    $logoFile->moveTo($targetPathAbs);
                    $configLogo = $targetPathRel;
                } catch (\Throwable $e) {
                    $errors['config_logo'] = 'Falha ao mover logotipo: ' . $e->getMessage();
                }
            } else {
                $errors['config_logo'] = 'Logotipo possui extensão inválida.';
            }
        }

        // 2. Icon File Upload
        /** @var \Psr\Http\Message\UploadedFileInterface|null $iconFile */
        $iconFile = $uploadedFiles['config_icon'] ?? null;
        if ($iconFile && $iconFile->getError() === UPLOAD_ERR_OK) {
            $clientFilename = $iconFile->getClientFilename();
            $extension = strtolower(pathinfo($clientFilename, PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'ico'];

            if (in_array($extension, $allowedExtensions, true)) {
                $safeFilename = sprintf('icon_%d_%s.%s', time(), uniqid(), $extension);
                $targetPathRel = 'image/catalog/' . $safeFilename;
                $targetPathAbs = DIR_IMAGE . $targetPathRel;

                $targetDir = dirname($targetPathAbs);
                if (!is_dir($targetDir)) {
                    @mkdir($targetDir, 0755, true);
                }

                try {
                    $iconFile->moveTo($targetPathAbs);
                    $configIcon = $targetPathRel;
                } catch (\Throwable $e) {
                    $errors['config_icon'] = 'Falha ao mover ícone: ' . $e->getMessage();
                }
            } else {
                $errors['config_icon'] = 'Ícone possui extensão inválida.';
            }
        }

        if (empty($errors)) {
            // Build config_description block for language 2
            $configDescription = [
                2 => [
                    'meta_title'       => $metaTitle,
                    'meta_description' => $metaDescription,
                    'meta_keyword'     => $metaKeyword
                ]
            ];

            // Merge with existing settings keys to prevent loss of other config parameters
            $newSettings = $settings;

            // Update targeted keys
            $newSettings['config_name'] = $configName;
            $newSettings['config_owner'] = $configOwner;
            $newSettings['config_address'] = $configAddress;
            $newSettings['config_email'] = $configEmail;
            $newSettings['config_telephone'] = $configTelephone;
            $newSettings['config_logo'] = $configLogo;
            $newSettings['config_icon'] = $configIcon;
            $newSettings['config_description'] = $configDescription;

            // Socials
            $newSettings['config_facebook'] = trim($formData['config_facebook'] ?? '');
            $newSettings['config_instagram'] = trim($formData['config_instagram'] ?? '');
            $newSettings['config_youtube'] = trim($formData['config_youtube'] ?? '');

            try {
                $settingRepo->editSetting('config', $newSettings, 1);

                $infoLangId = (int)($formData['info_language_id'] ?? 2);

                // Save institutional information pages for the target language
                /** @var \Alpha\Model\Domain\Repositories\InformationRepository $infoRepo */
                $infoRepo = $this->getRepository(\Alpha\Model\Domain\Repositories\InformationRepository::class);

                if (!empty($formData['information_pages']) && is_array($formData['information_pages'])) {
                    foreach ($formData['information_pages'] as $infoId => $infoData) {
                        $infoId = (int)$infoId;
                        if ($infoId > 0 && !empty($infoData['title'])) {
                            $infoRepo->saveInformationPage($infoId, $infoData, $infoLangId);
                        }
                    }
                }

                // Process new institutional page creation
                if (!empty($formData['new_information']) && is_array($formData['new_information'])) {
                    $newTitle = trim($formData['new_information']['title'] ?? '');
                    if (!empty($newTitle)) {
                        $infoRepo->createInformationPage($formData['new_information'], $infoLangId);
                    }
                }

                $_SESSION['success'] = 'Configurações da loja e páginas institucionais atualizadas com sucesso!';

                $tabRedirect = !empty($formData['active_tab']) ? '?tab=' . urlencode($formData['active_tab']) . '&lang_id=' . $infoLangId : '?tab=information&lang_id=' . $infoLangId;

                return $response
                    ->withHeader('Location', (defined('ADMIN_PATH') ? ADMIN_PATH : '/LPDHED2dC7Gjrg2b') . '/configuracoes' . $tabRedirect)
                    ->withStatus(302);
            } catch (\Throwable $e) {
                $errors['warning'] = 'Erro ao persistir configurações no banco: ' . $e->getMessage();
            }
        }

        // Formats current image previews for rendering errors
        $logoUrl = '';
        if (!empty($configLogo)) {
            $logoUrl = HTTP_SERVER . (str_starts_with($configLogo, 'image/') ? '' : 'image/') . $configLogo;
        }

        $iconUrl = '';
        if (!empty($configIcon)) {
            $iconUrl = HTTP_SERVER . (str_starts_with($configIcon, 'image/') ? '' : 'image/') . $configIcon;
        }

        // Fetch countries & Brazilian zones (UF)
        $countries = [];
        $zones = [];
        try {
            /** @var GeoCountryMapper $countryMapper */
            $countryMapper = $this->getMapper(GeoCountryMapper::class);
            $countries = $countryMapper->getCountries();

            /** @var \Alpha\Mappers\EntityMappers\GeoZoneMapper $zoneMapper */
            $zoneMapper = $this->getMapper(\Alpha\Mappers\EntityMappers\GeoZoneMapper::class);
            $zones = array_map(fn(\Alpha\Model\Domain\Entities\Geo\Zone $z) => [
                'id'   => $z->getId(),
                'name' => $z->getName(),
                'code' => $z->getIsoCode()
            ], $zoneMapper->getZonesByCountryId(30));
        } catch (\Throwable $ex) {
        }

        // Load active languages and information pages for re-rendering on error
        $informationPages = [];
        $languages = [];
        $infoLangId = (int)($formData['info_language_id'] ?? 2);
        try {
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

            /** @var \Alpha\Model\Domain\Repositories\InformationRepository $infoRepo */
            $infoRepo = $this->getRepository(\Alpha\Model\Domain\Repositories\InformationRepository::class);
            $informationPages = $infoRepo->getAllInformationsAdmin($infoLangId);
        } catch (\Throwable $ex) {
        }

        // Set warnings
        $errors['warning'] = $errors['warning'] ?? 'Por favor, verifique os erros informados no formulário.';

        $html = $this->getTemplate('admin/setting/store_setting/edit.html.twig', [
            'title'                     => 'Configurações da Loja | Painel Administrativo',
            'settings'                  => [
                'config_name'      => $configName,
                'config_owner'     => $configOwner,
                'config_address'   => $configAddress,
                'config_email'     => $configEmail,
                'config_telephone' => $configTelephone,
                'config_facebook'  => $formData['config_facebook'] ?? '',
                'config_instagram' => $formData['config_instagram'] ?? '',
                'config_youtube'   => $formData['config_youtube'] ?? '',
                'config_logo'      => $configLogo,
                'config_icon'      => $configIcon
            ],
            'meta_title'                => $metaTitle,
            'meta_description'          => $metaDescription,
            'meta_keyword'              => $metaKeyword,
            'logo_url'                  => $logoUrl,
            'icon_url'                  => $iconUrl,
            'countries'                 => $countries,
            'zones'                     => $zones,
            'information_pages'         => $informationPages,
            'languages'                 => $languages,
            'current_info_language_id'  => $infoLangId,
            'errors'                    => $errors
        ]);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}
