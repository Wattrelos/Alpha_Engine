<?php

namespace Alpha\Auth\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Routing\RouteContext;
use Alpha\Model\Domain\Repositories\LanguageRepository;
use Psr\Container\ContainerInterface;
use Twig\Environment as TwigEnvironment;

class LanguageMiddleware
{
    private LanguageRepository $languageRepository;
    private TwigEnvironment $twig;
    private ContainerInterface $container;

    public function __construct(LanguageRepository $languageRepository, TwigEnvironment $twig, ContainerInterface $container)
    {
        $this->languageRepository = $languageRepository;
        $this->twig = $twig;
        $this->container = $container;
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
        $translator = $this->container->get('language');
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
            /** @var \Alpha\Model\Domain\Repositories\CartRepository|null $cartRepo */
            $cartRepo = $this->container->has(\Alpha\Model\Domain\Repositories\CartRepository::class) ? $this->container->get(\Alpha\Model\Domain\Repositories\CartRepository::class) : null;
            if ($cartRepo) {
                $cartRepo->initializeContext();
                $cartCount = $cartRepo->countProducts();
            }
        }
        $this->twig->addGlobal('cart_count', $cartCount);

        // Carrega as traduções do cabeçalho globalmente para o Twig
        if ($translator && method_exists($translator, 'load')) {
            $wishlistCount = 0;
            $session = $this->container->get('session');
            if ($session && isset($session->data['wishlist']) && is_array($session->data['wishlist'])) {
                $wishlistCount = count($session->data['wishlist']);
            }

            // Novo sistema de tradução: carrega o namespace 'common'
            $translator->load('common');
            if (method_exists($translator, 'getNestedData')) {
                $commonData = $translator->getNestedData('common');
                if (!empty($commonData)) {
                    if (isset($commonData['header']['wishlist'])) {
                        $commonData['header']['wishlist'] = sprintf($commonData['header']['wishlist'], $wishlistCount);
                    }
                    $this->twig->addGlobal('Common', $commonData);
                }
            }

            // Retrocompatibilidade: carrega o cabeçalho legado
            $headerTranslations = $translator->load('common/header');
            foreach ($headerTranslations as $key => $value) {
                if ($key === 'text_wishlist') {
                    $value = sprintf($value, $wishlistCount);
                }
                $this->twig->addGlobal($key, $value);
            }
        }

        $session = $this->container->get('session');
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
