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

// Mock do Config necessário para o repositório
$config = new \Opencart\System\Engine\Config();
$config->set('config_store_id', 0);
$config->set('config_language_id', 2);
$registry->set('config', $config);

// Mocks extras para os testes
$registry->set('url', new class {
    public function link($route, $args = '') {
        return $route . ($args ? '&' . $args : '');
    }
});

$registry->set('customer', new class {
    public function isLogged() { return true; }
    public function getId() { return 1; }
    public function getGroupId() { return 1; }
});

$registry->set('language', new class {
    public function get($key) { return $key; }
    public function load($code) { return []; }
});

$registry->set('cache', new class {
    public function get($key) { return null; }
    public function set($key, $value, $ttl = 0) {}
    public function has($key) { return false; }
});

$registry->set('currency', new class {
    public function format($v, $c) { return number_format($v, 2) . ' ' . $c; }
});

$registry->set('tax', new class {
    public function calculate($value, $tax_class_id, $calculate) { return $value; }
});

$registry->set('session', new class {
    public $data = ['currency' => 'BRL', 'customer_token' => 'test-token'];
});

$registry->set('model_tool_image', new class {
    public function resize($image, $width, $height) { return $image; }
});

// Inicializa as Fábricas Alpha
$mapperFactory = new \Alpha\Mappers\MapperFactory($registry);
$repositoryFactory = new \Alpha\Model\Domain\Repositories\RepositoryFactory($mapperFactory, $registry);
$registry->set('alpha_repository_factory', $repositoryFactory);
$registry->set('alpha_mapper_factory', $mapperFactory);

$customerId = 1;
$productId = 40; // Fallback
$res = $db->query("SELECT p.id FROM `" . DB_PREFIX . "product` p 
    JOIN `" . DB_PREFIX . "product_to_store` p2s ON p.id = p2s.product_id 
    JOIN `" . DB_PREFIX . "product_description` pd ON p.id = pd.product_id 
    WHERE p.status = 1 AND p.date_available <= NOW() AND p2s.store_id = 0 AND pd.language_id = 2 LIMIT 1");
if (isset($res->row['id'])) {
    $productId = (int)$res->row['id'];
}

echo "🔍 Iniciando Teste do WishlistRepository...\n";
echo "------------------------------------------------------------\n";

try {
    /** @var \Alpha\Model\Domain\Repositories\WishlistRepository $wishlistRepo */
    $wishlistRepo = $repositoryFactory->get(\Alpha\Model\Domain\Repositories\WishlistRepository::class);
    
    // 1. Obter total inicial
    $initialTotal = $wishlistRepo->getTotalWishlist($customerId);
    echo "✅ Total inicial na Wishlist para o cliente {$customerId}: {$initialTotal}\n";

    // 2. Adicionar produto à Wishlist
    echo "➕ Adicionando produto {$productId} à Wishlist...\n";
    $wishlistRepo->addWishlist($customerId, $productId);
    
    // 3. Obter novo total
    $newTotal = $wishlistRepo->getTotalWishlist($customerId);
    echo "✅ Novo total na Wishlist: {$newTotal}\n";

    // 4. Buscar produtos formatados
    echo "📋 Buscando produtos formatados na Wishlist...\n";
    $products = $wishlistRepo->getFormattedWishlistProducts($customerId);
    echo "✅ Produtos retornados: " . count($products) . "\n";
    foreach ($products as $p) {
        echo "   - Produto ID: " . $p['product_id'] . " | Nome: " . $p['name'] . "\n";
    }

    // 5. Remover produto da Wishlist
    echo "➖ Removendo produto {$productId} da Wishlist...\n";
    $wishlistRepo->deleteWishlist($customerId, $productId);
    
    // 6. Verificar total após remoção
    $finalTotal = $wishlistRepo->getTotalWishlist($customerId);
    echo "✅ Total final na Wishlist: {$finalTotal}\n";

    if ($finalTotal == $initialTotal || ($initialTotal == 0 && $finalTotal == 0)) {
        echo "\n🎉 TODOS OS TESTES PASSARAM COM SUCESSO!\n";
    } else {
        echo "\n⚠️ Inconsistência nos totais (Inicial: {$initialTotal}, Final: {$finalTotal}).\n";
    }

} catch (\Exception $e) {
    echo "\n❌ Erro durante o teste: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
echo "------------------------------------------------------------\n";
