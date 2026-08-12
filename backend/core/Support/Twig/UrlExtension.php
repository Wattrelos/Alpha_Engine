<?php

namespace Alpha\Support\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Alpha\Model\Domain\Repositories\SeoUrlRepository;

class UrlExtension extends AbstractExtension
{
    private SeoUrlRepository $seoRepository;

    public function __construct(SeoUrlRepository $seoRepository)
    {
        $this->seoRepository = $seoRepository;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('url', [$this, 'generateUrl']),
        ];
    }

    public function generateUrl(string $route, array $params = [], ?string $lang = 'pt-br'): string
    {
        if (empty($lang)) {
            $lang = 'pt-br';
        }
        $storeId = 0;
        $languageId = ($lang === 'en') ? 1 : 2;

        switch ($route) {
            case 'home':
                return "/{$lang}";
            case 'login':
                return "/{$lang}/login";
            case 'cadastro':
                return "/{$lang}/cadastro";
            case 'logout':
                return "/{$lang}/logout";
            case 'carrinho':
                return "/{$lang}/carrinho";
            case 'checkout':
                return "/{$lang}/checkout";
            case 'busca':
                $searchQuery = isset($params['search']) ? '?busca=' . urlencode($params['search']) : '';
                return "/{$lang}/busca{$searchQuery}";
                
            case 'product':
                $id = $params['id'] ?? $params['product_id'] ?? 0;
                $keyword = $this->seoRepository->getKeywordByQuery('product_id', (string)$id, $storeId, $languageId);
                return $keyword ? "/{$lang}/produto/{$keyword}" : "/{$lang}/produto/{$id}";
                
            case 'category':
                $id = $params['id'] ?? $params['category_id'] ?? 0;
                $keyword = $this->seoRepository->getKeywordByQuery('category_id', (string)$id, $storeId, $languageId);
                return $keyword ? "/{$lang}/categoria/{$keyword}" : "/{$lang}/categoria/{$id}";
                
            case 'information':
                $id = $params['id'] ?? $params['information_id'] ?? 0;
                $keyword = $this->seoRepository->getKeywordByQuery('information_id', (string)$id, $storeId, $languageId);
                return $keyword ? "/{$lang}/pagina/{$keyword}" : "/{$lang}/pagina/{$id}";
        }

        return "/{$lang}/" . ltrim($route, '/');
    }
}
