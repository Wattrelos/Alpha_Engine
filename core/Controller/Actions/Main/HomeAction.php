<?php

namespace Alpha\Controller\Actions\Main;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Twig\Environment as TwigEnvironment;
use Alpha\Controller\Actions\ActionInterface;
use Alpha\Services\Menu\CategoryMenuService;

/**
 * HomeAction
 *
 * Action responsável pela rota GET '/' (página inicial pública).
 * Carrega a árvore de categorias via CategoryMenuService e renderiza o template home.html.twig.
 */
class HomeAction implements ActionInterface
{
    private TwigEnvironment    $twig;
    private CategoryMenuService $categoryMenu;

    public function __construct(TwigEnvironment $twig, CategoryMenuService $categoryMenu)
    {
        $this->twig         = $twig;
        $this->categoryMenu = $categoryMenu;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        // Carrega a árvore de categorias via 1 query (Batch Load — sem N+1)
        $categories = $this->categoryMenu->getCategoryTree();

        $html = $this->twig->render('home.html.twig', [
            'categories' => $categories,
            // Expansão futura:
            // 'featured_products' => $this->productRepository->getFeatured(),
        ]);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}
