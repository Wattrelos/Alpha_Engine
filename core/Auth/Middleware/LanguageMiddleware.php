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
        $this->twig->addGlobal('lang', $langCode);

        return $handler->handle($request);
    }
}

