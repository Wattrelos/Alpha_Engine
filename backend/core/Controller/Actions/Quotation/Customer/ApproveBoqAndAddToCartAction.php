<?php

declare(strict_types=1);

namespace Alpha\Controller\Actions\Quotation\Customer;

use Alpha\Controller\Actions\ActionInterface;
use Alpha\Model\Domain\Repositories\ProjectRfqRepository;
use Alpha\Model\Domain\Repositories\ProjectBoqRepository;
use Alpha\Services\Quotation\BoqToCartConverterService;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Twig\Environment as TwigEnvironment;
use Slim\Routing\RouteContext;

/**
 * ApproveBoqAndAddToCartAction - Exibe o BoQ com análise de descontos por volume (RN015) e permite conversão para o carrinho / cotação (RF036/RF037).
 */
class ApproveBoqAndAddToCartAction implements ActionInterface
{
    public function __construct(
        private TwigEnvironment $twig,
        private ProjectRfqRepository $rfqRepository,
        private ProjectBoqRepository $boqRepository,
        private BoqToCartConverterService $converterService,
        private ContainerInterface $container
    ) {}

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $customer = $this->container->get('customer');
        $lang = $request->getAttribute('lang', 'pt-br');

        if (!$customer || !$customer->isLogged()) {
            return $response
                ->withHeader('Location', '/' . $lang . '/login')
                ->withStatus(302);
        }

        $rfqId = (int)($args['rfq_id'] ?? 0);
        $rfq = $this->rfqRepository->find($rfqId);

        if (!$rfq || $rfq->getCustomerId() !== (int)$customer->getId()) {
            return $response
                ->withHeader('Location', '/' . $lang . '/account/projetos')
                ->withStatus(302);
        }

        $boq = $this->boqRepository->findByRfqId($rfqId);
        if (!$boq) {
            return $response
                ->withHeader('Location', '/' . $lang . '/account/projetos')
                ->withStatus(302);
        }

        $routeContext = RouteContext::fromRequest($request);
        $routeParser = $routeContext->getRouteParser();

        // Se for requisição POST de conversão em carrinho / cotação ("Add to Quote")
        if ($request->getMethod() === 'POST') {
            $customerGroupId = $customer->getCustomerGroupId() ?: 1;
            $result = $this->converterService->convertBoqToCart($boq, $customerGroupId);

            if ($result['success']) {
                return $response
                    ->withHeader('Location', '/' . $lang . '/carrinho')
                    ->withStatus(302);
            }
        }

        $customerGroupId = $customer->getCustomerGroupId() ?: 1;
        $volumeDiscounts = $this->converterService->calculateVolumeDiscounts($boq, $customerGroupId);

        $items = $this->boqRepository->findItemsByBoqId($boq->getId());
        $itemsData = array_map(fn($item) => [
            'id' => $item->getId(),
            'name' => $item->getItemName(),
            'unit' => $item->getUnit(),
            'quantity' => $item->getQuantity(),
            'unit_price' => $item->getUnitPrice(),
            'total_price' => $item->getTotalPrice(),
            'notes' => $item->getNotes(),
            'product_id' => $item->getProductId()
        ], $items);

        $breadcrumbs = [
            ['text' => 'Início', 'href' => $routeParser->urlFor('home', ['lang' => $lang])],
            ['text' => 'Minha Conta', 'href' => $routeParser->urlFor('account.index', ['lang' => $lang])],
            ['text' => 'Meus Projetos', 'href' => '/' . $lang . '/account/projetos'],
            ['text' => 'Lista de Materiais da Obra (BoQ)', 'href' => '/' . $lang . '/account/projetos/' . $rfqId . '/boq']
        ];

        $html = $this->twig->render('pages/quotation/customer-boq-view.twig', [
            'breadcrumbs' => $breadcrumbs,
            'rfq' => [
                'id' => $rfq->getId(),
                'title' => $rfq->getTitle(),
                'category' => $rfq->getCategory(),
                'city' => $rfq->getAddressCity(),
                'state' => $rfq->getAddressState()
            ],
            'boq' => [
                'id' => $boq->getId(),
                'title' => $boq->getTitle(),
                'notes' => $boq->getNotes(),
                'total' => $boq->getTotalEstimatedAmount(),
                'status' => $boq->getStatus(),
                'items' => $itemsData
            ],
            'volume_discounts' => $volumeDiscounts,
            'lang' => $lang
        ]);

        $response->getBody()->write($html);
        return $response;
    }
}
