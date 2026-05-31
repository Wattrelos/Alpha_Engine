<?php

namespace Alpha\Controller\Actions\Cart;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Twig\Environment as TwigEnvironment;
use Alpha\Controller\Actions\ActionInterface;
use Alpha\Model\Domain\Repositories\CartRepository;

class ShowCartAction implements ActionInterface
{
    private TwigEnvironment $twig;
    private CartRepository $cartRepository;

    public function __construct(TwigEnvironment $twig, CartRepository $cartRepository)
    {
        $this->twig = $twig;
        $this->cartRepository = $cartRepository;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        // Inicializa o contexto do carrinho (mescla sessão com login)
        $this->cartRepository->initializeContext();

        $cartPageData = $this->cartRepository->getCartPageData()->getData();
        $cartListData = $this->cartRepository->getCartListDisplayData()->getData();

        $viewData = array_merge($cartPageData, $cartListData);

        $title = $viewData['heading_title'] ?? 'Carrinho de Compras';

        $seoData = [
            'title'       => $title . ' | AgSonhos',
            'description' => 'Visualize e edite os itens em seu carrinho de compras.',
            'keywords'    => 'carrinho, compras, agsonhos'
        ];

        $html = $this->twig->render('pages/cart/cart.twig', array_merge($viewData, [
            'seo'         => $seoData,
            'title'       => $seoData['title'],
            'description' => $seoData['description'],
            'keywords'    => $seoData['keywords']
        ]));

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}
