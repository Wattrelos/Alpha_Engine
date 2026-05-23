<?php
namespace Opencart\Catalog\Controller\Common;

use Alpha\Controller\BaseController;
use Alpha\Model\Domain\Repositories\CartRepository;

/**
 * Class Cart
 *
 * Can be called from $this->load->controller('common/cart');
 *
 * @package Opencart\Catalog\Controller\Common
 */
class Cart extends BaseController {
	/**
	 * Index
	 *
	 * @return string
	 */
	public function index(): string {
		$data = [];
		
		// Alpha Engine: Carrega o dicionário PRIMEIRO para obter chaves estáticas como text_no_results, text_cart, etc.
		$this->loadLanguageData('common/cart', $data);

		// Alpha Engine: Injeção do repositório de domínio
		$cartRepository = $this->repository->get(CartRepository::class);
		$cartData = $cartRepository->getCartDisplayData();
		
		// Alpha Engine: Sobrescreve as variáveis de idioma cruas (ex: %s) com as strings dinâmicas formatadas do DTO
		$data = array_merge($data, $cartData->toArray());

		// Alpha Engine: Renderização de Totais centralizada (Exatamente igual ao checkout/cart)
		$totals = [];
		$taxes = $cartRepository->getTaxes();
		$total = 0;
		
		if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
		    $cartRepository->getTotals($totals, $taxes, $total);
		}
		
		foreach ($totals as $result) {
		    $data['totals'][] = ['title' => $result['title'], 'text' => $this->currency->format($result['value'], $this->session->data['currency'])];
		}

		// Alpha Engine: Define a rota de re-renderização via AJAX usada pelo common.js
		$data['list'] = $this->url->link('common/cart|info', 'language=' . $this->config->get('config_language'));

		// Widgets parciais usam load->view diretamente para não invocar o Header novamente (Loop Infinito)
		return $this->load->view('common/cart', $data);
	}

	/**
	 * Info
	 *
	 * @return string
	 */
	public function info(): string {
		return $this->index();
	}

	/**
	 * Remove Product
	 *
	 * @return void
	 */
	public function remove(): void {
		$this->language->load('checkout/cart');

		$json = [];
		$cart_id = (int)($this->request->post['key'] ?? $this->request->get['key'] ?? 0);

		$cartRepository = $this->repository->get(CartRepository::class);
		
		// Alpha Engine: Encapsulamento da remoção e limpeza de estado (Session) no Repositório
		$cartRepository->removeAndClearCheckout($cart_id);

		$json['success'] = $this->language->get('text_remove');

		$this->jsonResponse($json);
	}

    /**
     * Add Product
     *
     * @return void
     */
    public function add(): void {
        $this->language->load('checkout/cart');

        $json = [];

        // Validate product ID and quantity
        $product_id = (int)($this->request->post['product_id'] ?? 0);
        $quantity = (int)($this->request->post['quantity'] ?? 1);
        $option = $this->request->post['option'] ?? [];
        $subscription_plan_id = (int)($this->request->post['subscription_plan_id'] ?? 0);

        if (!$product_id) {
            $json['error']['product'] = $this->language->get('error_product_id_missing'); // Assuming this language key exists
        }

        if ($quantity <= 0) {
            $json['error']['quantity'] = $this->language->get('error_quantity_invalid'); // Assuming this language key exists
        }

        // TODO: Adicionar validação de opções de produto (se obrigatórias)
        // Exemplo:
        // $product_info = $this->mapper->get(ProductMapper::class)->getProduct($product_id, $this->language_id, (int)$this->config->get('config_store_id'));
        // if ($product_info && $product_info->getRequiredOptions() && empty($option)) {
        //     $json['error']['option'] = $this->language->get('error_required_option');
        // }

        if (!$json) {
            $cartRepository = $this->repository->get(CartRepository::class);

            // Alpha Engine: Adição e limpeza de checkout atômicas via Repository
            $cartRepository->addAndClearCheckout(
                (int)$this->customer->getId(),
                $this->session->getId(),
                $product_id,
                $quantity,
                $option,
                $subscription_plan_id
            );

            $json['success'] = sprintf($this->language->get('text_success_add'), $this->url->link('checkout/cart', 'language=' . $this->config->get('config_language'))); // Assuming this language key exists
        }

        $this->jsonResponse($json);
    }

	/**
	 * Edit/Update Product Quantity
	 *
	 * @return void
	 */
	public function edit(): void {
		$this->language->load('checkout/cart');

		$json = [];

		$cart_id = (int)($this->request->post['key'] ?? 0);
		$quantity = (int)($this->request->post['quantity'] ?? 1);

		if (!$json) {
			$cartRepository = $this->repository->get(CartRepository::class);
			
			// Alpha Engine: Atualização centralizada com invalidação de checkout
			$cartRepository->updateAndClearCheckout($cart_id, $quantity);

			$json['success'] = $this->language->get('text_edit');
		}

		$this->jsonResponse($json);
	}
}
