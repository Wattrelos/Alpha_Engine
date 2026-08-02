<?php

namespace Alpha\Admin\Controllers\Actions\Catalog\Product;

use Alpha\Controller\BaseController;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Model\DataAccessObject\ConnectionDB;
use Alpha\Support\Facades\Url;

class DeleteProductAction extends BaseController implements \Alpha\Controller\Actions\ActionInterface
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $conn = ConnectionDB::getInstance()->getConnection();

        $productId = isset($args['id']) ? (int)$args['id'] : 0;

        if ($productId <= 0) {
            $adminPath = defined('ADMIN_PATH') ? ADMIN_PATH : '/LPDHED2dC7Gjrg2b';
            return $response
                ->withHeader('Location', $adminPath . '/produtos?error=' . urlencode('ID do produto inválido.'))
                ->withStatus(302);
        }

        try {
            $conn->beginTransaction();

            // 1. Verify if the product exists
            $stmtCheck = $conn->prepare("SELECT id FROM `" . DB_PREFIX . "product` WHERE `id` = ?");
            $stmtCheck->execute([$productId]);
            $exists = (bool)$stmtCheck->fetchColumn();

            if (!$exists) {
                $conn->rollBack();
                return $response
                    ->withHeader('Location', $adminPath . '/produtos?error=' . urlencode('Produto não encontrado.'))
                    ->withStatus(302);
            }

            // 2. Delete related data
            $conn->prepare("DELETE FROM `" . DB_PREFIX . "product_attribute` WHERE `product_id` = ?")->execute([$productId]);
            $conn->prepare("DELETE FROM `" . DB_PREFIX . "product_code` WHERE `product_id` = ?")->execute([$productId]);
            $conn->prepare("DELETE FROM `" . DB_PREFIX . "product_description` WHERE `product_id` = ?")->execute([$productId]);
            $conn->prepare("DELETE FROM `" . DB_PREFIX . "product_discount` WHERE `product_id` = ?")->execute([$productId]);
            $conn->prepare("DELETE FROM `" . DB_PREFIX . "product_filter` WHERE `product_id` = ?")->execute([$productId]);
            $conn->prepare("DELETE FROM `" . DB_PREFIX . "product_image` WHERE `product_id` = ?")->execute([$productId]);
            $conn->prepare("DELETE FROM `" . DB_PREFIX . "product_option` WHERE `product_id` = ?")->execute([$productId]);
            $conn->prepare("DELETE FROM `" . DB_PREFIX . "product_option_value` WHERE `product_id` = ?")->execute([$productId]);
            $conn->prepare("DELETE FROM `" . DB_PREFIX . "product_related` WHERE `product_id` = ? OR `related_id` = ?")->execute([$productId, $productId]);
            $conn->prepare("DELETE FROM `" . DB_PREFIX . "product_report` WHERE `product_id` = ?")->execute([$productId]);
            $conn->prepare("DELETE FROM `" . DB_PREFIX . "product_reward` WHERE `product_id` = ?")->execute([$productId]);
            $conn->prepare("DELETE FROM `" . DB_PREFIX . "product_subscription` WHERE `product_id` = ?")->execute([$productId]);
            $conn->prepare("DELETE FROM `" . DB_PREFIX . "product_to_category` WHERE `product_id` = ?")->execute([$productId]);
            $conn->prepare("DELETE FROM `" . DB_PREFIX . "product_to_layout` WHERE `product_id` = ?")->execute([$productId]);
            $conn->prepare("DELETE FROM `" . DB_PREFIX . "product_to_store` WHERE `product_id` = ?")->execute([$productId]);
            $conn->prepare("DELETE FROM `" . DB_PREFIX . "product_viewed` WHERE `product_id` = ?")->execute([$productId]);
            $conn->prepare("DELETE FROM `" . DB_PREFIX . "review` WHERE `product_id` = ?")->execute([$productId]);
            $conn->prepare("DELETE FROM `" . DB_PREFIX . "cart` WHERE `product_id` = ?")->execute([$productId]);
            $conn->prepare("DELETE FROM `" . DB_PREFIX . "customer_wishlist` WHERE `product_id` = ?")->execute([$productId]);
            $conn->prepare("DELETE FROM `" . DB_PREFIX . "coupon_product` WHERE `product_id` = ?")->execute([$productId]);
            $conn->prepare("DELETE FROM `" . DB_PREFIX . "subscription_product` WHERE `product_id` = ?")->execute([$productId]);

            // Delete SEO URL keyword for this product
            $conn->prepare("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE `key` = 'product_id' AND `value` = CAST(? AS CHAR)")->execute([$productId]);

            // 3. Delete main product entry (primary key column is "id")
            $stmt = $conn->prepare("DELETE FROM `" . DB_PREFIX . "product` WHERE `id` = ?");
            $stmt->execute([$productId]);

            $conn->commit();

            // 4. Clear cache
            if ($this->container->has(\Alpha\Support\Cache\CacheStrategyInterface::class)) {
                $cache = $this->container->get(\Alpha\Support\Cache\CacheStrategyInterface::class);
                if ($cache) {
                    $cache->delete("product.images.p{$productId}");
                    $cache->delete("product.codes.p{$productId}");

                    // Loop through common store IDs (1-5), language IDs (1-5), and customer groups (1-5)
                    for ($s = 1; $s <= 5; $s++) {
                        for ($l = 1; $l <= 5; $l++) {
                            $cache->delete("product.options.p{$productId}.l{$l}");
                            $cache->delete("product.subscriptions.p{$productId}.l{$l}");
                            $cache->delete("product.attributes.p{$productId}.l{$l}");
                            for ($cg = 1; $cg <= 5; $cg++) {
                                $cache->delete("product.{$productId}.{$l}.{$s}.{$cg}");
                                $cache->delete("product.discounts.p{$productId}.cg{$cg}");
                                $cache->delete("product.related.p{$productId}.l{$l}.s{$s}.cg{$cg}");
                            }
                        }
                    }
                }
            }

            return $response
                ->withHeader('Location', $adminPath . '/produtos?success=' . urlencode('Produto excluído com sucesso.'))
                ->withStatus(302);
        } catch (\Throwable $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            error_log("Erro ao deletar produto: " . $e->getMessage());
            return $response
                ->withHeader('Location', $adminPath . '/produtos?error=' . urlencode('Erro ao deletar produto: ' . $e->getMessage()))
                ->withStatus(302);
        }
    }
}
