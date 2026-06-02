<?php

namespace Alpha\Auth\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Routing\RouteContext;
use Alpha\Model\Domain\Repositories\LanguageRepository;
use Alpha\Support\Registry;
use Twig\Environment as TwigEnvironment;

class LanguageMiddleware
{
    private LanguageRepository $languageRepository;
    private TwigEnvironment $twig;
    private Registry $registry;

    public function __construct(LanguageRepository $languageRepository, TwigEnvironment $twig, Registry $registry)
    {
        $this->languageRepository = $languageRepository;
        $this->twig = $twig;
        $this->registry = $registry;
    }

    public function __invoke(Request $request, Handler $handler): Response
    {
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();

        $langCode = 'pt-br';
        if ($route) {
            $langCode = $route->getArgument('lang', 'pt-br');
        }

        $language = $this->languageRepository->getByCode($langCode);
        if (!$language) {
            // Fallback para pt-br (ID 2)
            $language = $this->languageRepository->find(2);
        }

        $languageId = $language ? $language->getId() : 2;
        $langCode = $language ? $language->getCode() : 'pt-br';

        // Atualiza o tradutor no Registry
        $translator = $this->registry->get('language');
        if ($translator && method_exists($translator, 'setCode')) {
            $translator->setCode($langCode);
        }

        // Armazena atributos úteis na requisição para consumo pelos controladores
        $request = $request->withAttribute('language_id', $languageId)
                           ->withAttribute('language_code', $langCode);

        // Atualiza a global do Twig
        $isLogged = $this->isUserLogged($request);
        $this->twig->addGlobal('lang', $langCode);
        $this->twig->addGlobal('logged', $isLogged);

        // Resolve a contagem do carrinho para o usuário (se logado)
        $cartCount = 0;
        if ($isLogged) {
            $repositoryFactory = $this->registry->get('alpha_repository_factory');
            if ($repositoryFactory) {
                /** @var \Alpha\Model\Domain\Repositories\CartRepository $cartRepo */
                $cartRepo = $repositoryFactory->get(\Alpha\Model\Domain\Repositories\CartRepository::class);
                if ($cartRepo) {
                    $cartRepo->initializeContext();
                    $cartCount = $cartRepo->countProducts();
                }
            }
        }
        $this->twig->addGlobal('cart_count', $cartCount);

        // Carrega as traduções do cabeçalho globalmente para o Twig
        if ($translator && method_exists($translator, 'load')) {
            $headerTranslations = $translator->load('common/header');
            
            // Resolve a quantidade de itens na lista de desejos (wishlist) da sessão
            $wishlistCount = 0;
            $session = $this->registry->get('session');
            if ($session && isset($session->data['wishlist']) && is_array($session->data['wishlist'])) {
                $wishlistCount = count($session->data['wishlist']);
            }

            foreach ($headerTranslations as $key => $value) {
                if ($key === 'text_wishlist') {
                    $value = sprintf($value, $wishlistCount);
                }
                $this->twig->addGlobal($key, $value);
            }
        }

        $session = $this->registry->get('session');
        $shippingCep = '';
        if ($session && !empty($session->data['shipping_cep'])) {
            $shippingCep = (string)$session->data['shipping_cep'];
        }
        $this->twig->addGlobal('shipping_cep', $shippingCep);

        return $handler->handle($request);
    }

    /**
     * Verifica de forma otimizada se o cliente possui uma sessão ativa (Redis ou PHP local)
     */
    private function isUserLogged(Request $request): bool
    {
        $cookies = $request->getCookieParams();
        $sessionId = $cookies['session_id'] ?? '';

        if (empty($sessionId)) {
            return false;
        }

        try {
            $redis = new \Predis\Client([
                'host' => $_ENV['REDIS_HOST'] ?? '127.0.0.1',
                'port' => $_ENV['REDIS_PORT'] ?? 6379,
                'password' => ($_ENV['REDIS_PASSWORD'] ?? '') ?: null,
                'timeout' => 0.5
            ]);
            $redis->connect();
            $sessionData = $redis->get("sessao:" . $sessionId);
            if ($sessionData) {
                return true;
            }
        } catch (\Exception $e) {
            // Fallback para sessão local PHP
            if (session_status() === PHP_SESSION_NONE) {
                session_name('session_id');
                session_id($sessionId);
                @session_start();
            }
            $expire = $_SESSION['expire'] ?? $_SESSION['logged_user_expire'] ?? 0;
            if ($expire > time() && !empty($_SESSION['logged_user'])) {
                return true;
            }
        }

        return false;
    }
}


