<?php

declare(strict_types=1);

namespace Alpha\Controller\Actions\Customer\Auth;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Controller\Actions\ActionInterface;
use Alpha\Model\Domain\Repositories\LanguageRepository;
use Alpha\Model\Domain\Repositories\SettingRepository;
use Twig\Environment as TwigEnvironment;

class AccountAction implements ActionInterface
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

        $breadcrumbs = [
            ['text' => 'Início', 'href' => '/' . $languageCode],
            ['text' => 'Minha Conta', 'href' => '/' . $languageCode . '/account']
        ];

        // Tradução e variáveis do template da conta
        $data = [
            'direction' => 'ltr',
            'lang' => $language ? $language->getCode() : 'pt-br',
            'title' => 'Minha Conta | AgSonhos',
            'description' => 'Gerencie sua conta e compras.',
            'breadcrumbs' => $breadcrumbs,

            // Textos de tradução
            'text_my_account' => 'Minha Conta',
            'text_edit' => 'Alterar informações da minha conta',
            'text_password' => 'Alterar minha senha',
            'text_payment_method' => 'Formas de pagamento salvas',
            'text_address' => 'Alterar meus endereços',
            'text_wishlist' => 'Lista de desejos',
            
            'text_my_orders' => 'Meus Pedidos',
            'text_order' => 'Histórico de pedidos',
            'text_subscription' => 'Assinaturas',
            'text_download' => 'Downloads',
            'text_reward' => 'Pontos de fidelidade',
            'text_return' => 'Solicitações de devolução',
            'text_transaction' => 'Transações',
            
            'text_my_affiliate' => 'Minha Conta de Afiliado',
            'text_affiliate_add' => 'Cadastre-se como afiliado',
            'text_affiliate_edit' => 'Alterar informações de afiliado',
            'text_tracking' => 'Gerador de links de afiliado',
            
            'text_my_newsletter' => 'Novidades por E-mail',
            'text_newsletter' => 'Inscrever ou desinscrever-se na newsletter',

            // Links das rotas
            'edit' => '/' . $languageCode . '/account/edit',
            'password' => '/' . $languageCode . '/account/password',
            'payment_method' => '/' . $languageCode . '/account/payment',
            'address' => '/' . $languageCode . '/account/address',
            'wishlist' => '/' . $languageCode . '/account/wishlist',
            'order' => '/' . $languageCode . '/account/orders',
            'subscription' => '/' . $languageCode . '/account/subscription',
            'download' => '/' . $languageCode . '/account/download',
            'reward' => '/' . $languageCode . '/account/reward',
            'return' => '/' . $languageCode . '/account/return',
            'transaction' => '/' . $languageCode . '/account/transaction',
            'affiliate' => '/' . $languageCode . '/account/affiliate',
            'tracking' => '/' . $languageCode . '/account/tracking',
            'newsletter' => '/' . $languageCode . '/account/newsletter',

            // Estrutura
            'column_left' => '',
            'column_right' => '',
            'content_top' => '',
            'content_bottom' => '',
            'reward' => false, // Ocultar se não implementado
            'affiliate' => false // Ocultar se não implementado
        ];

        // Renderiza o template Twig moderno correspondente
        $html = $this->twig->render('pages/users/accounts/account.twig', $data);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}

