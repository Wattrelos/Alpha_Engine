<?php

declare(strict_types=1);

namespace Alpha\Controller\Actions\Quotation\Customer;

use Alpha\Controller\Actions\ActionInterface;
use Alpha\Model\Domain\Entities\Quotation\ProjectBoq;
use Alpha\Model\Domain\Entities\Quotation\ProjectBoqItem;
use Alpha\Model\Domain\Entities\Quotation\ProjectRfq;
use Alpha\Model\Domain\Repositories\ProjectRfqRepository;
use Alpha\Model\Domain\Repositories\ProjectBoqRepository;
use Alpha\Model\Domain\Repositories\ProductRepository;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * AddProductToQuoteAction - Adiciona um produto do catálogo diretamente ao orçamento / BoQ de um projeto do cliente (RF036/RF037).
 */
class AddProductToQuoteAction implements ActionInterface
{
    public function __construct(
        private ProjectRfqRepository $rfqRepository,
        private ProjectBoqRepository $boqRepository,
        private ProductRepository $productRepository,
        private ContainerInterface $container
    ) {}

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $customer = $this->container->get('customer');
        $lang = $request->getAttribute('lang') ?: 'pt-br';

        if (!$customer || !$customer->isLogged()) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'require_login' => true,
                'login_url' => '/' . $lang . '/login',
                'message' => 'Faça login para adicionar itens ao seu orçamento.'
            ]));
            return $response->withHeader('Content-Type', 'application/json');
        }

        $data = $request->getParsedBody() ?? [];
        $productId = (int)($data['product_id'] ?? 0);
        $quantity = (float)($data['quantity'] ?? 1.0);
        $projectId = (int)($data['project_id'] ?? 0);
        $notes = trim($data['notes'] ?? '');

        if ($productId <= 0) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Produto inválido.'
            ]));
            return $response->withHeader('Content-Type', 'application/json');
        }

        $product = $this->productRepository->find($productId);
        if (!$product) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Produto não encontrado no catálogo.'
            ]));
            return $response->withHeader('Content-Type', 'application/json');
        }

        $customerId = (int)$customer->getId();
        $languageId = (int)($request->getAttribute('language_id') ?: 2);
        $desc = $this->productRepository->getProductDescription($productId, $languageId);
        $productName = $desc['name'] ?? $product->getModel() ?: 'Produto ' . $productId;
        $unitPrice = $product->getPrice();

        // Se for para criar novo projeto com esse produto
        if ($projectId === 0) {
            $response->getBody()->write(json_encode([
                'success' => true,
                'redirect_new' => '/' . $lang . '/projetos/novo?pre_product_id=' . $productId . '&qty=' . $quantity
            ]));
            return $response->withHeader('Content-Type', 'application/json');
        }

        // Se for para adicionar a um projeto existente
        $rfq = $this->rfqRepository->find($projectId);
        if (!$rfq || $rfq->getCustomerId() !== $customerId) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Projeto não encontrado ou você não tem permissão.'
            ]));
            return $response->withHeader('Content-Type', 'application/json');
        }

        $boq = $this->boqRepository->findByRfqId($projectId);
        if (!$boq) {
            $boq = new ProjectBoq();
            $boq->setRfqId($projectId)
                ->setProviderId($rfq->getSelectedProviderId() ?: 0)
                ->setTitle('Levantamento de Materiais - ' . $rfq->getTitle())
                ->setStatus('draft');

            $newBoqId = $this->boqRepository->save($boq);
            $boq->setId((int)$newBoqId);
        }

        $item = new ProjectBoqItem();
        $item->setBoqId($boq->getId())
            ->setProductId($productId)
            ->setItemName($productName)
            ->setUnit('un')
            ->setQuantity(max(1.0, $quantity))
            ->setUnitPrice($unitPrice)
            ->setTotalPrice(round(max(1.0, $quantity) * $unitPrice, 2))
            ->setNotes($notes ?: 'Adicionado diretamente da vitrine');

        $itemId = $this->boqRepository->saveItem($item);
        $newTotal = $this->boqRepository->recalculateTotal($boq->getId());

        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => "Produto '{$productName}' adicionado com sucesso ao projeto '{$rfq->getTitle()}'!",
            'project_id' => $projectId,
            'boq_id' => $boq->getId(),
            'item_id' => $itemId,
            'new_boq_total' => $newTotal
        ]));

        return $response->withHeader('Content-Type', 'application/json');
    }
}
