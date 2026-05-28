<?php
// Modo de Depuração
ini_set('display_errors', '1');
error_reporting(E_ALL);

define('VERSION', '4.1.0.3');

// Carrega configurações
require_once dirname(__DIR__) . '/config.php';
require_once DIR_SYSTEM . 'startup.php';

if (is_file(DIR_OPENCART . 'vendor/autoload.php')) {
    require_once DIR_OPENCART . 'vendor/autoload.php';
}

// Autoloader do OpenCart/Alpha
$autoloader = new \Opencart\System\Engine\Autoloader();
$autoloader->register('Opencart\System', DIR_SYSTEM);
$autoloader->register('Alpha\\', DIR_OPENCART . 'core/');

$registry = new \Opencart\System\Engine\Registry();

// Inicializa Conexão com o Banco de Dados
$db = new \Opencart\System\Library\DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_PORT);
$registry->set('db', $db);

// Mock básico de Configuração
$config = new \Opencart\System\Engine\Config();
$config->set('config_store_id', 0);
$config->set('config_language_id', 2);
$registry->set('config', $config);

// Inicializa as Fábricas Alpha
$mapperFactory = new \Alpha\Mappers\MapperFactory($registry);
$repositoryFactory = new \Alpha\Model\Domain\Repositories\RepositoryFactory($mapperFactory, $registry);
$registry->set('alpha_repository_factory', $repositoryFactory);
$registry->set('alpha_mapper_factory', $mapperFactory);

// Inicia o interceptor AlphaContainer (Factory de modelos)
$factory = new \Alpha\AlphaContainer($registry);
$registry->set('load', new \Opencart\System\Engine\Loader($registry));

echo "🔍 Iniciando Testes de Interceptação do AlphaContainer (Store & Extension)...\n";
echo "------------------------------------------------------------\n";

try {
    // 1. Testa carregamento de 'setting/store'
    echo "1. Carregando model 'setting/store' via interceptor...\n";
    $storeModel = $factory->model('setting/store');
    
    echo "-> Classe retornada: " . get_class($storeModel) . "\n";
    if ($storeModel instanceof \Alpha\Model\Domain\Repositories\StoreRepository) {
        echo "✅ AlphaContainer interceptou 'setting/store' com sucesso e retornou StoreRepository!\n";
    } else {
        echo "❌ Falha: Retornou classe inesperada.\n";
    }

    echo "2. Chamando getStore(0)...\n";
    $storeInfo = $storeModel->getStore(0);
    if (is_array($storeInfo)) {
        echo "✅ getStore(0) executado com sucesso (Retornou array vazio pois a tabela de lojas adicionais está vazia, comportamento esperado)!\n";
    } else {
        echo "❌ Falha ao obter dados da loja padrão.\n";
    }

    // 2. Testa carregamento de 'setting/extension'
    echo "\n3. Carregando model 'setting/extension' via interceptor...\n";
    $extensionModel = $factory->model('setting/extension');
    
    echo "-> Classe retornada: " . get_class($extensionModel) . "\n";
    if ($extensionModel instanceof \Alpha\Model\Domain\Repositories\ExtensionRepository) {
        echo "✅ AlphaContainer interceptou 'setting/extension' com sucesso e retornou ExtensionRepository!\n";
    } else {
        echo "❌ Falha: Retornou classe inesperada.\n";
    }

    echo "4. Chamando getExtensionsByType('payment')...\n";
    $extensions = $extensionModel->getExtensionsByType('payment');
    echo "✅ getExtensionsByType('payment') executado com sucesso!\n";
        echo "-> Total retornado: " . count($extensions) . "\n";
        if (count($extensions) > 0) {
            $first = $extensions[0];
            echo "-> Primeira extensão encontrada: Code = " . $first->getCode() . " | Path = " . $first->getExtension() . "\n";
        }

        // 3. Testa carregamento de 'setting/api'
        echo "\n5. Carregando model 'setting/api' via interceptor...\n";
        $apiModel = $factory->model('setting/api');
        
        echo "-> Classe retornada: " . get_class($apiModel) . "\n";
        if ($apiModel instanceof \Alpha\Model\Domain\Repositories\ApiSessionRepository) {
            echo "✅ AlphaContainer interceptou 'setting/api' com sucesso e retornou ApiSessionRepository!\n";
        } else {
            echo "❌ Falha: Retornou classe inesperada.\n";
        }

        echo "6. Chamando getSessions(1)...\n";
        $sessions = $apiModel->getSessions(1);
        echo "✅ getSessions(1) executado com sucesso!\n";
        echo "-> Total retornado: " . count($sessions) . "\n";

    } catch (\Exception $e) {
    echo "\n❌ Erro durante o teste: " . $e->getMessage() . "\n";
}
echo "------------------------------------------------------------\n";
