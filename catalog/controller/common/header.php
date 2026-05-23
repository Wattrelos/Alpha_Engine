<?php
namespace Opencart\Catalog\Controller\Common;
/**
 * Alpha Engine: Imports
 */
use Alpha\Controller\BaseController;
use Alpha\Model\Domain\Repositories\HeaderRepository;
use Alpha\Model\Domain\Repositories\LayoutRepository;
use Opencart\Catalog\Controller\Common\Language;
use Opencart\Catalog\Controller\Common\Currency;
use Opencart\Catalog\Controller\Common\Search;
use Opencart\Catalog\Controller\Common\Cart;
use Opencart\Catalog\Controller\Common\Menu;
/**,
 * 3
 * Class Header
 *
 * Can be called from $this->load->controller('common/header');
 *
 * @package Opencart\Catalog\Controller\Common
 */
class Header extends BaseController {
	/**
	 * Index
	 *
	 * @return string
	 */
	public function index(): string {
		// --- MARCADOR DE MEMÓRIA (DEBUG) ---
		$log = $this->registry->get('log');
		$logMemory = function(string $step) use ($log) {
			$mb = round(memory_get_usage(true) / 1048576, 2);
			$peak = round(memory_get_peak_usage(true) / 1048576, 2);
			$log->write("[DEBUG MEMÓRIA] {$step} | Atual: {$mb}MB | Pico: {$peak}MB");
		};
		$logMemory('1. Início do Header');

		$route = (string)($this->request->get['route'] ?? 'common/home');

		// Alpha Engine: Injeção do repositório de domínio para dados de identidade e navegação
		$headerRepository = $this->getRepository(HeaderRepository::class);
		$headerData = $headerRepository->getHeaderData();

		$data = $headerData->toArray();

		// Alpha Engine: Carrega as traduções do header (text_home, text_account, etc.)
		$this->loadLanguageData('common/header', $data);

		// Alpha Engine: Injeção de Estado e Contatos
		$data['logged']    = $this->customer->isLogged();
		$data['telephone'] = $this->config->get('config_telephone');

		// Alpha Engine: Construção de Rotas Base (Links)
		$langUrl = 'language=' . $this->config->get('config_language');
		$data['home']          = $this->url->link('common/home', $langUrl);
		$data['contact']       = $this->url->link('information/contact', $langUrl);
		$data['register']      = $this->url->link('account/register', $langUrl);
		$data['login']         = $this->url->link('account/login', $langUrl);
		$data['account']       = $this->url->link('account/account', $langUrl);
		$data['order']         = $this->url->link('account/order', $langUrl);
		$data['transaction']   = $this->url->link('account/transaction', $langUrl);
		$data['logout']        = $this->url->link('account/logout', $langUrl);
		$data['shopping_cart'] = $this->url->link('checkout/cart', $langUrl);
		$data['checkout']      = $this->url->link('checkout/checkout', $langUrl);
		$data['wishlist']      = $this->url->link('account/wishlist', $langUrl);

		// Resolução da contagem da Lista de Desejos (Wishlist) baseada na tradução carregada
		if ($this->customer->isLogged()) {
			$this->load->model('account/wishlist');
			$data['text_wishlist'] = sprintf($this->language->get('text_wishlist'), $this->model_account_wishlist->getTotalWishlist());
		} else {
			$data['text_wishlist'] = sprintf($this->language->get('text_wishlist'), (isset($this->session->data['wishlist']) ? count($this->session->data['wishlist']) : 0));
		}

		// Alpha Engine: Resolvemos os módulos de analytics
		$data['analytics'] = $headerRepository->getAnalyticsModules($headerData);
		
		// Alpha Engine: Sincronização de Assets via Motor Alpha
		// Substitui as chamadas legadas ao $this->document->getStyles/Scripts
		$layoutRepository  = $this->getRepository(LayoutRepository::class);
		$data['styles']    = $layoutRepository->getStylesByRoute($route, $this->document);
		$data['scripts']   = $layoutRepository->getScriptsByRoute($route, 'header', $this->document);
		$data['links']     = $layoutRepository->getLinksByRoute($route, $this->document);

		// Alpha Engine: Injeção Loader-Free de sub-componentes do Header
		$logMemory('2. Antes de carregar Language');
		$data['language'] = (new Language($this->registry))->index();
		$logMemory('3. Após Language, antes de Currency');
		$data['currency'] = (new Currency($this->registry))->index();
		$logMemory('4. Após Currency, antes de Search');
		$data['search']   = (new Search($this->registry))->index();
		$logMemory('5. Após Search, antes de Cart');
		$data['cart']     = (new Cart($this->registry))->index();
		$logMemory('6. Após Cart, antes de Menu');
		$data['menu']     = (new Menu($this->registry))->index();
		$logMemory('7. Após Menu, fim do processamento do Header');

		return $this->load->view('common/header', $data);
	}
}
