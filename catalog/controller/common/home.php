<?php
namespace Opencart\Catalog\Controller\Common;

use Alpha\Controller\BaseController;
use Alpha\Model\Domain\Repositories\HomeRepository;

class Home extends BaseController {
	public function index(): void {
		// Alpha Engine: Utiliza o HomeRepository injetado pela BaseController
        $homeRepository = $this->getRepository(HomeRepository::class);
        $homeData = $homeRepository->getHomeData();
        
        $data = $homeData->toArray();

        // Alpha Engine: As meta tags (SEO) devem ser injetadas no objeto Document para o header.twig ler
        $this->document->setTitle($homeData->get('title') ?? $this->config->get('config_meta_title'));
        $this->document->setDescription($homeData->get('description') ?? $this->config->get('config_meta_description'));
        $this->document->setKeywords($homeData->get('keywords') ?? $this->config->get('config_meta_keyword'));

        // Alpha Engine: Registro de estilos CSS customizados (Identidade Visual)
        $this->document->addStyle('catalog/view/stylesheet/custom/personalizada.css');

        // Alpha Engine: Carregamento unificado das traduções
        $this->loadLanguageData('common/home', $data);

        $data['continue'] = $this->url->link('common/home', 'language=' . $this->config->get('config_language'));

		// Alpha Engine: O método render da BaseController injeta as colunas, header e footer automaticamente
		$this->render('common/home', $data);
	}
}