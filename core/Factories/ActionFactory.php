<?php

namespace Alpha\Core\Factories;

use Alpha\Controller\Actions\Product\ShowProductAction;
use Alpha\Controller\Actions\Auth\LogoutAction;
use Alpha\Model\Repositories\Product\ProductRepository;
use Alpha\Model\Services\Auth\AuthService;
use Twig\Environment as TwigEnvironment;

class ActionFactory
{
    private $twig;
    private $productRepository;
    private $authService;

    // Você inicializa o Twig e os Repositórios aqui uma única vez
    public function __construct(TwigEnvironment $twig, ProductRepository $productRepository, AuthService $authService)
    {
        $this->twig = $twig;
        $this->productRepository = $productRepository;
        $this->authService = $authService;
    }

    /**
     * O método da fábrica que sabe como construir cada Action do sistema
     */
    public function create(string $className)
    {
        // Se a rota pediu para ver um produto, a fábrica injeta o Repositório e o Twig nela
        if ($className === ShowProductAction::class) {
            return new ShowProductAction($this->productRepository, $this->twig);
        }

        // Se a rota pediu logout, a fábrica injeta o serviço de autenticação
        if ($className === LogoutAction::class) {
            return new LogoutAction($this->authService);
        }

        // Fallback genérico caso a classe não precise de dependências complexas
        return new $className();
    }
}
