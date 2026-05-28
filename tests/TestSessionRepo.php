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
$config->set('config_session_expire', 3600);
$registry->set('config', $config);

// Inicializa as Fábricas Alpha
$mapperFactory = new \Alpha\Mappers\MapperFactory($registry);
$repositoryFactory = new \Alpha\Model\Domain\Repositories\RepositoryFactory($mapperFactory, $registry);
$registry->set('alpha_repository_factory', $repositoryFactory);
$registry->set('alpha_mapper_factory', $mapperFactory);
$registry->set('repository', $repositoryFactory);

echo "🔍 Iniciando Testes de Persistência de Sessão no Alpha Engine...\n";
echo "------------------------------------------------------------\n";

try {
    /** @var \Alpha\Model\Domain\Repositories\SessionRepository $sessionRepo */
    $sessionRepo = $repositoryFactory->get(\Alpha\Model\Domain\Repositories\SessionRepository::class);
    
    $token = 'test_token_' . md5(uniqid(mt_rand(), true));
    $testData = [
        'customer_id' => 12345,
        'email' => 'teste@alpha.com',
        'cart' => [
            '10' => 2,
            '15' => 1
        ]
    ];

    echo "1. Escrevendo dados na sessão via token: {$token}...\n";
    $sessionRepo->write($token, $testData, 3600);
    echo "✅ Sessão gravada com sucesso!\n";

    echo "\n2. Lendo dados da sessão recém-gravada...\n";
    $readData = $sessionRepo->read($token);
    
    echo "-> Dados lidos: " . json_encode($readData) . "\n";
    if (isset($readData['customer_id']) && $readData['customer_id'] === 12345) {
        echo "✅ Os dados lidos são idênticos aos gravados (customer_id = 12345)!\n";
    } else {
        echo "❌ Falha: Os dados lidos estão vazios ou incorretos.\n";
    }

    echo "\n3. Destruindo a sessão...\n";
    $sessionRepo->destroy($token);
    echo "✅ Sessão destruída com sucesso!\n";

    echo "\n4. Tentando ler a sessão destruída...\n";
    $postDestroyData = $sessionRepo->read($token);
    echo "-> Dados pós-destruição: " . json_encode($postDestroyData) . "\n";
    if (empty($postDestroyData)) {
        echo "✅ A sessão foi completamente removida do banco de dados!\n";
    } else {
        echo "❌ Falha: A sessão ainda existe no banco de dados.\n";
    }

} catch (\Exception $e) {
    echo "\n❌ Erro durante o teste: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
echo "------------------------------------------------------------\n";
