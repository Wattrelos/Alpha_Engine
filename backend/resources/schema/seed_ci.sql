-- ========================================================
-- Alpha Engine CI Baseline Seed Data
-- Provides minimal essential entities for Unit, BDD & E2E tests:
-- 1. Super Administrator (user id = 1)
-- 2. Category (category id = 1)
-- 3. Product (product id = 1 - Adaptador Tubo Cerâmica)
-- ========================================================

SET FOREIGN_KEY_CHECKS=0;

-- 1. Admin User
INSERT INTO `agsc_user` (`id`, `user_group_id`, `username`, `password`, `firstname`, `lastname`, `email`, `image`, `ip`, `status`, `date_added`)
VALUES (1, 1, 'admin', '$argon2id$v=19$m=65536,t=4,p=1$WEhPekN1eFUwYlF1a0NIRQ$LnQLl1+NgX3xyUwlWod6xBdeA/EoIoleolZd+CgquFo', 'Super', 'Admin', 'admin@example.com', '', '127.0.0.1', 1, NOW())
ON DUPLICATE KEY UPDATE `id` = `id`;

-- 2. Base Category
INSERT INTO `agsc_category` (`id`, `image`, `parent_id`, `sort_order`, `status`)
VALUES (1, 'image/category/category_1780677063_6a22f9c73f29c.png', NULL, 0, 1)
ON DUPLICATE KEY UPDATE `id` = `id`;

INSERT INTO `agsc_category_description` (`category_id`, `language_id`, `name`, `description`, `meta_title`, `meta_description`, `meta_keyword`)
VALUES 
(1, 2, 'Materiais de Construção', 'Pisos, Revestimentos e Tubos', 'Materiais', '', ''),
(1, 1, 'Building Materials', 'Flooring, Coatings and Pipes', 'Materials', '', '')
ON DUPLICATE KEY UPDATE `category_id` = `category_id`;

INSERT INTO `agsc_category_to_store` (`category_id`, `store_id`)
VALUES (1, 0), (1, 1)
ON DUPLICATE KEY UPDATE `category_id` = `category_id`;

-- 3. Base Product
INSERT INTO `agsc_product` (`id`, `master_id`, `model`, `sku`, `quantity`, `stock_status_id`, `image`, `manufacturer_id`, `shipping`, `price`, `points`, `tax_class_id`, `date_available`, `weight`, `weight_class_id`, `length`, `width`, `height`, `length_class_id`, `subtract`, `minimum`, `rating`, `sort_order`, `status`, `date_added`, `date_modified`, `ncm`, `cest`)
VALUES (1, 0, 'ADAPT-TUBO-01', 'SKU-001', 50, 7, 'image/product/biancoglossczbrirt60x60-f1.webp', NULL, 1, 99.9000, 0, 0, '2026-01-01', 0, 0, 0, 0, 0, 0, 1, 1, 5, 0, 1, NOW(), NOW(), '', '')
ON DUPLICATE KEY UPDATE `id` = `id`;

INSERT INTO `agsc_product_description` (`product_id`, `language_id`, `name`, `description`, `tag`, `meta_title`, `meta_description`, `meta_keyword`)
VALUES 
(1, 2, 'Adaptador Tubo Cerâmica 100mm', 'Adaptador para tubo cerâmica de alta resistência.', 'tubo, adaptador, ceramica', 'Adaptador Tubo Cerâmica', '', ''),
(1, 1, 'Ceramic Pipe Adapter 100mm', 'Adapter for ceramic pipes.', 'pipe, adapter, ceramic', 'Pipe Adapter', '', '')
ON DUPLICATE KEY UPDATE `product_id` = `product_id`;

INSERT INTO `agsc_product_to_category` (`product_id`, `category_id`)
VALUES (1, 1)
ON DUPLICATE KEY UPDATE `product_id` = `product_id`;

INSERT INTO `agsc_product_to_store` (`product_id`, `store_id`)
VALUES (1, 0), (1, 1)
ON DUPLICATE KEY UPDATE `product_id` = `product_id`;

SET FOREIGN_KEY_CHECKS=1;
