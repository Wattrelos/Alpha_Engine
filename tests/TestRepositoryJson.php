<?php

namespace Alpha\Tests;

/**
 * TestRepositoryJson - Teste dinâmico para Repositories da Alpha Engine.
 * 
 * Este script permite testar os métodos padronizados (find, findAll) e métodos
 * específicos (ex: getIndexData, getHomeData) de qualquer repositório via GET.
 * 
 * Uso: 
 * - Padronizado: /tests/TestRepositoryJson.php?repo=Customer&id=1
 * - Específico: /tests/TestRepositoryJson.php?repo=Home&method=getHomeData
 */

// 1. Carregamento do ambiente OpenCart e Alpha Engine
require_once(dirname(__DIR__) . '/config.php');
require_once(DIR_SYSTEM . 'engine/autoloader.php');

$autoloader = new \Opencart\System\Engine\Autoloader();
$autoloader->register('Opencart\Catalog', DIR_APPLICATION);
$autoloader->register('Opencart\System', DIR_SYSTEM);

// Alpha Engine: Localização do diretório Core e registro do namespace
$alphaPath = realpath(dirname(DIR_SYSTEM) . '/core/');
if (!$alphaPath) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Diretório core não encontrado.']);
    exit;
}
$autoloader->register('Alpha', $alphaPath . '/');

use Alpha\Mappers\MapperFactory;
use Alpha\Model\Domain\Repositories\RepositoryFactory;
use Opencart\System\Engine\Registry;
use Opencart\System\Engine\Config;


/**
 * Serializador recursivo que suporta Entidades, DTOs (ViewResponse) e Arrays.
 */
function resultToData($data, &$visited = []) {
    if (is_array($data)) {
        return array_map(fn($item) => resultToData($item, $visited), $data);
    }

    if (is_object($data)) {
        $oid = spl_object_hash($data);
        if (isset($visited[$oid])) return '*** CIRCULAR (' . get_class($data) . ') ***';
        $visited[$oid] = true;

        $result = ['_class' => get_class($data)];

        // Prioriza DTOs que possuem o método toArray() (como ViewResponse ou ProductShowcaseDTO)
        if (method_exists($data, 'toArray')) {
            $result += $data->toArray();
        } else {
            // Fallback para reflexão de métodos Getter
            foreach (get_class_methods($data) as $method) {
                if (str_starts_with($method, 'get') && !in_array($method, ['getters', 'getRegistry'])) {
                    $key = lcfirst(substr($method, 3));
                    try {
                        $value = $data->$method();
                        $result[$key] = resultToData($value, $visited);
                    } catch (\Throwable) {
                        $result[$key] = '[Error]';
                    }
                }
            }
        }
        unset($visited[$oid]);
        return $result;
    }
    return $data;
}

header('Content-Type: application/json; charset=utf-8');

try {
    $repoName = $_GET['repo'] ?? '';
    $id       = (int)($_GET['id'] ?? 0);
    $method   = $_GET['method'] ?? '';

    if (!$repoName) {
        throw new \Exception("Parâmetro 'repo' é obrigatório. Ex: ?repo=Address&id=16");
    }

    // 2. Setup do Registry para satisfazer as dependências do AbstractRepository
    $registry = new Registry();
    $config = new Config();
    $config->set('config_language_id', 2); // Default Alpha (PT-BR)
    $config->set('config_store_id', 0);
    $registry->set('config', $config);

    // Alpha Engine: Mocks completos para satisfazer as dependências dos Repositories
    $registry->set('language', new class { 
        public function get($key) { return $key; } 
        public function load($code) { return []; } 
    });

    $registry->set('url', new class { public function link($r, $a = '') { return $r . ($a ? '&' . $a : ''); } });

    $registry->set('document', new class {
        public function setTitle($t) {}
        public function setDescription($d) {}
        public function setKeywords($k) {}
        public function getTitle() { return 'Alpha Unit Test'; }
        public function getDescription() { return ''; }
        public function getKeywords() { return ''; }
    });

    $registry->set('cache', new class {
        public function get($k) { return null; }
        public function set($k, $v) {}
    });

    $registry->set('session', new class {
        public $data = ['currency' => 'BRL', 'customer_token' => 'test-token'];
        public function getId() { return 'test-session-id'; }
    });

    $registry->set('customer', new class {
        public function isLogged() { return false; }
        public function getId() { return 0; }
        public function getGroupId() { return 1; }
    });

    $registry->set('currency', new class {
        public function format($v, $c) { return number_format($v, 2) . ' ' . $c; }
    });

    $mapperFactory = new MapperFactory($registry);
    $repoFactory = new RepositoryFactory($mapperFactory, $registry);

    // 3. Instanciação dinâmica via Factory
    $repository = $repoFactory->get($repoName);
    $output = null;

    if ($method && method_exists($repository, $method)) {
        $output = $repository->$method($id ?: null);
    } elseif ($id > 0) {
        $output = $repository->find($id);
    } else {
        $output = $repository->findAll();
    }

    echo json_encode(resultToData($output), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (\Throwable $t) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $t->getMessage(), 'line' => $t->getLine()]);
}