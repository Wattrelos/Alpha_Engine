<?php

declare(strict_types=1);

namespace Alpha\Controller\Actions\Quotation\Customer;

use Alpha\Controller\Actions\ActionInterface;
use Alpha\Model\Domain\Entities\Quotation\ProjectRfq;
use Alpha\Model\Domain\Entities\Quotation\ProjectBoq;
use Alpha\Model\Domain\Entities\Quotation\ProjectBoqItem;
use Alpha\Model\Domain\Repositories\ProjectRfqRepository;
use Alpha\Model\Domain\Repositories\ProjectBoqRepository;
use Alpha\Model\Domain\Repositories\ProductRepository;
use Alpha\Services\Quotation\GeoMatchingService;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Twig\Environment as TwigEnvironment;
use Slim\Routing\RouteContext;

/**
 * CreateProjectRfqAction - Permite ao cliente cadastrar e publicar um novo projeto/obra (RF033/RF036).
 */
class CreateProjectRfqAction implements ActionInterface
{
    public function __construct(
        private TwigEnvironment $twig,
        private ProjectRfqRepository $rfqRepository,
        private ProjectBoqRepository $boqRepository,
        private ProductRepository $productRepository,
        private GeoMatchingService $geoService,
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

        $routeContext = RouteContext::fromRequest($request);
        $routeParser = $routeContext->getRouteParser();
        $customerId = (int) $customer->getId();

        $error = null;
        $queryParams = $request->getQueryParams();
        $preProductId = (int)($queryParams['pre_product_id'] ?? 0);
        $preQty = (float)($queryParams['qty'] ?? 1.0);

        if ($request->getMethod() === 'POST') {
            $data = $request->getParsedBody() ?? [];

            $title = trim($data['title'] ?? '');
            $category = trim($data['category'] ?? 'geral');
            $description = trim($data['description'] ?? '');
            $cep = trim($data['address_cep'] ?? '');
            $street = trim($data['address_street'] ?? '');
            $number = trim($data['address_number'] ?? '');
            $neighborhood = trim($data['address_neighborhood'] ?? '');
            $city = trim($data['address_city'] ?? '');
            $state = strtoupper(trim($data['address_state'] ?? 'SP'));
            $budget = (float)($data['budget_expectation'] ?? 0.0);
            $deadline = (int)($data['desired_deadline_days'] ?? 30);
            $formPreProductId = (int)($data['pre_product_id'] ?? $preProductId);
            $formPreQty = (float)($data['pre_qty'] ?? $preQty);

            if (empty($title) || empty($description) || empty($cep) || empty($city)) {
                $error = 'Por favor, preencha todos os campos obrigatórios (Título, Descrição, CEP e Cidade).';
            } else {
                $coords = $this->geoService->resolveApproximateCoordinates($cep, $city, $state);

                $rfq = new ProjectRfq();
                $rfq->setCustomerId($customerId)
                    ->setTitle($title)
                    ->setCategory($category)
                    ->setDescription($description)
                    ->setAddressCep($cep)
                    ->setAddressStreet($street)
                    ->setAddressNumber($number)
                    ->setAddressNeighborhood($neighborhood)
                    ->setAddressCity($city)
                    ->setAddressState($state)
                    ->setBudgetExpectation($budget)
                    ->setDesiredDeadlineDays(max(1, $deadline))
                    ->setStatus('open');

                if ($coords) {
                    $rfq->setLatitude($coords['lat']);
                    $rfq->setLongitude($coords['lng']);
                }

                $newId = $this->rfqRepository->save($rfq);

                if ($newId) {
                    // Se foi iniciado a partir de um produto pré-selecionado ("Add to Quote")
                    if ($formPreProductId > 0) {
                        $product = $this->productRepository->find($formPreProductId);
                        if ($product) {
                            $boq = new ProjectBoq();
                            $boq->setRfqId((int)$newId)
                                ->setTitle('Levantamento Inicial - ' . $title)
                                ->setStatus('draft');

                            $boqId = $this->boqRepository->save($boq);

                            $desc = $this->productRepository->getProductDescription($formPreProductId, 2);
                            $productName = $desc['name'] ?? $product->getModel() ?: 'Produto ' . $formPreProductId;

                            $item = new ProjectBoqItem();
                            $item->setBoqId((int)$boqId)
                                ->setProductId($formPreProductId)
                                ->setItemName($productName)
                                ->setUnit('un')
                                ->setQuantity(max(1.0, $formPreQty))
                                ->setUnitPrice($product->getPrice())
                                ->setTotalPrice(round(max(1.0, $formPreQty) * $product->getPrice(), 2))
                                ->setNotes('Adicionado na criação da obra');

                            $this->boqRepository->saveItem($item);
                        }
                    }

                    return $response
                        ->withHeader('Location', '/' . $lang . '/account/projetos')
                        ->withStatus(302);
                } else {
                    $error = 'Ocorreu um erro ao salvar o projeto. Tente novamente.';
                }
            }
        }

        $preProduct = null;
        if ($preProductId > 0) {
            $prod = $this->productRepository->find($preProductId);
            if ($prod) {
                $desc = $this->productRepository->getProductDescription($preProductId, 2);
                $preProduct = [
                    'id' => $preProductId,
                    'name' => $desc['name'] ?? $prod->getModel(),
                    'qty' => $preQty,
                    'price' => $prod->getPrice()
                ];
            }
        }

        $breadcrumbs = [
            ['text' => 'Início', 'href' => $routeParser->urlFor('home', ['lang' => $lang])],
            ['text' => 'Minha Conta', 'href' => $routeParser->urlFor('account.index', ['lang' => $lang])],
            ['text' => 'Novo Pedido de Orçamento (RFQ)', 'href' => '/' . $lang . '/projetos/novo']
        ];

        $html = $this->twig->render('pages/quotation/project-rfq-form.twig', [
            'breadcrumbs' => $breadcrumbs,
            'error' => $error,
            'pre_product' => $preProduct,
            'lang' => $lang
        ]);

        $response->getBody()->write($html);
        return $response;
    }
}
