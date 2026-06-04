<?php

declare(strict_types=1);

namespace Alpha\Controller\Actions\Customer\Auth;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Twig\Environment as TwigEnvironment;
use Alpha\Controller\Actions\ActionInterface;
use Alpha\Model\Domain\Repositories\LanguageRepository;
use Alpha\Model\Domain\Repositories\SettingRepository;
use Slim\Routing\RouteContext;

/**
 * ShowRegistrationFormAction - Exibe o formulário de cadastro para o cliente.
 */
class ShowRegistrationFormAction implements ActionInterface
{
    private TwigEnvironment $twig;
    private SettingRepository $settingRepository;
    private LanguageRepository $languageRepository;

    public function __construct(TwigEnvironment $twig, SettingRepository $settingRepository, LanguageRepository $languageRepository)
    {
        $this->twig = $twig;
        $this->settingRepository = $settingRepository;
        $this->languageRepository = $languageRepository;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $configSettings = $this->settingRepository->getSetting('config', 0);
        $languageCode = $configSettings['config_language_catalog'] ?? 'pt-br';
        $language = $this->languageRepository->getByCode($languageCode);

        if (!$language) {
            $language = $this->languageRepository->find(2); // Fallback para pt-br (ID 2)
        }

        $routeContext = RouteContext::fromRequest($request);
        $routeParser = $routeContext->getRouteParser();
        $lang = $request->getAttribute('lang', 'pt-br');

        $breadcrumbs = [
            ['text' => 'Início', 'href' => $routeParser->urlFor('home', ['lang' => $lang])],
            ['text' => 'Cadastro', 'href' => $routeParser->urlFor('register.form', ['lang' => $lang])]
        ];

        // Carrega as traduções dinamicamente a partir dos arquivos de idiomas do core
        $languageCodeStr = $language ? $language->getCode() : 'pt-br';
        $languageData = $this->loadLanguageData('account/register', $languageCodeStr);

        // Formatação dinâmica dos links contidos nas chaves de tradução
        if (isset($languageData['text_account_already'])) {
            $languageData['text_account_already'] = sprintf(
                $languageData['text_account_already'], 
                $routeParser->urlFor('login.form', ['lang' => $lang])
            );
        }
        if (isset($languageData['text_agree'])) {
            $agreeUrl = '/index.php?route=information/information&information_id=5'; // Fallback para Termos de Uso
            $agreeTitle = 'Termos e Condições';
            $languageData['text_agree'] = sprintf($languageData['text_agree'], $agreeUrl, $agreeTitle);
        }

        $languageData['button_continue'] = $languageData['button_continue'] ?? 'Continuar';

        // Parâmetros da requisição para mensagens rápidas na URL
        $queryParams = $request->getQueryParams();
        $errorWarning = isset($queryParams['error']) ? 'Aviso: Erro ao preencher os dados de cadastro.' : null;
        $success = isset($queryParams['success']) ? 'Sucesso: Registro efetuado com sucesso.' : null;

        $viewData = array_merge([
            'direction' => 'ltr',
            'lang' => $languageCodeStr,
            'title' => 'Criar Conta | AgSonhos',
            'description' => 'Crie sua conta para gerenciar seus pedidos e compras.',
            'breadcrumbs' => $breadcrumbs,
            'register' => $routeParser->urlFor('register.submit', ['lang' => $lang]), // Rota POST para submissão do formulário

            // Configurações do painel
            'config_telephone_display' => $configSettings['config_telephone_display'] ?? true,
            'config_telephone_required' => $configSettings['config_telephone_required'] ?? false,

            // Alertas e redirecionamentos
            'error_warning' => $errorWarning,
            'success' => $success,
            'redirect' => $queryParams['redirect'] ?? '',
            'customer_groups' => [] // Pode ser estendido se houver grupos múltiplos no banco
        ], $languageData);

        $html = $this->twig->render('pages/users/register.twig', $viewData);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }

    /**
     * Carrega de forma dinâmica e segura um arquivo de idioma PHP e retorna seu array associativo.
     */
    private function loadLanguageData(string $route, string $languageCode): array
    {
        $data = [];
        $file = '/var/www/html/agsonhos/core/language_legacy/' . $languageCode . '/' . $route . '.php';
        
        if (is_file($file)) {
            $_ = [];
            include $file;
            foreach ($_ as $key => $value) {
                $data[$key] = $value;
            }
        }
        return $data;
    }
}
