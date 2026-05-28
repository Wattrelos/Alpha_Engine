<?php

namespace Core\Actions\Product;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Model\Domain\Repositories\ProductRepository; // Seu repositório real
use Twig\Environment as TwigEnvironment;
use Alpha\Controller\Actions\ActionInterface;

class ListProductsAction implements ActionInterface
{
    private $productRepository;
    private $twig;

    public function __construct(ProductRepository $productRepository, TwigEnvironment $twig)
    {
        $this->productRepository = $productRepository;
        $this->twig = $twig;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        // 1. Captura os filtros que vieram na URL (Ex: ?categoria=camas&pagina=2)
        $queryParams = $request->getQueryParams();

        $filterData = [
            'category_slug' => $queryParams['categoria'] ?? null,
            'page'          => (int)($queryParams['pagina'] ?? 1),
            'limit'         => 12, // Limite de produtos por página para performance
            'search'        => $queryParams['busca'] ?? null
        ];

        // 2. Executa o seu método inteligente (ele já vai checar se o usuário está logado e aplicar as tabelas de preço corretas)
        $products = $this->productRepository->getProducts($filterData);

        // 3. Prepara os dados de SEO para a página de catálogo
        $seoData = [
            'title'       => 'Catálogo de Produtos Artesanais | AgSonhos',
            'description' => 'Confira nossa linha completa de produtos exclusivos para o seu sonho.',
            'canonical'   => 'https://agsonhos.com'
        ];

        // 4. Renderiza o template do Twig respeitando o padrão PascalCase
        $html = $this->twig->render('Web/Product/List.twig', [
            'products' => $products,
            'seo'      => $seoData,
            'filters'  => $filterData
        ]);

        $response->getBody()->write($html);
        return $response;
    }
}
