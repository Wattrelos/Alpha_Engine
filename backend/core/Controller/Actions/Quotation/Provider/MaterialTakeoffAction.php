<?php

declare(strict_types=1);

namespace Alpha\Controller\Actions\Quotation\Provider;

use Alpha\Controller\Actions\ActionInterface;
use Alpha\Model\Domain\Entities\Quotation\ProjectBoq;
use Alpha\Model\Domain\Entities\Quotation\ProjectBoqItem;
use Alpha\Model\Domain\Repositories\ProjectRfqRepository;
use Alpha\Model\Domain\Repositories\ProjectBoqRepository;
use Alpha\Model\Domain\Repositories\ServiceProviderProfileRepository;
use Alpha\Services\Quotation\BoqSpreadsheetImportService;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Twig\Environment as TwigEnvironment;
use Slim\Routing\RouteContext;

/**
 * MaterialTakeoffAction - Ferramenta de Levantamento e Estimativa de Materiais (Takeoff Tool / BoQ - RF036).
 * Suporta inserção individual e importação em lote de planilhas CSV/TSV.
 */
class MaterialTakeoffAction implements ActionInterface
{
    public function __construct(
        private TwigEnvironment $twig,
        private ProjectRfqRepository $rfqRepository,
        private ProjectBoqRepository $boqRepository,
        private ServiceProviderProfileRepository $providerRepo,
        private BoqSpreadsheetImportService $importService,
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

        $customerId = (int)$customer->getId();
        $provider = $this->providerRepo->findByCustomerId($customerId);

        if (!$provider) {
            return $response
                ->withHeader('Location', '/' . $lang . '/prestador/oportunidades')
                ->withStatus(302);
        }

        $rfqId = (int)($args['rfq_id'] ?? 0);
        $rfq = $this->rfqRepository->find($rfqId);

        if (!$rfq) {
            return $response
                ->withHeader('Location', '/' . $lang . '/prestador/oportunidades')
                ->withStatus(302);
        }

        // FE01: Valida se o projeto foi atribuído a outro prestador
        if ($rfq->getSelectedProviderId() !== null && $rfq->getSelectedProviderId() !== $provider->getId()) {
            $response->getBody()->write('Acesso não autorizado. A ferramenta de levantamento de materiais só é liberada para o profissional contratado pelo cliente.');
            return $response->withStatus(403);
        }

        // Obtém ou inicializa o BoQ da obra
        $boq = $this->boqRepository->findByRfqId($rfqId);
        if (!$boq) {
            $boq = new ProjectBoq();
            $boq->setRfqId($rfqId)
                ->setProviderId($provider->getId())
                ->setTitle('Levantamento de Materiais - ' . $rfq->getTitle())
                ->setStatus('draft');

            $newBoqId = $this->boqRepository->save($boq);
            $boq->setId((int)$newBoqId);
        }

        // Manipulação AJAX / POST de itens do BoQ
        if ($request->getMethod() === 'POST') {
            $data = $request->getParsedBody() ?? [];
            $action = $data['action'] ?? '';

            // 1. Importação de Planilha
            if ($action === 'import_spreadsheet') {
                $uploadedFiles = $request->getUploadedFiles();
                $uploadedFile = $uploadedFiles['spreadsheet_file'] ?? null;

                if ($uploadedFile && $uploadedFile->getError() === UPLOAD_ERR_OK) {
                    $tmpPath = $uploadedFile->getStream()->getMetadata('uri');
                    if (!$tmpPath || !file_exists($tmpPath)) {
                        $tmpPath = tempnam(sys_get_temp_dir(), 'boq_import_');
                        $uploadedFile->moveTo($tmpPath);
                    }

                    $importResult = $this->importService->importCsv($boq->getId(), $tmpPath);

                    $response->getBody()->write(json_encode($importResult));
                    return $response->withHeader('Content-Type', 'application/json');
                } else {
                    $response->getBody()->write(json_encode([
                        'success' => false,
                        'errors' => ['Nenhum arquivo válido foi enviado para importação.']
                    ]));
                    return $response->withHeader('Content-Type', 'application/json');
                }
            }

            // 2. Adicionar Item Manual
            if ($action === 'add_item') {
                $itemName = trim($data['item_name'] ?? '');
                $unit = trim($data['unit'] ?? 'un');
                $quantity = (float)($data['quantity'] ?? 1.0);
                $unitPrice = (float)($data['unit_price'] ?? 0.0);
                $productId = !empty($data['product_id']) ? (int)$data['product_id'] : null;
                $notes = trim($data['notes'] ?? '');

                if (!empty($itemName) && $quantity > 0) {
                    $item = new ProjectBoqItem();
                    $item->setBoqId($boq->getId())
                        ->setProductId($productId)
                        ->setItemName($itemName)
                        ->setUnit($unit)
                        ->setQuantity($quantity)
                        ->setUnitPrice($unitPrice)
                        ->setTotalPrice(round($quantity * $unitPrice, 2))
                        ->setNotes($notes);

                    $itemId = $this->boqRepository->saveItem($item);
                    $total = $this->boqRepository->recalculateTotal($boq->getId());

                    $response->getBody()->write(json_encode([
                        'success' => true,
                        'item_id' => $itemId,
                        'new_total' => $total
                    ]));
                    return $response->withHeader('Content-Type', 'application/json');
                }
            } elseif ($action === 'remove_item') {
                $itemId = (int)($data['item_id'] ?? 0);
                $deleted = $this->boqRepository->deleteItem($itemId);
                $total = $this->boqRepository->recalculateTotal($boq->getId());

                $response->getBody()->write(json_encode([
                    'success' => $deleted,
                    'new_total' => $total
                ]));
                return $response->withHeader('Content-Type', 'application/json');
            } elseif ($action === 'finalize_boq') {
                $boq->setStatus('submitted');
                $this->boqRepository->save($boq);

                // Atualiza o RFQ para 'boq_ready' para que o cliente possa aprovar (UC_CLI_029)
                $rfq->setStatus('boq_ready');
                $this->rfqRepository->save($rfq);

                $response->getBody()->write(json_encode([
                    'success' => true,
                    'message' => 'Lista de materiais enviada com sucesso para o cliente!'
                ]));
                return $response->withHeader('Content-Type', 'application/json');
            }
        }

        $routeContext = RouteContext::fromRequest($request);
        $routeParser = $routeContext->getRouteParser();

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
            ['text' => 'Painel do Prestador', 'href' => '/' . $lang . '/prestador/oportunidades'],
            ['text' => 'Levantamento de Materiais (Takeoff Tool)', 'href' => '/' . $lang . '/prestador/projetos/' . $rfqId . '/takeoff']
        ];

        $html = $this->twig->render('pages/quotation/takeoff-tool.twig', [
            'breadcrumbs' => $breadcrumbs,
            'rfq' => [
                'id' => $rfq->getId(),
                'title' => $rfq->getTitle(),
                'category' => $rfq->getCategory(),
                'description' => $rfq->getDescription(),
                'city' => $rfq->getAddressCity(),
                'state' => $rfq->getAddressState()
            ],
            'boq' => [
                'id' => $boq->getId(),
                'title' => $boq->getTitle(),
                'total' => $boq->getTotalEstimatedAmount(),
                'status' => $boq->getStatus(),
                'items' => $itemsData
            ],
            'lang' => $lang
        ]);

        $response->getBody()->write($html);
        return $response;
    }
}
