<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\StoreMapper;
use Alpha\Model\Domain\InterfaceEntity;
use Alpha\Support\Registry;

/**
 * StoreRepository - Autoridade de Domínio para Lojas (Store).
 */
class StoreRepository extends AbstractRepository implements BaseRepositoryInterface
{
    protected function getMapper(): StoreMapper
    {
        return $this->mapperFactory->get(StoreMapper::class);
    }

    public function getStore(int $store_id): array
    {
        return $this->getMapper()->getStore($store_id);
    }

    public function getStoreByHostname(string $hostname): array
    {
        return $this->getMapper()->getStoreByHostname($hostname) ?? [];
    }

    public function getStores(): array
    {
        return $this->getMapper()->getStores();
    }

    /**
     * Create Store Instance
     *
     * @param int    $store_id
     * @param string $language
     * @param string $session_id
     *
     * @throws \Exception
     *
     * @return Registry
     */
    public function createStoreInstance(int $store_id = 0, string $language = '', string $session_id = ''): Registry
    {
        // Autoloader
        $this->autoloader->register('Opencart\Catalog', DIR_APPLICATION);

        // Registry
        $registry = new Registry();
        $registry->set('autoloader', $this->autoloader);

        // Config
        $config = new \Opencart\System\Engine\Config();
        $registry->set('config', $config);

        // Load the default config
        $config->addPath(DIR_CONFIG);
        $config->load('default');
        $config->set('application', 'Catalog');

        // Store
        $config->set('config_store_id', $store_id);

        // Logging
        $registry->set('log', $this->log);

        // Event
        $event = new \Opencart\System\Engine\Event($registry);
        $registry->set('event', $event);

        // Event Register
        if ($config->has('action_event')) {
            foreach ($config->get('action_event') as $key => $value) {
                foreach ($value as $priority => $action) {
                    $event->register($key, new \Opencart\System\Engine\Action($action), $priority);
                }
            }
        }

        // Factory
        $registry->set('factory', new \Opencart\System\Engine\Factory($registry));

        // Loader
        $loader = new \Opencart\System\Engine\Loader($registry);
        $registry->set('load', $loader);

        // Create a dummy request class so we can feed the data to the order editor
        $request = new \stdClass();
        $request->get = [];
        $request->post = [];
        $request->server = $this->request->server;
        $request->cookie = [];

        // Request
        $registry->set('request', $request);

        // Response
        $response = new \Opencart\System\Library\Response();
        $registry->set('response', $response);

        // Database
        $registry->set('db', $this->db);

        // Cache
        $registry->set('cache', $this->cache);

        // Session
        $session = new \Opencart\System\Library\Session($config->get('session_engine'), $registry);
        $session->start();
        $registry->set('session', $session);

        // Template
        $template = new \Opencart\System\Library\Template($config->get('template_engine'));
        $template->addPath(DIR_TEMPLATE);
        $registry->set('template', $template);

        // Adding language var to the GET variable so there is a default language
        if ($language) {
            $request->get['language'] = $language;
        } else {
            $request->get['language'] = $config->get('language_code');
        }

        // Language
        $language = new \Opencart\System\Library\Language($request->get['language']);
        $language->addPath(DIR_APPLICATION . 'language/');
        $language->load('default');
        $registry->set('language', $language);

        // Url
        $registry->set('url', new \Opencart\System\Library\Url($config->get('site_url')));

        // Document
        $registry->set('document', new \Opencart\System\Library\Document());

        // Run pre actions to load key settings and classes.
        $pre_actions = [
            'startup/setting',
            'startup/extension',
            'startup/customer',
            'startup/tax',
            'startup/currency',
            'startup/application',
            'startup/startup',
            'startup/event'
        ];

        // Pre Actions
        foreach ($pre_actions as $pre_action) {
            $loader->controller($pre_action);
        }

        return $registry;
    }

    // Métodos obrigatórios da BaseRepositoryInterface
    public function find(int $id): ?InterfaceEntity { return null; }
    public function findAll(): array { return []; }
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array { return []; }
    public function findOneBy(array $criteria): ?InterfaceEntity { return null; }
}
