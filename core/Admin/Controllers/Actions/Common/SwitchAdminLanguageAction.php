<?php

namespace Alpha\Admin\Controllers\Actions\Common;

use Alpha\Controller\BaseController;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Model\Domain\Repositories\LanguageRepository;
use Alpha\Model\Domain\Repositories\RepositoryFactory;

class SwitchAdminLanguageAction extends BaseController implements \Alpha\Controller\Actions\ActionInterface
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();
        $langCode = trim($data['language_code'] ?? 'pt-br');

        /** @var LanguageRepository $languageRepository */
        $languageRepository = RepositoryFactory::getInstance()->get(LanguageRepository::class);
        $language = $languageRepository->getByCode($langCode);

        // Fallback to default if not found or inactive
        if (!$language || !$language->getStatus()) {
            $langCode = 'pt-br';
        } else {
            $langCode = $language->getCode();
        }

        // Create the cookie string for admin_language
        $expire = 30 * 86400; // 30 days
        $cookieHeader = sprintf(
            'admin_language=%s; Max-Age=%d; Path=/; HttpOnly; SameSite=Lax',
            $langCode,
            $expire
        );

        // Get referer to redirect user back to their current page
        $referer = $request->getHeaderLine('Referer');
        if (empty($referer)) {
            $referer = '/LPDHED2dC7Gjrg2b/dashboard';
        }

        return $response
            ->withHeader('Set-Cookie', $cookieHeader)
            ->withHeader('Location', $referer)
            ->withStatus(302);
    }
}
