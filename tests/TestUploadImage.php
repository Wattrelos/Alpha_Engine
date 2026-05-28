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
$config->set('config_url', HTTP_SERVER);
$registry->set('config', $config);

// Inicializa as Fábricas Alpha
$mapperFactory = new \Alpha\Mappers\MapperFactory($registry);
$repositoryFactory = new \Alpha\Model\Domain\Repositories\RepositoryFactory($mapperFactory, $registry);
$registry->set('alpha_repository_factory', $repositoryFactory);
$registry->set('alpha_mapper_factory', $mapperFactory);

echo "🔍 Iniciando Testes de UploadRepository e ImagePresenter...\n";
echo "------------------------------------------------------------\n";

try {
    // 1. Testando UploadRepository
    $uploadRepo = $repositoryFactory->get(\Alpha\Model\Domain\Repositories\UploadRepository::class);
    
    $testName = "test_file.txt";
    $testFile = "test_file.txt." . bin2hex(random_bytes(8));
    
    echo "1. Criando upload: name = $testName, file = $testFile...\n";
    $code = $uploadRepo->addUpload($testName, $testFile);
    echo "✅ Upload criado com código token: $code\n";
    
    echo "2. Buscando upload pelo código...\n";
    $upload = $uploadRepo->findByCode($code);
    if ($upload && $upload->getFilename() === $testFile) {
        echo "✅ Upload encontrado com sucesso!\n";
        echo "-> ID: " . $upload->getId() . "\n";
        echo "-> Nome: " . $upload->getName() . "\n";
        echo "-> Arquivo: " . $upload->getFilename() . "\n";
    } else {
        echo "❌ Falha ao buscar upload por código ou dados inválidos.\n";
    }
    
    echo "3. Buscando upload via DTO legado...\n";
    $uploadInfo = $uploadRepo->getUploadByCode($code);
    if (!empty($uploadInfo) && $uploadInfo['filename'] === $testFile) {
        echo "✅ Upload DTO obtido com sucesso!\n";
        echo "-> Name in DTO: " . $uploadInfo['name'] . "\n";
    } else {
        echo "❌ Falha ao obter DTO de upload.\n";
    }

    // Limpar o registro criado no banco de dados para não sujar o ambiente
    if ($upload) {
        $mapper = $mapperFactory->get(\Alpha\Mappers\EntityMappers\UploadMapper::class);
        $mapper->delete($upload->getId());
        echo "🗑️ Registro de teste removido do banco de dados.\n";
    }

    // 2. Testando ImagePresenter
    echo "\n4. Testando ImagePresenter...\n";
    $imagePresenter = new \Alpha\Support\Presenters\ImagePresenter($registry);
    
    $resizedUrl = $imagePresenter->resize('placeholder.png', 100, 100);
    echo "✅ ImagePresenter resize gerado com sucesso!\n";
    echo "-> URL gerada: $resizedUrl\n";

} catch (\Exception $e) {
    echo "\n❌ Erro durante o teste: " . $e->getMessage() . "\n";
}
echo "------------------------------------------------------------\n";
