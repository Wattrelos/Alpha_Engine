<?php
namespace Opencart\Catalog\Controller\Checkout;

use Alpha\Controller\BaseController;
use Alpha\Model\Domain\Repositories\CartRepository;
use Alpha\Model\Domain\Repositories\OrderRepository;

/**
 * Class Confirm
 * 
 * Refatorado para Alpha Engine: Orquestra a exibição final e a confirmação transacional do pedido.
 */
class Confirm extends BaseController {
	/**
	 * Exibe o resumo final do pedido (Carrinho, Endereços, Totais).
	 */
	public function index(): string {
		$data = [];
		$this->loadLanguageData('checkout/confirm', $data);

		$cartRepository = $this->getRepository(CartRepository::class);

		$data['products'] = $cartRepository->getCartProducts(
			(int)$this->customer->getId(),
			$this->session->getId(),
			(int)$this->customer->getGroupId()
		);

		$data['vouchers'] = [];
		foreach ($this->session->data['vouchers'] ?? [] as $voucher) {
			$data['vouchers'][] = [
				'description' => $voucher['description'],
				'amount'      => $this->currency->format($voucher['amount'], $this->session->data['currency'])
			];
		}

		$totals = [];
		$taxes = $cartRepository->getTaxes();
		$total = 0;

		$cartRepository->getTotals($totals, $taxes, $total);

		$data['totals'] = [];
		foreach ($totals as $total_row) {
			$data['totals'][] = [
				'title' => $total_row['title'],
				'text'  => $this->currency->format($total_row['value'], $this->session->data['currency'])
			];
		}

		// Alpha Engine: O método render já injeta Header/Footer se necessário, 
		// mas aqui retornamos apenas o HTML da tabela para o Ajax do checkout.
		return $this->viewRenderer->render('checkout/confirm', $data);
	}

	/**
	 * Executa a confirmação final do pedido (Geralmente chamado pelo botão "Confirmar" ou Callback de Pagamento).
	 */
	public function confirm(): void {
		$json = [];

		try {
			$orderRepository = $this->getRepository(OrderRepository::class);
			$cartRepository = $this->getRepository(CartRepository::class);

			// 1. Criação Inicial do Pedido (Status Pendente)
			if (!isset($this->session->data['order_id'])) {
				$this->session->data['order_id'] = $orderRepository->createFromSession();
			}

			$order_id = (int)$this->session->data['order_id'];
			$order_status_id = (int)$this->config->get('config_order_status_id'); // Status inicial (ex: Pendente)

			// 2. Alpha Engine: Confirmação Transacional (Histórico + Estoque + Notificação)
			$orderRepository->confirm($order_id, $order_status_id, 'Pedido confirmado via checkout Alpha Engine', true);

			// 3. Limpeza de estado após sucesso
			$cartRepository->clear();
			unset($this->session->data['shipping_method'], $this->session->data['shipping_methods']);
			unset($this->session->data['payment_method'], $this->session->data['payment_methods']);
			unset($this->session->data['guest'], $this->session->data['comment'], $this->session->data['order_id']);
			unset($this->session->data['coupon'], $this->session->data['reward'], $this->session->data['voucher'], $this->session->data['vouchers']);

			$json['redirect'] = $this->url->link('checkout/success', 'language=' . $this->config->get('config_language'));

		} catch (\Exception $e) {
			$json['error'] = $e->getMessage();
			
			// Log de erro Alpha Engine para auditoria técnica
			$this->log->write('Alpha Engine Error (Checkout Confirm): ' . $e->getMessage());
		}

		$this->jsonResponse($json);
	}
}