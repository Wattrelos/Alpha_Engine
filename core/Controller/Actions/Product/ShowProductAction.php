<?php

namespace Alpha\Controller\Actions\Product;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Model\Domain\Repositories\ProductRepository; // Ajuste para o seu namespace real
use Twig\Environment as TwigEnvironment;

class ShowProductAction
{
    private $productRepository;
    private $twig;

    // O seu container de dependências ou você mesmo injeta o repositório e o Twig aqui
    public function __construct(ProductRepository $productRepository, TwigEnvironment $twig)
    {
        $this->productRepository = $productRepository;
        $this->twig = $twig;
    }

    /**
     * O método __invoke transforma a classe em um "comando" executável.
     * O Slim passa os parâmetros da URL (como o {slug}) dentro do array $args.
     */
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        // 1. Captura o slug amigável direto da URL (Foco em SEO)
        $slug = $args['slug'] ?? '';

        // 2. Busca o produto no banco de dados usando o repositório
        $product = $this->productRepository->findBySlug($slug);

        // 3. Se o produto não existir, renderiza uma página 404 limpa (Essencial para SEO)
        if (!$product) {
            $html404 = $this->twig->render('errors/404.twig', [
                'meta_title' => 'Produto Não Encontrado | AgSonhos'
            ]);
            $response->getBody()->write($html404);
            return $response->withStatus(404);
        }

        // 4. Prepara os dados específicos de SEO para as tags do cabeçalho HTML
        $seoData = [
            'title' => $product->name . ' | AgSonhos',
            'description' => $product->short_description ?? 'Confira nossos produtos artesanais.',
            'image' => $product->main_image_url ?? 'https://agsonhos.com',
            'canonical' => 'https://agsonhos.com' . $product->slug
        ];

        // 5. Renderiza o template do Twig enviando o produto e os dados de SEO
        $html = $this->twig->render('Web/Product/Show.twig', [
            'product' => $product,
            'seo' => $seoData
        ]);

        // 6. Devolve a página web montada no servidor (Server-Side Rendering)
        $response->getBody()->write($html);
        return $response;
    }
}
