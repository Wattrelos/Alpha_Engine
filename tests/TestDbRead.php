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

// Inicializa as Fábricas Alpha
$mapperFactory = new \Alpha\Mappers\MapperFactory($registry);
$repositoryFactory = new \Alpha\Model\Domain\Repositories\RepositoryFactory($mapperFactory, $registry);

echo "🔍 Iniciando Teste de Leitura no Banco de Dados...\n";
echo "------------------------------------------------------------\n";

try {
    // 1. Obtém o repositório correto (LanguageRepository)
    $languageRepo = $repositoryFactory->get(\Alpha\Model\Domain\Repositories\LanguageRepository::class);
    
    // Tenta buscar a linguagem com ID 1 (geralmente en-gb no OpenCart)
    // NOTA: Se o seu repositório base usa findById, altere find(1) para findById(1)
    $language = $languageRepo->find(2); // Observação: Restaurei id = 1 no banco de dados, pois é a linguagem padrão.

    if ($language) {
        echo "✅ Leitura bem-sucedida usando o Alpha Engine ORM!\n";
        echo "-> Entidade: Language\n";
        echo "-> Nome: " . $language->getName() . "\n";
        echo "-> Código: " . $language->getCode() . "\n";
    } else {
        echo "⚠️ Nenhum registro encontrado com ID 1 na tabela language.\n";
    }

    // 2. Teste extra com Loja (Store)
    $storeRepo = $repositoryFactory->get(\Alpha\Model\Domain\Repositories\StoreRepository::class);
    $store = $storeRepo->find(0); // A loja principal (padrão) do OpenCart tem ID 0
    if ($store) {
        echo "\n✅ Leitura da Loja padrão concluída com sucesso!\n";
        echo "-> Nome da Loja: " . $store->getName() . "\n";
    }
} catch (\Exception $e) {
    echo "\n❌ Erro durante o teste: " . $e->getMessage() . "\n";
}
echo "------------------------------------------------------------\n";
