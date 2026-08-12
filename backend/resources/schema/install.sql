-- ========================================================
-- Alpha Engine SaaS Database Install Schema & Seeds
-- Generated: 2026-08-02 14:56:20
-- Aligned with EERDiagram.puml and MySQL 8.0 Full-Text Search
-- ========================================================

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
SET time_zone = '+00:00';

-- Structure for table `agsc_address`
DROP TABLE IF EXISTS `agsc_address`;
CREATE TABLE `agsc_address` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `customer_id` bigint(20) NOT NULL,
  `firstname` varchar(80) DEFAULT NULL,
  `lastname` varchar(80) DEFAULT NULL,
  `company` varchar(60) DEFAULT NULL,
  `address_1` varchar(128) DEFAULT NULL,
  `number` int(11) NOT NULL DEFAULT 0 COMMENT 'Número',
  `address_2` varchar(128) DEFAULT NULL,
  `neighborhood` varchar(80) NOT NULL DEFAULT '' COMMENT 'Bairro',
  `city` varchar(128) DEFAULT NULL,
  `postcode` varchar(10) DEFAULT NULL,
  `country_id` bigint(20) NOT NULL,
  `zone_id` bigint(20) NOT NULL,
  `default` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`)
) ENGINE=InnoDB AUTO_INCREMENT=15693 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_address_format`
DROP TABLE IF EXISTS `agsc_address_format`;
CREATE TABLE `agsc_address_format` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) DEFAULT NULL,
  `address_format` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_antispam`
DROP TABLE IF EXISTS `agsc_antispam`;
CREATE TABLE `agsc_antispam` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `keyword` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `keyword` (`keyword`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_api_history`
DROP TABLE IF EXISTS `agsc_api_history`;
CREATE TABLE `agsc_api_history` (
  `api_history_id` int(11) NOT NULL AUTO_INCREMENT,
  `api_id` int(11) DEFAULT NULL,
  `call` varchar(32) DEFAULT NULL,
  `ip` varchar(40) DEFAULT NULL,
  `date_added` datetime DEFAULT NULL,
  PRIMARY KEY (`api_history_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Structure for table `agsc_api_ip`
DROP TABLE IF EXISTS `agsc_api_ip`;
CREATE TABLE `agsc_api_ip` (
  `api_ip_id` int(11) NOT NULL AUTO_INCREMENT,
  `api_id` int(11) DEFAULT NULL,
  `ip` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`api_ip_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Structure for table `agsc_article`
DROP TABLE IF EXISTS `agsc_article`;
CREATE TABLE `agsc_article` (
  `article_id` int(11) NOT NULL AUTO_INCREMENT,
  `topic_id` int(11) DEFAULT 0,
  `author` varchar(64) DEFAULT NULL,
  `rating` int(11) DEFAULT 0,
  `status` tinyint(1) DEFAULT 0,
  `date_added` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  PRIMARY KEY (`article_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Structure for table `agsc_article_description`
DROP TABLE IF EXISTS `agsc_article_description`;
CREATE TABLE `agsc_article_description` (
  `article_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `tag` text DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` varchar(255) DEFAULT NULL,
  `meta_keyword` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`article_id`,`language_id`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Structure for table `agsc_article_to_store`
DROP TABLE IF EXISTS `agsc_article_to_store`;
CREATE TABLE `agsc_article_to_store` (
  `article_id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`article_id`,`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Structure for table `agsc_attribute`
DROP TABLE IF EXISTS `agsc_attribute`;
CREATE TABLE `agsc_attribute` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `attribute_group_id` bigint(20) NOT NULL,
  `sort_order` int(3) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_attribute_description`
DROP TABLE IF EXISTS `agsc_attribute_description`;
CREATE TABLE `agsc_attribute_description` (
  `attribute_id` bigint(20) NOT NULL,
  `language_id` bigint(20) NOT NULL,
  `name` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`attribute_id`,`language_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_attribute_group`
DROP TABLE IF EXISTS `agsc_attribute_group`;
CREATE TABLE `agsc_attribute_group` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `sort_order` int(3) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_attribute_group_description`
DROP TABLE IF EXISTS `agsc_attribute_group_description`;
CREATE TABLE `agsc_attribute_group_description` (
  `attribute_group_id` bigint(20) NOT NULL,
  `language_id` bigint(20) NOT NULL,
  `name` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`attribute_group_id`,`language_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_brand`
DROP TABLE IF EXISTS `agsc_brand`;
CREATE TABLE `agsc_brand` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=318 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_cart`
DROP TABLE IF EXISTS `agsc_cart`;
CREATE TABLE `agsc_cart` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `store_id` bigint(20) NOT NULL,
  `customer_id` bigint(20) NOT NULL,
  `session_id` varchar(32) DEFAULT NULL,
  `product_id` bigint(20) NOT NULL,
  `subscription_plan_id` bigint(20) NOT NULL,
  `option` text DEFAULT NULL,
  `quantity` int(5) DEFAULT NULL,
  `override` text DEFAULT NULL,
  `price` decimal(15,4) DEFAULT NULL,
  `date_added` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cart_id` (`customer_id`,`session_id`,`product_id`,`subscription_plan_id`)
) ENGINE=InnoDB AUTO_INCREMENT=252 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_category`
DROP TABLE IF EXISTS `agsc_category`;
CREATE TABLE `agsc_category` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `image` varchar(255) DEFAULT NULL,
  `parent_id` bigint(20) DEFAULT NULL,
  `sort_order` int(3) DEFAULT 0,
  `status` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`),
  CONSTRAINT `fk_category_parent` FOREIGN KEY (`parent_id`) REFERENCES `agsc_category` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=116 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_category_description`
DROP TABLE IF EXISTS `agsc_category_description`;
CREATE TABLE `agsc_category_description` (
  `category_id` bigint(20) NOT NULL,
  `language_id` bigint(20) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` varchar(255) DEFAULT NULL,
  `meta_keyword` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`category_id`,`language_id`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_category_filter`
DROP TABLE IF EXISTS `agsc_category_filter`;
CREATE TABLE `agsc_category_filter` (
  `category_id` bigint(20) NOT NULL,
  `filter_id` bigint(20) NOT NULL,
  PRIMARY KEY (`category_id`,`filter_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_category_path`
DROP TABLE IF EXISTS `agsc_category_path`;
CREATE TABLE `agsc_category_path` (
  `category_id` bigint(20) NOT NULL,
  `path_id` bigint(20) NOT NULL,
  `level` int(11) DEFAULT NULL,
  PRIMARY KEY (`category_id`,`path_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_category_to_layout`
DROP TABLE IF EXISTS `agsc_category_to_layout`;
CREATE TABLE `agsc_category_to_layout` (
  `category_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `store_id` bigint(20) NOT NULL,
  `layout_id` bigint(20) NOT NULL,
  PRIMARY KEY (`category_id`,`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_category_to_store`
DROP TABLE IF EXISTS `agsc_category_to_store`;
CREATE TABLE `agsc_category_to_store` (
  `category_id` bigint(20) NOT NULL,
  `store_id` bigint(20) NOT NULL,
  PRIMARY KEY (`category_id`,`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_contact`
DROP TABLE IF EXISTS `agsc_contact`;
CREATE TABLE `agsc_contact` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `email` varchar(120) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_coupon`
DROP TABLE IF EXISTS `agsc_coupon`;
CREATE TABLE `agsc_coupon` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) DEFAULT NULL,
  `code` varchar(20) DEFAULT NULL,
  `type` char(1) DEFAULT NULL,
  `discount` decimal(15,4) DEFAULT NULL,
  `logged` tinyint(1) DEFAULT 0,
  `shipping` tinyint(1) DEFAULT 0,
  `total` decimal(15,4) DEFAULT NULL,
  `date_start` date DEFAULT NULL,
  `date_end` date DEFAULT NULL,
  `uses_total` int(11) DEFAULT 0,
  `uses_customer` int(11) DEFAULT 0,
  `status` tinyint(1) DEFAULT 0,
  `date_added` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_coupon_history`
DROP TABLE IF EXISTS `agsc_coupon_history`;
CREATE TABLE `agsc_coupon_history` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `coupon_id` bigint(20) NOT NULL,
  `order_id` bigint(20) NOT NULL,
  `customer_id` bigint(20) NOT NULL,
  `amount` decimal(15,4) DEFAULT NULL,
  `date_added` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_coupon_product`
DROP TABLE IF EXISTS `agsc_coupon_product`;
CREATE TABLE `agsc_coupon_product` (
  `coupon_product_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `coupon_id` bigint(20) NOT NULL,
  `product_id` bigint(20) NOT NULL,
  PRIMARY KEY (`coupon_product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_cron`
DROP TABLE IF EXISTS `agsc_cron`;
CREATE TABLE `agsc_cron` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `code` varchar(128) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `cycle` varchar(12) DEFAULT NULL,
  `action` text DEFAULT NULL,
  `status` tinyint(1) DEFAULT 0,
  `date_added` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_currency`
DROP TABLE IF EXISTS `agsc_currency`;
CREATE TABLE `agsc_currency` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `title` varchar(32) DEFAULT NULL,
  `code` varchar(3) DEFAULT NULL,
  `symbol_left` varchar(12) DEFAULT NULL,
  `symbol_right` varchar(12) DEFAULT NULL,
  `decimal_place` int(1) DEFAULT 2,
  `value` double(15,8) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 0,
  `date_modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_customer`
DROP TABLE IF EXISTS `agsc_customer`;
CREATE TABLE `agsc_customer` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `customer_group_id` bigint(20) NOT NULL,
  `store_id` bigint(20) NOT NULL,
  `language_id` bigint(20) NOT NULL,
  `address_id` int(11) NOT NULL DEFAULT 0,
  `firstname` varchar(80) DEFAULT NULL,
  `lastname` varchar(80) DEFAULT NULL,
  `email` varchar(96) DEFAULT NULL,
  `telephone` varchar(32) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `custom_field` text DEFAULT NULL,
  `newsletter` tinyint(1) DEFAULT 0,
  `ip` varchar(40) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 0,
  `safe` tinyint(1) DEFAULT 0,
  `commenter` tinyint(1) DEFAULT 0,
  `token` text DEFAULT NULL,
  `code` varchar(40) DEFAULT NULL,
  `date_added` datetime DEFAULT NULL,
  `cpf_cnpj` varchar(14) NOT NULL DEFAULT '' COMMENT 'CPF/CNPJ 	',
  `persontype` varchar(1) NOT NULL DEFAULT 'F' COMMENT 'Tipo de Pessoa',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_email_per_store` (`email`,`store_id`),
  KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=16693 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_customer_addresses`
DROP TABLE IF EXISTS `agsc_customer_addresses`;
CREATE TABLE `agsc_customer_addresses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` bigint(20) unsigned NOT NULL,
  `country_id` bigint(20) unsigned NOT NULL,
  `zone_id` bigint(20) unsigned NOT NULL,
  `city_id` bigint(20) unsigned NOT NULL,
  `postal_code` varchar(20) NOT NULL,
  `street` varchar(255) NOT NULL,
  `number` varchar(20) NOT NULL,
  `complement` varchar(100) DEFAULT NULL,
  `district` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `zone_id` (`zone_id`),
  KEY `city_id` (`city_id`),
  KEY `idx_shipping_lookup` (`country_id`,`postal_code`),
  CONSTRAINT `agsc_customer_addresses_ibfk_1` FOREIGN KEY (`country_id`) REFERENCES `agsc_geo_countries` (`id`),
  CONSTRAINT `agsc_customer_addresses_ibfk_2` FOREIGN KEY (`zone_id`) REFERENCES `agsc_geo_zones` (`id`),
  CONSTRAINT `agsc_customer_addresses_ibfk_3` FOREIGN KEY (`city_id`) REFERENCES `agsc_geo_cities` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_customer_affiliate`
DROP TABLE IF EXISTS `agsc_customer_affiliate`;
CREATE TABLE `agsc_customer_affiliate` (
  `customer_id` bigint(20) NOT NULL,
  `company` varchar(60) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `tracking` varchar(64) DEFAULT NULL,
  `balance` decimal(15,4) DEFAULT NULL,
  `commission` decimal(4,2) DEFAULT 0.00,
  `tax` varchar(64) DEFAULT NULL,
  `payment_method` varchar(6) DEFAULT NULL,
  `cheque` varchar(100) DEFAULT NULL,
  `paypal` varchar(64) DEFAULT NULL,
  `bank_name` varchar(64) DEFAULT NULL,
  `bank_branch_number` varchar(64) DEFAULT NULL,
  `bank_swift_code` varchar(64) DEFAULT NULL,
  `bank_account_name` varchar(64) DEFAULT NULL,
  `bank_account_number` varchar(64) DEFAULT NULL,
  `custom_field` text DEFAULT NULL,
  `status` tinyint(1) DEFAULT 0,
  `date_added` datetime DEFAULT NULL,
  `cpf_cnpj` varchar(14) NOT NULL DEFAULT '' COMMENT 'CPF/CNPJ ',
  `persontype` varchar(1) NOT NULL COMMENT 'Tipo de Pessoa ',
  PRIMARY KEY (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_customer_approval`
DROP TABLE IF EXISTS `agsc_customer_approval`;
CREATE TABLE `agsc_customer_approval` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `customer_id` bigint(20) NOT NULL,
  `type` varchar(9) DEFAULT NULL,
  `date_added` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_customer_authorize`
DROP TABLE IF EXISTS `agsc_customer_authorize`;
CREATE TABLE `agsc_customer_authorize` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `customer_id` bigint(20) NOT NULL,
  `token` varchar(96) DEFAULT NULL,
  `total` int(1) DEFAULT 0,
  `ip` varchar(40) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 0,
  `date_added` datetime DEFAULT NULL,
  `date_expire` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_customer_group`
DROP TABLE IF EXISTS `agsc_customer_group`;
CREATE TABLE `agsc_customer_group` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `approval` int(1) DEFAULT 0,
  `sort_order` int(3) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_customer_group_description`
DROP TABLE IF EXISTS `agsc_customer_group_description`;
CREATE TABLE `agsc_customer_group_description` (
  `customer_group_id` bigint(20) NOT NULL,
  `language_id` bigint(20) NOT NULL,
  `name` varchar(32) DEFAULT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`customer_group_id`,`language_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_customer_history`
DROP TABLE IF EXISTS `agsc_customer_history`;
CREATE TABLE `agsc_customer_history` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `customer_id` bigint(20) NOT NULL,
  `comment` text DEFAULT NULL,
  `date_added` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_customer_login`
DROP TABLE IF EXISTS `agsc_customer_login`;
CREATE TABLE `agsc_customer_login` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `email` varchar(96) DEFAULT NULL,
  `ip` varchar(40) DEFAULT NULL,
  `total` int(4) DEFAULT 0,
  `date_added` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `email` (`email`),
  KEY `ip` (`ip`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_customer_online`
DROP TABLE IF EXISTS `agsc_customer_online`;
CREATE TABLE `agsc_customer_online` (
  `ip` varchar(40) NOT NULL,
  `customer_id` bigint(20) NOT NULL,
  `url` text DEFAULT NULL,
  `referer` text DEFAULT NULL,
  `date_added` datetime DEFAULT NULL,
  PRIMARY KEY (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_customer_reward`
DROP TABLE IF EXISTS `agsc_customer_reward`;
CREATE TABLE `agsc_customer_reward` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `customer_id` bigint(20) NOT NULL,
  `order_id` bigint(20) NOT NULL,
  `description` text DEFAULT NULL,
  `points` int(8) DEFAULT 0,
  `date_added` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_customer_token`
DROP TABLE IF EXISTS `agsc_customer_token`;
CREATE TABLE `agsc_customer_token` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `customer_id` bigint(20) NOT NULL,
  `code` text DEFAULT NULL,
  `type` varchar(10) DEFAULT NULL,
  `date_added` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_customer_transaction`
DROP TABLE IF EXISTS `agsc_customer_transaction`;
CREATE TABLE `agsc_customer_transaction` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `customer_id` bigint(20) NOT NULL,
  `order_id` bigint(20) NOT NULL,
  `description` text DEFAULT NULL,
  `amount` decimal(15,4) DEFAULT NULL,
  `date_added` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_customer_wishlist`
DROP TABLE IF EXISTS `agsc_customer_wishlist`;
CREATE TABLE `agsc_customer_wishlist` (
  `customer_id` bigint(20) NOT NULL,
  `store_id` bigint(20) NOT NULL,
  `product_id` bigint(20) NOT NULL,
  `date_added` datetime DEFAULT NULL,
  PRIMARY KEY (`customer_id`,`store_id`,`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_event`
DROP TABLE IF EXISTS `agsc_event`;
CREATE TABLE `agsc_event` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `code` varchar(128) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `trigger` text DEFAULT NULL,
  `action` text DEFAULT NULL,
  `status` tinyint(1) DEFAULT 0,
  `sort_order` int(3) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_extension`
DROP TABLE IF EXISTS `agsc_extension`;
CREATE TABLE `agsc_extension` (
  `extension_id` int(11) NOT NULL AUTO_INCREMENT,
  `extension` varchar(255) DEFAULT NULL,
  `type` varchar(32) DEFAULT NULL,
  `code` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`extension_id`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Structure for table `agsc_filter`
DROP TABLE IF EXISTS `agsc_filter`;
CREATE TABLE `agsc_filter` (
  `filter_id` int(11) NOT NULL AUTO_INCREMENT,
  `filter_group_id` int(11) DEFAULT NULL,
  `sort_order` int(3) DEFAULT 0,
  PRIMARY KEY (`filter_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Structure for table `agsc_filter_description`
DROP TABLE IF EXISTS `agsc_filter_description`;
CREATE TABLE `agsc_filter_description` (
  `filter_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `name` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`filter_id`,`language_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Structure for table `agsc_filter_group`
DROP TABLE IF EXISTS `agsc_filter_group`;
CREATE TABLE `agsc_filter_group` (
  `filter_group_id` int(11) NOT NULL AUTO_INCREMENT,
  `sort_order` int(3) DEFAULT 0,
  PRIMARY KEY (`filter_group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Structure for table `agsc_gdpr`
DROP TABLE IF EXISTS `agsc_gdpr`;
CREATE TABLE `agsc_gdpr` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `store_id` bigint(20) NOT NULL,
  `language_id` bigint(20) NOT NULL,
  `code` varchar(40) DEFAULT NULL,
  `email` varchar(96) DEFAULT NULL,
  `action` varchar(6) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 0,
  `date_added` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_geo_cities`
DROP TABLE IF EXISTS `agsc_geo_cities`;
CREATE TABLE `agsc_geo_cities` (
  `id` bigint(20) unsigned NOT NULL,
  `zone_id` bigint(20) unsigned NOT NULL,
  `name` varchar(150) NOT NULL,
  `is_served` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `zone_id` (`zone_id`),
  CONSTRAINT `agsc_geo_cities_ibfk_1` FOREIGN KEY (`zone_id`) REFERENCES `agsc_geo_zones` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_geo_countries`
DROP TABLE IF EXISTS `agsc_geo_countries`;
CREATE TABLE `agsc_geo_countries` (
  `id` bigint(20) unsigned NOT NULL,
  `name` varchar(100) NOT NULL,
  `iso_alpha2` char(2) NOT NULL,
  `iso_alpha3` char(3) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `iso_alpha2` (`iso_alpha2`),
  UNIQUE KEY `iso_alpha3` (`iso_alpha3`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_geo_zone`
DROP TABLE IF EXISTS `agsc_geo_zone`;
CREATE TABLE `agsc_geo_zone` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_geo_zones`
DROP TABLE IF EXISTS `agsc_geo_zones`;
CREATE TABLE `agsc_geo_zones` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `country_id` bigint(20) unsigned NOT NULL,
  `iso_code` varchar(10) NOT NULL,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_country_zone_iso` (`country_id`,`iso_code`),
  CONSTRAINT `agsc_geo_zones_ibfk_1` FOREIGN KEY (`country_id`) REFERENCES `agsc_geo_countries` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=108 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_identifier`
DROP TABLE IF EXISTS `agsc_identifier`;
CREATE TABLE `agsc_identifier` (
  `identifier_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) DEFAULT NULL,
  `code` varchar(48) DEFAULT NULL,
  `validation` varchar(255) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`identifier_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Structure for table `agsc_information`
DROP TABLE IF EXISTS `agsc_information`;
CREATE TABLE `agsc_information` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `sort_order` int(3) DEFAULT 0,
  `status` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_information_description`
DROP TABLE IF EXISTS `agsc_information_description`;
CREATE TABLE `agsc_information_description` (
  `information_id` bigint(20) NOT NULL,
  `language_id` bigint(20) NOT NULL,
  `title` varchar(64) DEFAULT NULL,
  `description` mediumtext DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` varchar(255) DEFAULT NULL,
  `meta_keyword` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`information_id`,`language_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_information_to_layout`
DROP TABLE IF EXISTS `agsc_information_to_layout`;
CREATE TABLE `agsc_information_to_layout` (
  `information_id` bigint(20) NOT NULL,
  `store_id` bigint(20) NOT NULL,
  `layout_id` bigint(20) NOT NULL,
  PRIMARY KEY (`information_id`,`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_information_to_store`
DROP TABLE IF EXISTS `agsc_information_to_store`;
CREATE TABLE `agsc_information_to_store` (
  `information_id` bigint(20) NOT NULL,
  `store_id` bigint(20) NOT NULL,
  PRIMARY KEY (`information_id`,`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_language`
DROP TABLE IF EXISTS `agsc_language`;
CREATE TABLE `agsc_language` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) DEFAULT NULL,
  `code` varchar(5) DEFAULT NULL,
  `locale` varchar(255) DEFAULT NULL,
  `image` varchar(80) NOT NULL,
  `extension` varchar(255) DEFAULT NULL,
  `sort_order` int(3) DEFAULT 0,
  `status` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_layout`
DROP TABLE IF EXISTS `agsc_layout`;
CREATE TABLE `agsc_layout` (
  `layout_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`layout_id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Structure for table `agsc_layout_module`
DROP TABLE IF EXISTS `agsc_layout_module`;
CREATE TABLE `agsc_layout_module` (
  `layout_module_id` int(11) NOT NULL AUTO_INCREMENT,
  `layout_id` int(11) DEFAULT 0,
  `code` varchar(64) DEFAULT NULL,
  `position` varchar(14) DEFAULT NULL,
  `sort_order` int(3) DEFAULT 0,
  PRIMARY KEY (`layout_module_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Structure for table `agsc_layout_route`
DROP TABLE IF EXISTS `agsc_layout_route`;
CREATE TABLE `agsc_layout_route` (
  `layout_route_id` int(11) NOT NULL AUTO_INCREMENT,
  `layout_id` int(11) DEFAULT NULL,
  `store_id` int(11) DEFAULT 0,
  `route` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`layout_route_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Structure for table `agsc_length_class`
DROP TABLE IF EXISTS `agsc_length_class`;
CREATE TABLE `agsc_length_class` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `value` decimal(15,8) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_length_class_description`
DROP TABLE IF EXISTS `agsc_length_class_description`;
CREATE TABLE `agsc_length_class_description` (
  `length_class_id` bigint(20) NOT NULL,
  `language_id` bigint(20) NOT NULL,
  `title` varchar(32) DEFAULT NULL,
  `unit` varchar(4) DEFAULT NULL,
  PRIMARY KEY (`length_class_id`,`language_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_location`
DROP TABLE IF EXISTS `agsc_location`;
CREATE TABLE `agsc_location` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `telephone` varchar(32) DEFAULT NULL,
  `geocode` varchar(32) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `open` text DEFAULT NULL,
  `comment` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_manufacturer`
DROP TABLE IF EXISTS `agsc_manufacturer`;
CREATE TABLE `agsc_manufacturer` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `sort_order` int(3) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=318 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_manufacturer_to_layout`
DROP TABLE IF EXISTS `agsc_manufacturer_to_layout`;
CREATE TABLE `agsc_manufacturer_to_layout` (
  `manufacturer_id` bigint(20) NOT NULL,
  `store_id` bigint(20) NOT NULL,
  `layout_id` bigint(20) NOT NULL,
  PRIMARY KEY (`manufacturer_id`,`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_manufacturer_to_store`
DROP TABLE IF EXISTS `agsc_manufacturer_to_store`;
CREATE TABLE `agsc_manufacturer_to_store` (
  `manufacturer_id` bigint(20) NOT NULL,
  `store_id` bigint(20) NOT NULL,
  PRIMARY KEY (`manufacturer_id`,`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_modification`
DROP TABLE IF EXISTS `agsc_modification`;
CREATE TABLE `agsc_modification` (
  `modification_id` int(11) NOT NULL AUTO_INCREMENT,
  `extension_install_id` int(11) NOT NULL,
  `name` varchar(64) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `code` varchar(64) DEFAULT NULL,
  `author` varchar(64) DEFAULT NULL,
  `version` varchar(32) DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  `xml` mediumtext DEFAULT NULL,
  `status` tinyint(1) DEFAULT 0,
  `date_added` datetime DEFAULT NULL,
  PRIMARY KEY (`modification_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Structure for table `agsc_module`
DROP TABLE IF EXISTS `agsc_module`;
CREATE TABLE `agsc_module` (
  `module_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) DEFAULT NULL,
  `code` varchar(64) DEFAULT NULL,
  `setting` text DEFAULT NULL,
  PRIMARY KEY (`module_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Structure for table `agsc_option`
DROP TABLE IF EXISTS `agsc_option`;
CREATE TABLE `agsc_option` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `type` varchar(32) DEFAULT NULL,
  `validation` varchar(255) DEFAULT NULL,
  `sort_order` int(3) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_option_description`
DROP TABLE IF EXISTS `agsc_option_description`;
CREATE TABLE `agsc_option_description` (
  `option_id` bigint(20) NOT NULL,
  `language_id` bigint(20) NOT NULL,
  `name` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`option_id`,`language_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_option_value`
DROP TABLE IF EXISTS `agsc_option_value`;
CREATE TABLE `agsc_option_value` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `option_id` bigint(20) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `sort_order` int(3) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_option_value_description`
DROP TABLE IF EXISTS `agsc_option_value_description`;
CREATE TABLE `agsc_option_value_description` (
  `option_value_id` bigint(20) NOT NULL,
  `language_id` bigint(20) NOT NULL,
  `option_id` bigint(20) NOT NULL,
  `name` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`option_value_id`,`language_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_order`
DROP TABLE IF EXISTS `agsc_order`;
CREATE TABLE `agsc_order` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `subscription_id` bigint(20) NOT NULL,
  `invoice_no` int(11) DEFAULT 0,
  `invoice_prefix` varchar(26) DEFAULT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `store_id` bigint(20) NOT NULL,
  `store_name` varchar(64) DEFAULT NULL,
  `store_url` varchar(255) DEFAULT NULL,
  `customer_id` bigint(20) NOT NULL,
  `customer_group_id` bigint(20) NOT NULL,
  `firstname` varchar(32) DEFAULT NULL,
  `lastname` varchar(32) DEFAULT NULL,
  `email` varchar(96) DEFAULT NULL,
  `telephone` varchar(32) DEFAULT NULL,
  `cpf_cnpj` varchar(14) NOT NULL DEFAULT '' COMMENT 'CPF/CNPJ',
  `persontype` varchar(1) NOT NULL DEFAULT 'F' COMMENT 'Pessoa física ou jurídica\r\n',
  `custom_field` text DEFAULT NULL,
  `payment_address_id` bigint(20) NOT NULL,
  `payment_firstname` varchar(32) DEFAULT NULL,
  `payment_lastname` varchar(32) DEFAULT NULL,
  `payment_company` varchar(60) DEFAULT NULL,
  `payment_street` varchar(128) DEFAULT NULL,
  `payment_number` int(11) NOT NULL COMMENT 'Número',
  `payment_complement` varchar(128) DEFAULT NULL,
  `payment_district` varchar(60) NOT NULL,
  `payment_city` varchar(128) DEFAULT NULL,
  `payment_postcode` varchar(10) DEFAULT NULL,
  `payment_country` varchar(128) DEFAULT NULL,
  `payment_country_id` bigint(20) NOT NULL,
  `payment_zone` varchar(128) DEFAULT NULL,
  `payment_zone_id` bigint(20) NOT NULL,
  `payment_address_format` text DEFAULT NULL,
  `payment_custom_field` text DEFAULT NULL,
  `payment_method` text DEFAULT NULL,
  `shipping_address_id` bigint(20) NOT NULL,
  `shipping_firstname` varchar(32) DEFAULT NULL,
  `shipping_lastname` varchar(32) DEFAULT NULL,
  `shipping_company` varchar(60) DEFAULT NULL,
  `shipping_street` varchar(128) DEFAULT NULL,
  `shipping_number` int(11) NOT NULL COMMENT 'Número',
  `shipping_complement` varchar(128) DEFAULT NULL,
  `shipping_district` varchar(60) NOT NULL,
  `shipping_city` varchar(128) DEFAULT NULL,
  `shipping_postcode` varchar(10) DEFAULT NULL,
  `shipping_country` varchar(128) DEFAULT NULL,
  `shipping_country_id` bigint(20) NOT NULL,
  `shipping_zone` varchar(128) DEFAULT NULL,
  `shipping_zone_id` bigint(20) NOT NULL,
  `shipping_address_format` text DEFAULT NULL,
  `shipping_custom_field` text DEFAULT NULL,
  `shipping_method` text DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `total` decimal(15,4) DEFAULT 0.0000,
  `order_status_id` bigint(20) NOT NULL,
  `affiliate_id` bigint(20) NOT NULL,
  `commission` decimal(15,4) DEFAULT NULL,
  `marketing_id` bigint(20) NOT NULL,
  `tracking` varchar(64) DEFAULT NULL,
  `language_id` bigint(20) NOT NULL,
  `language_code` varchar(5) DEFAULT NULL,
  `currency_id` bigint(20) NOT NULL,
  `currency_code` varchar(3) DEFAULT NULL,
  `currency_value` decimal(15,8) DEFAULT 1.00000000,
  `ip` varchar(40) DEFAULT NULL,
  `forwarded_ip` varchar(40) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `accept_language` varchar(255) DEFAULT NULL,
  `date_added` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Structure for table `agsc_order_history`
DROP TABLE IF EXISTS `agsc_order_history`;
CREATE TABLE `agsc_order_history` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) NOT NULL,
  `order_status_id` bigint(20) NOT NULL,
  `notify` tinyint(1) DEFAULT 0,
  `comment` text DEFAULT NULL,
  `date_added` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_order_option`
DROP TABLE IF EXISTS `agsc_order_option`;
CREATE TABLE `agsc_order_option` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) NOT NULL,
  `order_product_id` bigint(20) NOT NULL,
  `product_option_id` bigint(20) NOT NULL,
  `product_option_value_id` bigint(20) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `value` text DEFAULT NULL,
  `type` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_order_product`
DROP TABLE IF EXISTS `agsc_order_product`;
CREATE TABLE `agsc_order_product` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) NOT NULL,
  `product_id` bigint(20) NOT NULL,
  `master_id` bigint(20) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `model` varchar(64) DEFAULT NULL,
  `quantity` int(4) DEFAULT 1,
  `price` decimal(15,4) DEFAULT 0.0000,
  `total` decimal(15,4) DEFAULT 0.0000,
  `tax` decimal(15,4) DEFAULT 0.0000,
  `reward` int(8) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=93 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_order_status`
DROP TABLE IF EXISTS `agsc_order_status`;
CREATE TABLE `agsc_order_status` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `language_id` bigint(20) NOT NULL,
  `name` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`,`language_id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_order_subscription`
DROP TABLE IF EXISTS `agsc_order_subscription`;
CREATE TABLE `agsc_order_subscription` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `order_product_id` bigint(20) NOT NULL,
  `order_id` bigint(20) NOT NULL,
  `product_id` bigint(20) NOT NULL,
  `quantity` int(4) DEFAULT 1,
  `subscription_plan_id` bigint(20) NOT NULL,
  `trial_price` decimal(10,4) DEFAULT NULL,
  `trial_tax` decimal(15,4) DEFAULT NULL,
  `trial_frequency` enum('day','week','semi_month','month','year') DEFAULT NULL,
  `trial_cycle` smallint(6) DEFAULT NULL,
  `trial_duration` smallint(6) DEFAULT NULL,
  `trial_status` tinyint(1) DEFAULT 0,
  `price` decimal(10,4) DEFAULT NULL,
  `tax` decimal(15,4) DEFAULT NULL,
  `frequency` enum('day','week','semi_month','month','year') DEFAULT NULL,
  `cycle` smallint(6) DEFAULT 1,
  `duration` smallint(6) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_order_total`
DROP TABLE IF EXISTS `agsc_order_total`;
CREATE TABLE `agsc_order_total` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) NOT NULL,
  `extension` varchar(255) DEFAULT NULL,
  `code` varchar(32) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `value` decimal(15,4) DEFAULT 0.0000,
  `sort_order` int(3) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_product`
DROP TABLE IF EXISTS `agsc_product`;
CREATE TABLE `agsc_product` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `master_id` bigint(20) NOT NULL,
  `model` varchar(64) DEFAULT NULL,
  `sku` varchar(64) DEFAULT NULL COMMENT 'Unidade de Manutenção de Estoque',
  `upc` varchar(12) DEFAULT NULL COMMENT 'Código Universal de Produto',
  `ean` varchar(14) DEFAULT NULL,
  `jan` varchar(13) DEFAULT NULL,
  `isbn` varchar(17) DEFAULT NULL COMMENT 'Número Padrão Internacional de Livro',
  `mpn` varchar(64) DEFAULT NULL COMMENT 'Número de Peça do Fabricante',
  `location` varchar(128) DEFAULT NULL,
  `variant` text DEFAULT '',
  `override` text DEFAULT '',
  `quantity` int(4) DEFAULT 0,
  `stock_status_id` bigint(20) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `manufacturer_id` bigint(20) DEFAULT NULL,
  `shipping` tinyint(1) DEFAULT 1,
  `price` decimal(15,4) DEFAULT 0.0000,
  `points` int(8) DEFAULT 0,
  `tax_class_id` bigint(20) NOT NULL,
  `date_available` date DEFAULT NULL,
  `weight` decimal(15,8) DEFAULT 0.00000000,
  `weight_class_id` bigint(20) NOT NULL,
  `length` decimal(15,8) DEFAULT 0.00000000,
  `width` decimal(15,8) DEFAULT 0.00000000,
  `height` decimal(15,8) DEFAULT 0.00000000,
  `length_class_id` bigint(20) NOT NULL,
  `subtract` tinyint(1) DEFAULT 1,
  `minimum` int(11) DEFAULT 1,
  `rating` int(1) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0,
  `status` tinyint(1) DEFAULT 0,
  `date_added` datetime DEFAULT current_timestamp(),
  `date_modified` datetime DEFAULT current_timestamp(),
  `ncm` varchar(10) NOT NULL COMMENT 'Nomenclatura Comum do Mercosul',
  `cest` varchar(15) NOT NULL COMMENT 'Código Especificador da Substituição Tributária',
  PRIMARY KEY (`id`),
  KEY `fk_product_manufacturer` (`manufacturer_id`),
  CONSTRAINT `fk_product_manufacturer` FOREIGN KEY (`manufacturer_id`) REFERENCES `agsc_manufacturer` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=81728 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Structure for table `agsc_product_attribute`
DROP TABLE IF EXISTS `agsc_product_attribute`;
CREATE TABLE `agsc_product_attribute` (
  `product_id` bigint(20) NOT NULL,
  `attribute_id` bigint(20) NOT NULL,
  `language_id` bigint(20) NOT NULL,
  `text` text DEFAULT NULL,
  PRIMARY KEY (`product_id`,`attribute_id`,`language_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_product_code`
DROP TABLE IF EXISTS `agsc_product_code`;
CREATE TABLE `agsc_product_code` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) NOT NULL,
  `code` varchar(48) DEFAULT NULL,
  `value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_product_description`
DROP TABLE IF EXISTS `agsc_product_description`;
CREATE TABLE `agsc_product_description` (
  `product_id` bigint(20) NOT NULL,
  `language_id` bigint(20) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `tag` text DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` varchar(255) DEFAULT NULL,
  `meta_keyword` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`product_id`,`language_id`),
  KEY `name` (`name`),
  FULLTEXT KEY `idx_ft_product_search` (`name`,`description`,`tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_product_discount`
DROP TABLE IF EXISTS `agsc_product_discount`;
CREATE TABLE `agsc_product_discount` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) NOT NULL,
  `customer_group_id` bigint(20) NOT NULL,
  `quantity` int(4) DEFAULT 0,
  `priority` int(5) DEFAULT 1,
  `price` decimal(15,4) DEFAULT 0.0000,
  `type` char(1) DEFAULT 'P',
  `special` tinyint(1) DEFAULT 0,
  `date_start` date DEFAULT NULL,
  `date_end` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=441 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_product_filter`
DROP TABLE IF EXISTS `agsc_product_filter`;
CREATE TABLE `agsc_product_filter` (
  `product_id` bigint(20) NOT NULL,
  `filter_id` bigint(20) NOT NULL,
  PRIMARY KEY (`product_id`,`filter_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_product_image`
DROP TABLE IF EXISTS `agsc_product_image`;
CREATE TABLE `agsc_product_image` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `sort_order` int(3) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2352 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_product_option`
DROP TABLE IF EXISTS `agsc_product_option`;
CREATE TABLE `agsc_product_option` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) NOT NULL,
  `option_id` bigint(20) NOT NULL,
  `value` text DEFAULT NULL,
  `required` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=227 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_product_option_value`
DROP TABLE IF EXISTS `agsc_product_option_value`;
CREATE TABLE `agsc_product_option_value` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `product_option_id` bigint(20) NOT NULL,
  `product_id` bigint(20) NOT NULL,
  `option_id` bigint(20) NOT NULL,
  `option_value_id` bigint(20) NOT NULL,
  `quantity` int(3) DEFAULT 0,
  `subtract` tinyint(1) DEFAULT 0,
  `price` decimal(15,4) DEFAULT NULL,
  `price_prefix` varchar(1) DEFAULT NULL,
  `points` int(8) DEFAULT 0,
  `points_prefix` varchar(1) DEFAULT NULL,
  `weight` decimal(15,8) DEFAULT NULL,
  `weight_prefix` varchar(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_product_related`
DROP TABLE IF EXISTS `agsc_product_related`;
CREATE TABLE `agsc_product_related` (
  `product_id` bigint(20) NOT NULL,
  `related_id` bigint(20) NOT NULL,
  PRIMARY KEY (`product_id`,`related_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_product_report`
DROP TABLE IF EXISTS `agsc_product_report`;
CREATE TABLE `agsc_product_report` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) NOT NULL,
  `store_id` bigint(20) NOT NULL,
  `ip` varchar(40) DEFAULT NULL,
  `country` varchar(2) DEFAULT NULL,
  `date_added` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_product_return`
DROP TABLE IF EXISTS `agsc_product_return`;
CREATE TABLE `agsc_product_return` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) NOT NULL DEFAULT 0,
  `customer_id` bigint(20) NOT NULL DEFAULT 0,
  `firstname` varchar(32) NOT NULL,
  `lastname` varchar(32) NOT NULL,
  `email` varchar(96) NOT NULL,
  `telephone` varchar(32) NOT NULL,
  `cpf_cnpj` varchar(14) NOT NULL DEFAULT '',
  `persontype` varchar(1) NOT NULL DEFAULT 'F',
  `product_id` bigint(20) NOT NULL DEFAULT 0,
  `product` varchar(255) NOT NULL,
  `model` varchar(64) NOT NULL,
  `quantity` int(4) NOT NULL DEFAULT 0,
  `opened` tinyint(1) NOT NULL DEFAULT 0,
  `return_reason_id` bigint(20) NOT NULL DEFAULT 0,
  `return_action_id` bigint(20) NOT NULL DEFAULT 0,
  `return_status_id` bigint(20) NOT NULL DEFAULT 0,
  `comment` text NOT NULL,
  `date_ordered` date NOT NULL,
  `date_added` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  `version` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_product_reward`
DROP TABLE IF EXISTS `agsc_product_reward`;
CREATE TABLE `agsc_product_reward` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) NOT NULL,
  `customer_group_id` bigint(20) NOT NULL,
  `points` int(8) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=546 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_product_subscription`
DROP TABLE IF EXISTS `agsc_product_subscription`;
CREATE TABLE `agsc_product_subscription` (
  `product_id` bigint(20) NOT NULL,
  `subscription_plan_id` bigint(20) NOT NULL,
  `customer_group_id` bigint(20) NOT NULL,
  `trial_price` decimal(10,4) DEFAULT NULL,
  `price` decimal(10,4) DEFAULT NULL,
  PRIMARY KEY (`product_id`,`subscription_plan_id`,`customer_group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_product_to_category`
DROP TABLE IF EXISTS `agsc_product_to_category`;
CREATE TABLE `agsc_product_to_category` (
  `product_id` bigint(20) NOT NULL,
  `category_id` bigint(20) NOT NULL,
  PRIMARY KEY (`product_id`,`category_id`),
  KEY `category_id` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_product_to_layout`
DROP TABLE IF EXISTS `agsc_product_to_layout`;
CREATE TABLE `agsc_product_to_layout` (
  `product_id` bigint(20) NOT NULL,
  `store_id` bigint(20) NOT NULL,
  `layout_id` bigint(20) NOT NULL,
  PRIMARY KEY (`product_id`,`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_product_to_store`
DROP TABLE IF EXISTS `agsc_product_to_store`;
CREATE TABLE `agsc_product_to_store` (
  `product_id` bigint(20) NOT NULL,
  `store_id` bigint(20) NOT NULL,
  PRIMARY KEY (`product_id`,`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_product_viewed`
DROP TABLE IF EXISTS `agsc_product_viewed`;
CREATE TABLE `agsc_product_viewed` (
  `product_id` bigint(20) NOT NULL,
  `viewed` int(11) DEFAULT 0,
  PRIMARY KEY (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_return`
DROP TABLE IF EXISTS `agsc_return`;
CREATE TABLE `agsc_return` (
  `return_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) DEFAULT 0,
  `customer_id` int(11) DEFAULT 0,
  `firstname` varchar(32) DEFAULT NULL,
  `lastname` varchar(32) DEFAULT NULL,
  `email` varchar(96) DEFAULT NULL,
  `telephone` varchar(32) DEFAULT NULL,
  `product_id` int(11) DEFAULT 0,
  `product` varchar(255) DEFAULT NULL,
  `model` varchar(64) DEFAULT NULL,
  `quantity` int(4) DEFAULT 0,
  `opened` tinyint(1) DEFAULT 0,
  `return_reason_id` int(11) DEFAULT 0,
  `return_action_id` int(11) DEFAULT 0,
  `return_status_id` int(11) DEFAULT 0,
  `comment` text DEFAULT NULL,
  `date_ordered` date DEFAULT NULL,
  `date_added` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  PRIMARY KEY (`return_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Structure for table `agsc_return_action`
DROP TABLE IF EXISTS `agsc_return_action`;
CREATE TABLE `agsc_return_action` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `language_id` bigint(20) NOT NULL,
  `name` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`,`language_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_return_history`
DROP TABLE IF EXISTS `agsc_return_history`;
CREATE TABLE `agsc_return_history` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `return_id` bigint(20) NOT NULL,
  `return_status_id` bigint(20) NOT NULL,
  `notify` tinyint(1) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `date_added` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_return_reason`
DROP TABLE IF EXISTS `agsc_return_reason`;
CREATE TABLE `agsc_return_reason` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `language_id` bigint(20) NOT NULL,
  `name` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`,`language_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_return_status`
DROP TABLE IF EXISTS `agsc_return_status`;
CREATE TABLE `agsc_return_status` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `language_id` bigint(20) NOT NULL,
  `name` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`,`language_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_review`
DROP TABLE IF EXISTS `agsc_review`;
CREATE TABLE `agsc_review` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) NOT NULL,
  `customer_id` bigint(20) NOT NULL,
  `author` varchar(64) DEFAULT NULL,
  `text` text DEFAULT NULL,
  `rating` int(1) DEFAULT 0,
  `status` tinyint(1) DEFAULT 0,
  `date_added` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_seo_url`
DROP TABLE IF EXISTS `agsc_seo_url`;
CREATE TABLE `agsc_seo_url` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `store_id` bigint(20) NOT NULL,
  `language_id` bigint(20) NOT NULL,
  `key` varchar(64) DEFAULT NULL,
  `value` varchar(255) DEFAULT NULL,
  `keyword` varchar(768) DEFAULT NULL,
  `sort_order` int(3) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `store` (`store_id`),
  KEY `language` (`language_id`),
  KEY `keyword` (`keyword`),
  KEY `query` (`key`,`value`)
) ENGINE=InnoDB AUTO_INCREMENT=127 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_session`
DROP TABLE IF EXISTS `agsc_session`;
CREATE TABLE `agsc_session` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `token_session` varchar(255) NOT NULL COMMENT 'token',
  `customer_id` bigint(20) NOT NULL,
  `data` text DEFAULT NULL,
  `expire_at` datetime NOT NULL,
  `user_agent` varchar(255) NOT NULL,
  `ip` varchar(40) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_session_id` (`token_session`),
  KEY `expire` (`expire_at`)
) ENGINE=InnoDB AUTO_INCREMENT=7864 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_setting`
DROP TABLE IF EXISTS `agsc_setting`;
CREATE TABLE `agsc_setting` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `store_id` bigint(20) NOT NULL,
  `code` varchar(128) DEFAULT NULL,
  `key` varchar(128) DEFAULT NULL,
  `value` text DEFAULT NULL,
  `serialized` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `fk_setting_store` (`store_id`),
  CONSTRAINT `fk_setting_store` FOREIGN KEY (`store_id`) REFERENCES `agsc_store` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4388 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_startup`
DROP TABLE IF EXISTS `agsc_startup`;
CREATE TABLE `agsc_startup` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `description` text DEFAULT NULL,
  `code` varchar(64) DEFAULT NULL,
  `action` text DEFAULT NULL,
  `status` tinyint(1) DEFAULT 0,
  `sort_order` int(3) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_statistics`
DROP TABLE IF EXISTS `agsc_statistics`;
CREATE TABLE `agsc_statistics` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `code` varchar(64) DEFAULT NULL,
  `value` decimal(15,4) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_stock_status`
DROP TABLE IF EXISTS `agsc_stock_status`;
CREATE TABLE `agsc_stock_status` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `language_id` bigint(20) NOT NULL,
  `name` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`,`language_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_store`
DROP TABLE IF EXISTS `agsc_store`;
CREATE TABLE `agsc_store` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_subscription`
DROP TABLE IF EXISTS `agsc_subscription`;
CREATE TABLE `agsc_subscription` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) NOT NULL,
  `store_id` bigint(20) NOT NULL,
  `customer_id` bigint(20) NOT NULL,
  `payment_address_id` bigint(20) NOT NULL,
  `payment_method` text DEFAULT NULL,
  `shipping_address_id` bigint(20) NOT NULL,
  `shipping_method` text DEFAULT NULL,
  `subscription_plan_id` bigint(20) NOT NULL,
  `trial_price` decimal(10,4) DEFAULT NULL,
  `trial_tax` decimal(10,4) DEFAULT NULL,
  `trial_frequency` enum('day','week','semi_month','month','year') DEFAULT NULL,
  `trial_cycle` smallint(6) DEFAULT 0,
  `trial_duration` smallint(6) DEFAULT 0,
  `trial_remaining` smallint(6) DEFAULT 0,
  `trial_status` tinyint(1) DEFAULT 0,
  `price` decimal(10,4) DEFAULT NULL,
  `tax` decimal(10,4) DEFAULT NULL,
  `frequency` enum('day','week','semi_month','month','year') DEFAULT NULL,
  `cycle` smallint(6) DEFAULT 0,
  `duration` smallint(6) DEFAULT 0,
  `remaining` smallint(6) DEFAULT 0,
  `date_next` datetime DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `subscription_status_id` bigint(20) NOT NULL,
  `language` varchar(5) DEFAULT NULL,
  `currency` varchar(3) DEFAULT NULL,
  `date_added` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_subscription_history`
DROP TABLE IF EXISTS `agsc_subscription_history`;
CREATE TABLE `agsc_subscription_history` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `subscription_id` bigint(20) NOT NULL,
  `subscription_status_id` bigint(20) NOT NULL,
  `notify` tinyint(1) DEFAULT 0,
  `comment` text DEFAULT NULL,
  `date_added` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_subscription_log`
DROP TABLE IF EXISTS `agsc_subscription_log`;
CREATE TABLE `agsc_subscription_log` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `subscription_id` bigint(20) NOT NULL,
  `code` varchar(128) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` tinyint(1) DEFAULT 0,
  `date_added` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_subscription_option`
DROP TABLE IF EXISTS `agsc_subscription_option`;
CREATE TABLE `agsc_subscription_option` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `subscription_id` bigint(20) NOT NULL,
  `subscription_product_id` bigint(20) NOT NULL,
  `product_option_id` bigint(20) NOT NULL,
  `product_option_value_id` bigint(20) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `value` text DEFAULT NULL,
  `type` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_subscription_plan`
DROP TABLE IF EXISTS `agsc_subscription_plan`;
CREATE TABLE `agsc_subscription_plan` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `trial_frequency` enum('day','week','semi_month','month','year') DEFAULT NULL,
  `trial_duration` int(10) DEFAULT 0,
  `trial_cycle` int(10) DEFAULT 0,
  `trial_status` tinyint(4) DEFAULT 0,
  `frequency` enum('day','week','semi_month','month','year') DEFAULT NULL,
  `duration` int(10) DEFAULT 0,
  `cycle` int(10) DEFAULT 0,
  `status` tinyint(1) DEFAULT 0,
  `sort_order` int(3) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_subscription_plan_description`
DROP TABLE IF EXISTS `agsc_subscription_plan_description`;
CREATE TABLE `agsc_subscription_plan_description` (
  `subscription_plan_id` bigint(20) NOT NULL,
  `language_id` bigint(20) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`subscription_plan_id`,`language_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_subscription_product`
DROP TABLE IF EXISTS `agsc_subscription_product`;
CREATE TABLE `agsc_subscription_product` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `subscription_id` bigint(20) NOT NULL,
  `order_id` bigint(20) NOT NULL,
  `order_product_id` bigint(20) NOT NULL,
  `product_id` bigint(20) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `model` varchar(255) DEFAULT NULL,
  `quantity` int(4) DEFAULT 0,
  `trial_price` decimal(10,4) DEFAULT NULL,
  `trial_tax` decimal(15,4) DEFAULT 0.0000,
  `price` decimal(10,4) DEFAULT NULL,
  `tax` decimal(15,4) DEFAULT 0.0000,
  PRIMARY KEY (`id`),
  KEY `subscription_id` (`subscription_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_subscription_status`
DROP TABLE IF EXISTS `agsc_subscription_status`;
CREATE TABLE `agsc_subscription_status` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `language_id` bigint(20) NOT NULL,
  `name` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`,`language_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_supplier_addresses`
DROP TABLE IF EXISTS `agsc_supplier_addresses`;
CREATE TABLE `agsc_supplier_addresses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `supplier_id` bigint(20) unsigned NOT NULL,
  `country_id` bigint(20) unsigned NOT NULL,
  `zone_id` bigint(20) unsigned NOT NULL,
  `city_id` bigint(20) unsigned NOT NULL,
  `postal_code` varchar(20) NOT NULL,
  `street` varchar(255) NOT NULL,
  `number` varchar(20) NOT NULL,
  `complement` varchar(100) DEFAULT NULL,
  `district` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `country_id` (`country_id`),
  KEY `zone_id` (`zone_id`),
  KEY `city_id` (`city_id`),
  CONSTRAINT `agsc_supplier_addresses_ibfk_1` FOREIGN KEY (`country_id`) REFERENCES `agsc_geo_countries` (`id`),
  CONSTRAINT `agsc_supplier_addresses_ibfk_2` FOREIGN KEY (`zone_id`) REFERENCES `agsc_geo_zones` (`id`),
  CONSTRAINT `agsc_supplier_addresses_ibfk_3` FOREIGN KEY (`city_id`) REFERENCES `agsc_geo_cities` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_supplier_contact_manufacturer`
DROP TABLE IF EXISTS `agsc_supplier_contact_manufacturer`;
CREATE TABLE `agsc_supplier_contact_manufacturer` (
  `supplier_id` bigint(20) NOT NULL,
  `contact_id` bigint(20) NOT NULL,
  `manufacturer_id` bigint(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`supplier_id`,`contact_id`,`manufacturer_id`),
  KEY `idx_scb_supplier` (`supplier_id`),
  KEY `idx_scb_contact` (`contact_id`),
  KEY `fk_scb_manufacturer` (`manufacturer_id`),
  CONSTRAINT `fk_scb_brand` FOREIGN KEY (`manufacturer_id`) REFERENCES `agsc_brand` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_scb_contact` FOREIGN KEY (`contact_id`) REFERENCES `agsc_contact` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_scb_manufacturer` FOREIGN KEY (`manufacturer_id`) REFERENCES `agsc_manufacturer` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_scb_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `agsc_suppliers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_suppliers`
DROP TABLE IF EXISTS `agsc_suppliers`;
CREATE TABLE `agsc_suppliers` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `company_name` varchar(255) NOT NULL COMMENT 'Razão Social (Legal Name)',
  `trade_name` varchar(255) DEFAULT NULL COMMENT 'Nome Fantasia (Doing Business As / DBA)',
  `tax_id` varchar(14) NOT NULL COMMENT 'CNPJ (Universalizado como Tax ID)',
  `state_registration` varchar(20) DEFAULT NULL COMMENT 'Inscrição Estadual (State Tax Registration)',
  `municipal_registration` varchar(20) DEFAULT NULL COMMENT 'Inscrição Municipal',
  `email` varchar(150) NOT NULL COMMENT 'E-mail do fornecedor',
  `phone` varchar(20) DEFAULT NULL COMMENT 'Telefone de contato',
  `website` varchar(255) DEFAULT NULL COMMENT 'Site',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Ativo?',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `tax_id` (`tax_id`),
  KEY `idx_suppliers_tax_id` (`tax_id`),
  KEY `idx_suppliers_company_name` (`company_name`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_tax_class`
DROP TABLE IF EXISTS `agsc_tax_class`;
CREATE TABLE `agsc_tax_class` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `title` varchar(32) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_tax_rate`
DROP TABLE IF EXISTS `agsc_tax_rate`;
CREATE TABLE `agsc_tax_rate` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `geo_zones_id` bigint(20) NOT NULL,
  `name` varchar(32) DEFAULT NULL,
  `rate` decimal(15,4) DEFAULT 0.0000,
  `type` char(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=89 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_tax_rate_to_customer_group`
DROP TABLE IF EXISTS `agsc_tax_rate_to_customer_group`;
CREATE TABLE `agsc_tax_rate_to_customer_group` (
  `tax_rate_id` bigint(20) NOT NULL,
  `customer_group_id` bigint(20) NOT NULL,
  PRIMARY KEY (`tax_rate_id`,`customer_group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_tax_rule`
DROP TABLE IF EXISTS `agsc_tax_rule`;
CREATE TABLE `agsc_tax_rule` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tax_class_id` bigint(20) NOT NULL,
  `tax_rate_id` bigint(20) NOT NULL,
  `based` varchar(10) DEFAULT NULL,
  `priority` int(5) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=130 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_theme`
DROP TABLE IF EXISTS `agsc_theme`;
CREATE TABLE `agsc_theme` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `store_id` bigint(20) NOT NULL,
  `route` varchar(64) DEFAULT NULL,
  `code` mediumtext DEFAULT NULL,
  `status` tinyint(1) DEFAULT 0,
  `date_added` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_topic`
DROP TABLE IF EXISTS `agsc_topic`;
CREATE TABLE `agsc_topic` (
  `topic_id` int(11) NOT NULL AUTO_INCREMENT,
  `sort_order` int(3) DEFAULT 0,
  `status` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`topic_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Structure for table `agsc_topic_description`
DROP TABLE IF EXISTS `agsc_topic_description`;
CREATE TABLE `agsc_topic_description` (
  `topic_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` varchar(255) DEFAULT NULL,
  `meta_keyword` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`topic_id`,`language_id`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Structure for table `agsc_topic_to_layout`
DROP TABLE IF EXISTS `agsc_topic_to_layout`;
CREATE TABLE `agsc_topic_to_layout` (
  `topic_id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL DEFAULT 0,
  `layout_id` int(11) DEFAULT 0,
  PRIMARY KEY (`topic_id`,`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Structure for table `agsc_topic_to_store`
DROP TABLE IF EXISTS `agsc_topic_to_store`;
CREATE TABLE `agsc_topic_to_store` (
  `topic_id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`topic_id`,`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Structure for table `agsc_translation`
DROP TABLE IF EXISTS `agsc_translation`;
CREATE TABLE `agsc_translation` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `store_id` bigint(20) NOT NULL,
  `language_id` bigint(20) NOT NULL,
  `route` varchar(64) DEFAULT NULL,
  `key` varchar(64) DEFAULT NULL,
  `value` text DEFAULT NULL,
  `date_added` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_user`
DROP TABLE IF EXISTS `agsc_user`;
CREATE TABLE `agsc_user` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_group_id` bigint(20) NOT NULL,
  `username` varchar(20) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `firstname` varchar(32) DEFAULT NULL,
  `lastname` varchar(32) DEFAULT NULL,
  `email` varchar(96) DEFAULT NULL,
  `image` varchar(255) DEFAULT '',
  `ip` varchar(40) DEFAULT '',
  `status` tinyint(1) DEFAULT 0,
  `date_added` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_user_authorize`
DROP TABLE IF EXISTS `agsc_user_authorize`;
CREATE TABLE `agsc_user_authorize` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `token` varchar(96) DEFAULT NULL,
  `total` int(1) DEFAULT 0,
  `ip` varchar(40) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 0,
  `date_added` datetime DEFAULT NULL,
  `date_expire` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_user_group`
DROP TABLE IF EXISTS `agsc_user_group`;
CREATE TABLE `agsc_user_group` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) DEFAULT NULL,
  `permission` text DEFAULT NULL,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_user_group_description`
DROP TABLE IF EXISTS `agsc_user_group_description`;
CREATE TABLE `agsc_user_group_description` (
  `user_group_id` bigint(20) NOT NULL,
  `language_id` bigint(20) NOT NULL,
  `name` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`user_group_id`,`language_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_user_login`
DROP TABLE IF EXISTS `agsc_user_login`;
CREATE TABLE `agsc_user_login` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `username` varchar(96) NOT NULL,
  `ip` varchar(40) NOT NULL,
  `total` int(4) NOT NULL DEFAULT 1,
  `date_added` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `username` (`username`),
  KEY `ip` (`ip`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_user_token`
DROP TABLE IF EXISTS `agsc_user_token`;
CREATE TABLE `agsc_user_token` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `code` text DEFAULT NULL,
  `type` varchar(10) DEFAULT NULL,
  `date_added` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_weight_class`
DROP TABLE IF EXISTS `agsc_weight_class`;
CREATE TABLE `agsc_weight_class` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `value` decimal(15,8) DEFAULT 0.00000000,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure for table `agsc_weight_class_description`
DROP TABLE IF EXISTS `agsc_weight_class_description`;
CREATE TABLE `agsc_weight_class_description` (
  `weight_class_id` bigint(20) NOT NULL,
  `language_id` bigint(20) NOT NULL,
  `title` varchar(32) DEFAULT NULL,
  `unit` varchar(4) DEFAULT NULL,
  PRIMARY KEY (`weight_class_id`,`language_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Baseline Seed Data for `agsc_language`
INSERT INTO `agsc_language` (`id`, `name`, `code`, `locale`, `image`, `extension`, `sort_order`, `status`) VALUES
('1', 'English', 'en-gb', 'en-GB,cc_EN.UTF-8,en_US.UTF-8,en_US,english', 'gbr.png', '', '1', '1'),
('2', 'Português (Portuguese)', 'pt-br', 'pt-BR,pt_BR.UTF-8,pt_BR,pt-br,portuguese', 'bra.png', '', '2', '1'),
('3', 'French', 'fr', 'fr_FR.UTF-8,fr_FR,fr-fr,french', 'fr.png', '', '3', '1'),
('4', 'Swedish', 'sv', 'sv_SE.UTF-8,sv_SE,sv-se,swedish', 'Sweden.png', '', '4', '1');

-- Baseline Seed Data for `agsc_currency`
INSERT INTO `agsc_currency` (`id`, `title`, `code`, `symbol_left`, `symbol_right`, `decimal_place`, `value`, `status`, `date_modified`) VALUES
('1', 'Pound Sterling', 'GBP', '£', '', '2', '0.14018451', '0', '2026-01-23 11:30:27'),
('2', 'US Dollar', 'USD', '$', '', '2', '0.18814491', '0', '2026-01-23 11:30:27'),
('3', 'Euro', 'EUR', '', '€', '2', '0.16072519', '0', '2026-01-23 11:30:27'),
('4', 'Hong Kong Dollar', 'HKD', 'HK$', '', '2', '1.46714777', '0', '2026-01-23 11:30:27'),
('5', 'Indian Rupee', 'INR', '₹', '', '2', '17.23231219', '0', '2026-01-23 11:30:27'),
('6', 'Russian Ruble', 'RUB', '', '₽', '2', '56.4036', '0', '2018-02-16 12:00:00'),
('7', 'Chinese Yuan Renminbi', 'CNY', '¥', '', '2', '1.31244977', '0', '2026-01-23 11:30:27'),
('8', 'Australian Dollar', 'AUD', '$', '', '2', '0.27639911', '0', '2026-01-23 11:30:27'),
('20', 'Real', 'BRL', 'R$', '', '2', '1', '1', '2026-01-23 11:30:27');

-- Baseline Seed Data for `agsc_length_class`
INSERT INTO `agsc_length_class` (`id`, `value`) VALUES
('1', '1.00000000'),
('2', '10.00000000'),
('3', '0.39370000');

-- Baseline Seed Data for `agsc_length_class_description`
INSERT INTO `agsc_length_class_description` (`length_class_id`, `language_id`, `title`, `unit`) VALUES
('1', '1', 'Centimeter', 'cm'),
('1', '2', 'Centímetro', 'cm'),
('2', '1', 'Millimeter', 'mm'),
('2', '2', 'Milímetro', 'mm'),
('3', '1', 'Inch', 'inch'),
('3', '2', 'Polegada', 'in');

-- Baseline Seed Data for `agsc_weight_class`
INSERT INTO `agsc_weight_class` (`id`, `value`) VALUES
('1', '1.00000000'),
('2', '1000.00000000'),
('3', '2.20460000'),
('4', '35.27400000');

-- Baseline Seed Data for `agsc_weight_class_description`
INSERT INTO `agsc_weight_class_description` (`weight_class_id`, `language_id`, `title`, `unit`) VALUES
('1', '2', 'Kilograma', 'kg'),
('2', '2', 'Grama', 'g');

-- Baseline Seed Data for `agsc_customer_group`
INSERT INTO `agsc_customer_group` (`id`, `approval`, `sort_order`) VALUES
('1', '0', '1'),
('2', '0', '2'),
('3', '1', '3');

-- Baseline Seed Data for `agsc_customer_group_description`
INSERT INTO `agsc_customer_group_description` (`customer_group_id`, `language_id`, `name`, `description`) VALUES
('1', '2', 'Padrão', 'Cliente padrão');

-- Baseline Seed Data for `agsc_order_status`
INSERT INTO `agsc_order_status` (`id`, `language_id`, `name`) VALUES
('1', '1', 'Pending'),
('1', '2', 'Pendente'),
('2', '1', 'Processing'),
('2', '2', 'Processando'),
('3', '1', 'Shipped'),
('3', '2', 'Enviado'),
('5', '1', 'Complete'),
('5', '2', 'Completo'),
('7', '1', 'Canceled'),
('7', '2', 'Cancelado'),
('8', '1', 'Denied'),
('8', '2', 'Negado'),
('9', '1', 'Canceled Reversal'),
('9', '2', 'Cancelamento Revertido'),
('10', '1', 'Failed'),
('10', '2', 'Falhou'),
('11', '1', 'Refunded'),
('11', '2', 'Reembolsado'),
('12', '1', 'Reversed'),
('12', '2', 'Revertido'),
('13', '1', 'Chargeback'),
('13', '2', 'Estornado (Chargeback)'),
('14', '1', 'Expired'),
('14', '2', 'Expirado'),
('15', '1', 'Processed'),
('15', '2', 'Processado'),
('16', '1', 'Voided'),
('16', '2', 'Anulado');

-- Baseline Seed Data for `agsc_return_reason`
INSERT INTO `agsc_return_reason` (`id`, `language_id`, `name`) VALUES
('1', '1', 'Dead On Arrival'),
('1', '2', 'Inoperante na Chegada (DOA)'),
('2', '1', 'Faulty, please supply details'),
('2', '2', 'Defeituoso, consulte os detalhes'),
('3', '1', 'Order Error'),
('3', '2', 'Erro no pedido'),
('4', '1', 'Other, please supply details'),
('4', '2', 'Outro, consulte os detalhes'),
('5', '1', 'Received Wrong Item'),
('5', '2', 'Produto Errado Recebido');

-- Baseline Seed Data for `agsc_return_action`
INSERT INTO `agsc_return_action` (`id`, `language_id`, `name`) VALUES
('1', '1', 'Refunded'),
('1', '2', 'Reembolsado'),
('2', '1', 'Credited'),
('2', '2', 'Crédito Emitido'),
('3', '1', 'Returned'),
('3', '2', 'Devolvido'),
('4', '1', 'Replaced'),
('4', '2', 'Envio de Substituição');

-- Baseline Seed Data for `agsc_return_status`
INSERT INTO `agsc_return_status` (`id`, `language_id`, `name`) VALUES
('1', '1', 'Pending'),
('1', '2', 'Pendente'),
('2', '1', 'Processing'),
('2', '2', 'Em processamento'),
('3', '1', 'Complete'),
('3', '2', 'Completado');

-- Baseline Seed Data for `agsc_stock_status`
INSERT INTO `agsc_stock_status` (`id`, `language_id`, `name`) VALUES
('5', '1', 'Out Of Stock'),
('5', '2', 'Esgotado'),
('6', '1', '2 - 3 Days'),
('6', '2', '2-3 Dias'),
('7', '1', 'In Stock'),
('7', '2', 'Em Estoque'),
('8', '1', 'Pre-Order'),
('8', '2', 'Pre-Ordem');

-- Baseline Seed Data for `agsc_user_group`
INSERT INTO `agsc_user_group` (`id`, `name`, `permission`, `description`) VALUES
('1', 'Administrator', '{\"access\":[\"common\\/dashboard\",\"catalog\\/product\",\"catalog\\/category\",\"catalog\\/manufacturer\",\"procurement\\/supplier\",\"customer\\/customer\",\"setting\\/setting\",\"sale\\/order\"],\"modify\":[\"common\\/dashboard\",\"catalog\\/product\",\"catalog\\/category\",\"catalog\\/manufacturer\",\"procurement\\/supplier\",\"customer\\/customer\",\"setting\\/setting\",\"sale\\/order\"]}', 'Full access to all system settings, infrastructure, and user management.'),
('2', 'Demonstration', '{\"access\":[\"common\\/dashboard\",\"catalog\\/product\",\"catalog\\/category\",\"catalog\\/manufacturer\",\"procurement\\/supplier\",\"customer\\/customer\",\"setting\\/setting\",\"sale\\/order\"],\"modify\":[]}', 'Read-only access to the system for testing, presentations, or investors.'),
('3', 'Marketing', '{\"access\":[\"common\\/dashboard\",\"catalog\\/product\",\"catalog\\/category\",\"catalog\\/manufacturer\",\"customer\\/customer\"],\"modify\":[\"catalog\\/manufacturer\"]}', 'Manages promotional campaigns, discount coupons, SEO settings, and tracking pixels.'),
('4', 'Product Data Entry', '{\"access\":[\"common\\/dashboard\",\"catalog\\/product\",\"catalog\\/category\",\"catalog\\/manufacturer\",\"procurement\\/supplier\"],\"modify\":[\"catalog\\/product\",\"catalog\\/category\",\"catalog\\/manufacturer\",\"procurement\\/supplier\"]}', 'Responsible for registering and updating products, categories, technical specs, and SKUs.'),
('5', 'Order Processing', '{\"access\":[\"common\\/dashboard\",\"catalog\\/product\",\"sale\\/order\"],\"modify\":[\"sale\\/order\"]}', 'Handles order validation, invoicing, packing, and shipping operations.'),
('6', 'Accounting', '{\"access\":[\"common\\/dashboard\",\"sale\\/order\",\"setting\\/setting\"],\"modify\":[\"sale\\/order\"]}', 'Accesses financial reports, tax invoices, bank reconciliation, and chargeback management.'),
('7', 'Customer Service', '{\"access\":[\"common\\/dashboard\",\"customer\\/customer\",\"sale\\/order\"],\"modify\":[\"customer\\/customer\"]}', 'Manages customer support tickets, order tracking inquiries, returns, and refunds.'),
('8', 'Analysis', '{\"access\":[\"common\\/dashboard\",\"sale\\/order\",\"customer\\/customer\"],\"modify\":[]}', 'Views business intelligence metrics, sales funnels, and traffic analytics dashboards.'),
('9', 'Content Writing', '{\"access\":[\"common\\/dashboard\",\"catalog\\/product\",\"catalog\\/category\"],\"modify\":[\"catalog\\/product\",\"catalog\\/category\"]}', 'Creates and edits blog posts, help center articles, and institutional pages.'),
('10', 'Seller', NULL, 'Manages direct sales, custom quotes, and B2B customer relationships.'),
('11', 'Translator / Localizer', NULL, 'Translates and adapts product details, emails, and banners for different regional locales.'),
('12', 'Logistics Manager', NULL, 'Configures international shipping carriers, freight rules, and third-party warehouse (3PL) integrations.'),
('13', 'Compliance & Tax Specialist', NULL, 'Manages regional tax settings (VAT/Sales Tax) and data privacy compliance regulations.'),
('14', 'POS Salesperson (Test)', '{\"access\":[\"pos\\/sales_rep\",\"catalog\\/product\"],\"modify\":[\"pos\\/sales_rep\"]}', ''),
('22', 'Motorista (entregas)', '{\"access\":[],\"modify\":[]}', ''),
('23', 'Ajudante (entregas)', '{\"access\":[],\"modify\":[]}', ''),
('24', 'Grupo Teste Temp', '{\"access\":[\"dashboard\"],\"modify\":[]}', ''),
('26', 'Grupo Teste Temp', '{\"access\":[\"dashboard\"],\"modify\":[]}', '');

-- Baseline Seed Data for `agsc_user_group_description`
INSERT INTO `agsc_user_group_description` (`user_group_id`, `language_id`, `name`) VALUES
('1', '1', 'Administrator'),
('1', '2', 'Administrador'),
('1', '3', 'Administrateur'),
('1', '4', 'Administratör'),
('2', '1', 'Demonstration'),
('2', '2', 'Demonstração'),
('2', '3', 'Démonstration'),
('2', '4', 'Demonstration'),
('3', '1', 'Marketing'),
('3', '2', 'Marketing'),
('3', '3', 'Marketing'),
('3', '4', 'Marknadsföring'),
('4', '1', 'Product Data Entry'),
('4', '2', 'Cadastro de Produtos'),
('4', '3', 'Saisie de données produits'),
('4', '4', 'Produktdatainmatning'),
('5', '1', 'Order Processing'),
('5', '2', 'Processamento de Pedidos'),
('5', '3', 'Traitement des commandes'),
('5', '4', 'Orderhantering'),
('6', '1', 'Accounting'),
('6', '2', 'Contabilidade'),
('6', '3', 'Comptabilité'),
('6', '4', 'Redovisning'),
('7', '1', 'Customer Service'),
('7', '2', 'Atendimento ao Cliente'),
('7', '3', 'Service client'),
('7', '4', 'Kundtjänst'),
('8', '1', 'Analysis'),
('8', '2', 'Análise'),
('8', '3', 'Analyse'),
('8', '4', 'Analys'),
('9', '1', 'Content Writing'),
('9', '2', 'Redação de Conteúdo'),
('9', '3', 'Rédaction de contenu'),
('9', '4', 'Innehållsskrivning'),
('10', '1', 'Seller'),
('10', '2', 'Vendedor'),
('10', '3', 'Vendeur'),
('10', '4', 'Säljare'),
('11', '1', 'Translator / Localizer'),
('11', '2', 'Tradutor/Localizador'),
('11', '3', 'Traducteur / Spécialiste en localisation'),
('11', '4', 'Översättare / Lokaliserare'),
('12', '1', 'Logistics Manager'),
('12', '2', 'Gerente de Logística'),
('12', '3', 'Responsable logistique'),
('12', '4', 'Logistikchef'),
('13', '1', 'Compliance & Tax Specialist'),
('13', '2', 'Especialista em Compliance e Tributação');
INSERT INTO `agsc_user_group_description` (`user_group_id`, `language_id`, `name`) VALUES
('13', '3', 'Spécialiste conformité et fiscalité'),
('13', '4', 'Efterlevnads- och skattespecialist'),
('14', '1', 'POS Salesperson (Test)'),
('14', '2', 'Teste de Vendedor de PDV'),
('14', '3', 'Vendeur point de vente (test)'),
('14', '4', 'Leverantör av PDV-test'),
('15', '1', 'delivery driver'),
('15', '2', 'Motorista (entregas)'),
('15', '3', 'Livreur'),
('15', '4', 'budförare'),
('16', '1', 'Delivery driver\'s assistant'),
('16', '2', 'Ajudante (entregas)'),
('16', '3', 'Assistant livreur'),
('16', '4', 'Busförarassistent'),
('26', '1', 'Test Role (EN)'),
('26', '2', 'Papel de Teste (PT)'),
('26', '3', 'Rôle de Test (FR)');

-- Baseline Seed Data for `agsc_store`
INSERT INTO `agsc_store` (`id`, `name`, `url`) VALUES
('1', 'Loja Principal', 'http://localhost/');

-- Baseline Seed Data for `agsc_setting`
INSERT INTO `agsc_setting` (`id`, `store_id`, `code`, `key`, `value`, `serialized`) VALUES
('130', '1', 'currency_ecb', 'currency_ecb_status', '1', '0'),
('131', '1', 'dashboard_activity', 'dashboard_activity_status', '1', '0'),
('132', '1', 'dashboard_activity', 'dashboard_activity_sort_order', '7', '0'),
('133', '1', 'dashboard_sale', 'dashboard_sale_status', '1', '0'),
('134', '1', 'dashboard_sale', 'dashboard_sale_width', '3', '0'),
('135', '1', 'dashboard_chart', 'dashboard_chart_status', '1', '0'),
('136', '1', 'dashboard_chart', 'dashboard_chart_width', '6', '0'),
('137', '1', 'dashboard_customer', 'dashboard_customer_status', '1', '0'),
('138', '1', 'dashboard_customer', 'dashboard_customer_width', '3', '0'),
('139', '1', 'dashboard_map', 'dashboard_map_status', '1', '0'),
('140', '1', 'dashboard_map', 'dashboard_map_width', '6', '0'),
('141', '1', 'dashboard_online', 'dashboard_online_status', '1', '0'),
('142', '1', 'dashboard_online', 'dashboard_online_width', '3', '0'),
('143', '1', 'dashboard_order', 'dashboard_order_sort_order', '1', '0'),
('144', '1', 'dashboard_order', 'dashboard_order_status', '1', '0'),
('145', '1', 'dashboard_order', 'dashboard_order_width', '3', '0'),
('146', '1', 'dashboard_sale', 'dashboard_sale_sort_order', '2', '0'),
('147', '1', 'dashboard_customer', 'dashboard_customer_sort_order', '3', '0'),
('148', '1', 'dashboard_online', 'dashboard_online_sort_order', '4', '0'),
('149', '1', 'dashboard_map', 'dashboard_map_sort_order', '5', '0'),
('150', '1', 'dashboard_chart', 'dashboard_chart_sort_order', '6', '0'),
('151', '1', 'dashboard_recent', 'dashboard_recent_status', '1', '0'),
('152', '1', 'dashboard_recent', 'dashboard_recent_sort_order', '8', '0'),
('153', '1', 'dashboard_activity', 'dashboard_activity_width', '4', '0'),
('154', '1', 'dashboard_recent', 'dashboard_recent_width', '8', '0'),
('155', '1', 'developer', 'developer_sass', '1', '0'),
('156', '1', 'module_category', 'module_category_status', '1', '0'),
('157', '1', 'module_account', 'module_account_status', '1', '0'),
('158', '1', 'module_topic', 'module_topic_status', '1', '0'),
('167', '1', 'report_customer_activity', 'report_customer_activity_status', '1', '0'),
('168', '1', 'report_customer_activity', 'report_customer_activity_sort_order', '1', '0'),
('169', '1', 'report_customer_order', 'report_customer_order_status', '1', '0'),
('170', '1', 'report_customer_order', 'report_customer_order_sort_order', '2', '0'),
('171', '1', 'report_customer_reward', 'report_customer_reward_status', '1', '0'),
('172', '1', 'report_customer_reward', 'report_customer_reward_sort_order', '3', '0'),
('173', '1', 'report_customer_search', 'report_customer_search_status', '1', '0'),
('174', '1', 'report_customer_search', 'report_customer_search_sort_order', '4', '0'),
('175', '1', 'report_customer_transaction', 'report_customer_transaction_status', '1', '0'),
('176', '1', 'report_customer_transaction', 'report_customer_transaction_sort_order', '5', '0'),
('177', '1', 'report_customer', 'report_customer_status', '1', '0'),
('178', '1', 'report_customer', 'report_customer_sort_order', '6', '0'),
('179', '1', 'report_sale_tax', 'report_sale_tax_status', '1', '0'),
('180', '1', 'report_sale_tax', 'report_sale_tax_sort_order', '8', '0'),
('181', '1', 'report_sale_shipping', 'report_sale_shipping_status', '1', '0'),
('182', '1', 'report_sale_shipping', 'report_sale_shipping_sort_order', '9', '0'),
('183', '1', 'report_sale_return', 'report_sale_return_status', '1', '0'),
('184', '1', 'report_sale_return', 'report_sale_return_sort_order', '10', '0'),
('185', '1', 'report_sale_order', 'report_sale_order_status', '1', '0'),
('186', '1', 'report_sale_order', 'report_sale_order_sort_order', '11', '0'),
('187', '1', 'report_sale_coupon', 'report_sale_coupon_status', '1', '0');
INSERT INTO `agsc_setting` (`id`, `store_id`, `code`, `key`, `value`, `serialized`) VALUES
('188', '1', 'report_sale_coupon', 'report_sale_coupon_sort_order', '12', '0'),
('189', '1', 'report_product_viewed', 'report_product_viewed_status', '1', '0'),
('190', '1', 'report_product_viewed', 'report_product_viewed_sort_order', '13', '0'),
('191', '1', 'report_product_purchased', 'report_product_purchased_status', '1', '0'),
('192', '1', 'report_product_purchased', 'report_product_purchased_sort_order', '14', '0'),
('193', '1', 'report_marketing', 'report_marketing_status', '1', '0'),
('194', '1', 'report_marketing', 'report_marketing_sort_order', '15', '0'),
('195', '1', 'report_subscription', 'report_subscription_status', '1', '0'),
('196', '1', 'report_subscription', 'report_subscription_sort_order', '16', '0'),
('202', '1', 'theme_basic', 'theme_basic_status', '1', '0'),
('203', '1', 'total_shipping', 'total_shipping_sort_order', '3', '0'),
('204', '1', 'total_sub_total', 'total_sub_total_sort_order', '1', '0'),
('205', '1', 'total_sub_total', 'total_sub_total_status', '1', '0'),
('206', '1', 'total_tax', 'total_tax_sort_order', '5', '0'),
('207', '1', 'total_tax', 'total_tax_status', '1', '0'),
('208', '1', 'total_total', 'total_total_sort_order', '9', '0'),
('209', '1', 'total_total', 'total_total_status', '1', '0'),
('210', '1', 'total_credit', 'total_credit_sort_order', '7', '0'),
('211', '1', 'total_credit', 'total_credit_status', '1', '0'),
('212', '1', 'total_reward', 'total_reward_sort_order', '2', '0'),
('213', '1', 'total_reward', 'total_reward_status', '1', '0'),
('214', '1', 'total_shipping', 'total_shipping_status', '1', '0'),
('215', '1', 'total_shipping', 'total_shipping_estimator', '1', '0'),
('216', '1', 'total_coupon', 'total_coupon_sort_order', '4', '0'),
('217', '1', 'total_coupon', 'total_coupon_status', '1', '0'),
('3436', '1', 'payment_cod', 'payment_cod_order_status_id', '1', '0'),
('3437', '1', 'payment_cod', 'payment_cod_geo_zone_id', '6', '0'),
('3438', '1', 'payment_cod', 'payment_cod_status', '1', '0'),
('3439', '1', 'payment_cod', 'payment_cod_sort_order', '5', '0'),
('3443', '1', 'shipping_pickup', 'shipping_pickup_geo_zone_id', '5', '0'),
('3444', '1', 'shipping_pickup', 'shipping_pickup_status', '1', '0'),
('3445', '1', 'shipping_pickup', 'shipping_pickup_sort_order', '1', '0'),
('4250', '1', 'config', 'config_name', 'Minha Loja Alpha', '0'),
('4251', '1', 'config', 'config_theme', 'basic', '0'),
('4252', '1', 'config', 'config_layout_id', '4', '0'),
('4253', '1', 'config', 'config_logo', 'logo2.png', '0'),
('4254', '1', 'config', 'config_icon', 'image/logomark/logo2.png', '0'),
('4255', '1', 'config', 'config_description', '{\"2\":{\"meta_title\":\"Minha Loja Alpha\",\"meta_description\":\"Sua loja online construída com Alpha Engine\",\"meta_keyword\":\"e-commerce, loja\"}}', '1'),
('4256', '1', 'config', 'config_owner', 'Administrador', '0'),
('4257', '1', 'config', 'config_address', 'Endereço da Loja, 100', '0'),
('4258', '1', 'config', 'config_geocode', '', '0'),
('4259', '1', 'config', 'config_email', 'admin@sualoja.com', '0'),
('4260', '1', 'config', 'config_telephone', '(00) 0000-0000', '0'),
('4261', '1', 'config', 'config_image', 'image/logomark/logo2.png', '0'),
('4262', '1', 'config', 'config_open', 'domingo	08:00–13:00\r\nsegunda a sexta-feira	07:00–18:00\r\nsábado	07:00–17:00', '0'),
('4263', '1', 'config', 'config_comment', '', '0'),
('4264', '1', 'config', 'config_country_id', '76', '0'),
('4265', '1', 'config', 'config_zone_id', '31 	', '0'),
('4266', '1', 'config', 'config_timezone', 'America/Sao_Paulo', '0'),
('4267', '1', 'config', 'config_language_catalog', 'pt-br', '0');
INSERT INTO `agsc_setting` (`id`, `store_id`, `code`, `key`, `value`, `serialized`) VALUES
('4268', '1', 'config', 'config_language_admin', 'pt-br', '0'),
('4269', '1', 'config', 'config_currency', 'BRL', '0'),
('4270', '1', 'config', 'config_currency_engine', 'ecb', '0'),
('4271', '1', 'config', 'config_currency_auto', '0', '0'),
('4272', '1', 'config', 'config_length_class_id', '1', '0'),
('4273', '1', 'config', 'config_weight_class_id', '1', '0'),
('4274', '1', 'config', 'config_product_description_length', '100', '0'),
('4275', '1', 'config', 'config_pagination', '20', '0'),
('4276', '1', 'config', 'config_product_count', '1', '0'),
('4277', '1', 'config', 'config_pagination_admin', '20', '0'),
('4278', '1', 'config', 'config_autocomplete_limit', '5', '0'),
('4279', '1', 'config', 'config_product_report_status', '0', '0'),
('4280', '1', 'config', 'config_review_status', '1', '0'),
('4281', '1', 'config', 'config_review_purchased', '0', '0'),
('4282', '1', 'config', 'config_review_guest', '1', '0'),
('4283', '1', 'config', 'config_article_description_length', '600', '0'),
('4284', '1', 'config', 'config_comment_status', '0', '0'),
('4285', '1', 'config', 'config_comment_approve', '0', '0'),
('4286', '1', 'config', 'config_comment_interval', '', '0'),
('4287', '1', 'config', 'config_cookie_id', '3', '0'),
('4288', '1', 'config', 'config_gdpr_id', '0', '0'),
('4289', '1', 'config', 'config_gdpr_limit', '180', '0'),
('4290', '1', 'config', 'config_tax', '0', '0'),
('4291', '1', 'config', 'config_tax_default', 'shipping', '0'),
('4292', '1', 'config', 'config_tax_customer', 'shipping', '0'),
('4293', '1', 'config', 'config_customer_online', '1', '0'),
('4294', '1', 'config', 'config_customer_online_expire', '1', '0'),
('4295', '1', 'config', 'config_customer_activity', '1', '0'),
('4296', '1', 'config', 'config_customer_search', '1', '0'),
('4297', '1', 'config', 'config_customer_group_id', '1', '0'),
('4298', '1', 'config', 'config_customer_group_display', '[\"1\"]', '1'),
('4299', '1', 'config', 'config_customer_price', '0', '0'),
('4300', '1', 'config', 'config_telephone_display', '1', '0'),
('4301', '1', 'config', 'config_telephone_required', '0', '0'),
('4302', '1', 'config', 'config_account_id', '2', '0'),
('4303', '1', 'config', 'config_2fa', '0', '0'),
('4304', '1', 'config', 'config_login_attempts', '5', '0'),
('4305', '1', 'config', 'config_password_length', '6', '0'),
('4306', '1', 'config', 'config_invoice_prefix', 'PED-', '0'),
('4307', '1', 'config', 'config_cart_weight', '1', '0'),
('4308', '1', 'config', 'config_checkout_guest', '1', '0'),
('4309', '1', 'config', 'config_checkout_payment_address', '0', '0'),
('4310', '1', 'config', 'config_checkout_shipping_address', '1', '0'),
('4311', '1', 'config', 'config_checkout_id', '2', '0'),
('4312', '1', 'config', 'config_order_status_id', '1', '0'),
('4313', '1', 'config', 'config_processing_status', '[\"5\",\"2\",\"3\",\"1\",\"12\"]', '1'),
('4314', '1', 'config', 'config_complete_status', '[\"5\",\"3\"]', '1'),
('4315', '1', 'config', 'config_failed_status_id', '17', '0'),
('4316', '1', 'config', 'config_void_status_id', '7', '0'),
('4317', '1', 'config', 'config_fraud_status_id', '8', '0');
INSERT INTO `agsc_setting` (`id`, `store_id`, `code`, `key`, `value`, `serialized`) VALUES
('4318', '1', 'config', 'config_api_id', '1', '0'),
('4319', '1', 'config', 'config_stock_display', '0', '0'),
('4320', '1', 'config', 'config_stock_warning', '1', '0'),
('4321', '1', 'config', 'config_stock_checkout', '0', '0'),
('4322', '1', 'config', 'config_stock_status_id', '7', '0'),
('4323', '1', 'config', 'config_affiliate_status', '0', '0'),
('4324', '1', 'config', 'config_affiliate_group_id', '1', '0'),
('4325', '1', 'config', 'config_affiliate_approval', '0', '0'),
('4326', '1', 'config', 'config_affiliate_auto', '0', '0'),
('4327', '1', 'config', 'config_affiliate_commission', '5', '0'),
('4328', '1', 'config', 'config_affiliate_expire', '', '0'),
('4329', '1', 'config', 'config_affiliate_id', '4', '0'),
('4330', '1', 'config', 'config_return_status_id', '2', '0'),
('4331', '1', 'config', 'config_return_id', '2', '0'),
('4332', '1', 'config', 'config_captcha', '', '0'),
('4333', '1', 'config', 'config_captcha_page', '[\"review\",\"contact\"]', '1'),
('4334', '1', 'config', 'config_image_default_width', '300', '0'),
('4335', '1', 'config', 'config_image_default_height', '300', '0'),
('4336', '1', 'config', 'config_image_category_width', '300', '0'),
('4337', '1', 'config', 'config_image_category_height', '300', '0'),
('4338', '1', 'config', 'config_image_thumb_width', '500', '0'),
('4339', '1', 'config', 'config_image_thumb_height', '500', '0'),
('4340', '1', 'config', 'config_image_popup_width', '800', '0'),
('4341', '1', 'config', 'config_image_popup_height', '800', '0'),
('4342', '1', 'config', 'config_image_product_width', '250', '0'),
('4343', '1', 'config', 'config_image_product_height', '250', '0'),
('4344', '1', 'config', 'config_image_additional_width', '74', '0'),
('4345', '1', 'config', 'config_image_additional_height', '74', '0'),
('4346', '1', 'config', 'config_image_related_width', '250', '0'),
('4347', '1', 'config', 'config_image_related_height', '250', '0'),
('4348', '1', 'config', 'config_image_article_width', '1140', '0'),
('4349', '1', 'config', 'config_image_article_height', '380', '0'),
('4350', '1', 'config', 'config_image_topic_width', '1140', '0'),
('4351', '1', 'config', 'config_image_topic_height', '380', '0'),
('4352', '1', 'config', 'config_image_compare_width', '90', '0'),
('4353', '1', 'config', 'config_image_compare_height', '90', '0'),
('4354', '1', 'config', 'config_image_wishlist_width', '47', '0'),
('4355', '1', 'config', 'config_image_wishlist_height', '47', '0'),
('4356', '1', 'config', 'config_image_cart_width', '47', '0'),
('4357', '1', 'config', 'config_image_cart_height', '47', '0'),
('4358', '1', 'config', 'config_image_location_width', '268', '0'),
('4359', '1', 'config', 'config_image_location_height', '268', '0'),
('4360', '1', 'config', 'config_mail_engine', '', '0'),
('4361', '1', 'config', 'config_mail_parameter', '', '0'),
('4362', '1', 'config', 'config_mail_smtp_hostname', '', '0'),
('4363', '1', 'config', 'config_mail_smtp_username', '', '0'),
('4364', '1', 'config', 'config_mail_smtp_password', '', '0'),
('4365', '1', 'config', 'config_mail_smtp_port', '25', '0'),
('4366', '1', 'config', 'config_mail_smtp_timeout', '5', '0'),
('4367', '1', 'config', 'config_mail_alert', '[\"order\"]', '1');
INSERT INTO `agsc_setting` (`id`, `store_id`, `code`, `key`, `value`, `serialized`) VALUES
('4368', '1', 'config', 'config_mail_alert_email', '', '0'),
('4369', '1', 'config', 'config_maintenance', '0', '0'),
('4370', '1', 'config', 'config_session_expire', '86400', '0'),
('4371', '1', 'config', 'config_session_samesite', 'Strict', '0'),
('4372', '1', 'config', 'config_seo_url', '0', '0'),
('4373', '1', 'config', 'config_compression', '0', '0'),
('4374', '1', 'config', 'config_user_2fa', '0', '0'),
('4375', '1', 'config', 'config_2fa_expire', '90', '0'),
('4376', '1', 'config', 'config_user_password_length', '', '0'),
('4377', '1', 'config', 'config_shared', '0', '0'),
('4378', '1', 'config', 'config_file_max_size', '20', '0'),
('4379', '1', 'config', 'config_file_ext_allowed', 'zip\r\ntxt\r\npng\r\njpe\r\njpeg\r\nwebp\r\njpg\r\ngif\r\nbmp\r\nico\r\ntiff\r\ntif\r\nsvg\r\nsvgz\r\nzip\r\nrar\r\nmsi\r\ncab\r\nmp3\r\nmp4\r\nqt\r\nmov\r\npdf\r\npsd\r\nai\r\neps\r\nps\r\ndoc', '0'),
('4380', '1', 'config', 'config_file_mime_allowed', 'text/plain\r\nimage/png\r\nimage/webp\r\nimage/jpeg\r\nimage/gif\r\nimage/bmp\r\nimage/tiff\r\nimage/svg+xml\r\napplication/zip\r\napplication/x-zip\r\napplication/x-zip-compressed\r\napplication/rar\r\napplication/x-rar\r\napplication/x-rar-compressed\r\napplication/octet-stream\r\naudio/mpeg\r\nvideo/mp4\r\nvideo/quicktime\r\napplication/pdf', '0'),
('4381', '1', 'config', 'config_error_display', '1', '0'),
('4382', '1', 'config', 'config_error_log', '1', '0'),
('4383', '1', 'config', 'config_error_filename', 'error.log', '0'),
('4384', '1', 'config', 'config_facebook', '', '0'),
('4385', '1', 'config', 'config_instagram', '', '0'),
('4386', '1', 'config', 'config_youtube', '', '0'),
('4387', '1', 'config', 'config_store_id', '1', '0');

-- Baseline Seed Data for `agsc_geo_zone`
INSERT INTO `agsc_geo_zone` (`id`, `name`, `description`) VALUES
('5', 'Brasil', 'Território brasileiro'),
('6', 'São Paulo', 'Estado de São Paulo');

-- Baseline Seed Data for `agsc_geo_countries`
INSERT INTO `agsc_geo_countries` (`id`, `name`, `iso_alpha2`, `iso_alpha3`, `is_active`) VALUES
('76', 'Brasil', 'BR', 'BRA', '1'),
('208', 'Dinamarca', 'DK', 'DNK', '1'),
('246', 'Finlândia', 'FI', 'FIN', '1'),
('578', 'Noruega', 'NO', 'NOK', '1'),
('752', 'Suécia', 'SE', 'SWE', '1'),
('826', 'Reino Unido', 'GB', 'GBR', '1');

-- Baseline Seed Data for `agsc_geo_zones`
INSERT INTO `agsc_geo_zones` (`id`, `country_id`, `iso_code`, `name`) VALUES
('1', '208', 'DK-84', 'Hovedstaden'),
('2', '246', 'FI-18', 'Uusimaa'),
('3', '578', 'NO-03', 'Oslo'),
('4', '826', 'GB-ENG', 'Inglaterra'),
('5', '826', 'GB-SCT', 'Escócia'),
('6', '76', 'BR-AC', 'Acre'),
('7', '76', 'BR-AL', 'Alagoas'),
('8', '76', 'BR-AM', 'Amazonas'),
('9', '76', 'BR-AP', 'Amapá'),
('10', '76', 'BR-BA', 'Bahia'),
('11', '76', 'BR-CE', 'Ceará'),
('12', '76', 'BR-DF', 'Distrito Federal'),
('13', '76', 'BR-ES', 'Espírito Santo'),
('14', '76', 'BR-GO', 'Goiás'),
('15', '76', 'BR-MA', 'Maranhão'),
('16', '76', 'BR-MG', 'Minas Gerais'),
('17', '76', 'BR-MS', 'Mato Grosso do Sul'),
('18', '76', 'BR-MT', 'Mato Grosso'),
('19', '76', 'BR-PA', 'Pará'),
('20', '76', 'BR-PB', 'Paraíba'),
('21', '76', 'BR-PE', 'Pernambuco'),
('22', '76', 'BR-PI', 'Piauí'),
('23', '76', 'BR-PR', 'Paraná'),
('24', '76', 'BR-RJ', 'Rio de Janeiro'),
('25', '76', 'BR-RN', 'Rio Grande do Norte'),
('26', '76', 'BR-RO', 'Rondônia'),
('27', '76', 'BR-RR', 'Roraima'),
('28', '76', 'BR-RS', 'Rio Grande do Sul'),
('29', '76', 'BR-SC', 'Santa Catarina'),
('30', '76', 'BR-SE', 'Sergipe'),
('31', '76', 'BR-SP', 'São Paulo'),
('32', '76', 'BR-TO', 'Tocantins'),
('33', '752', 'SE-AB', 'Stockholm'),
('34', '752', 'SE-C', 'Uppsala'),
('35', '752', 'SE-D', 'Södermanland'),
('36', '752', 'SE-E', 'Östergötland'),
('37', '752', 'SE-F', 'Jönköping'),
('38', '752', 'SE-G', 'Kronoberg'),
('39', '752', 'SE-H', 'Kalmar'),
('40', '752', 'SE-I', 'Gotland'),
('41', '752', 'SE-K', 'Blekinge'),
('42', '752', 'SE-M', 'Skåne'),
('43', '752', 'SE-N', 'Halland'),
('44', '752', 'SE-O', 'Västra Götaland'),
('45', '752', 'SE-S', 'Värmland'),
('46', '752', 'SE-T', 'Örebro'),
('47', '752', 'SE-U', 'Västmanland'),
('48', '752', 'SE-W', 'Dalarna'),
('49', '752', 'SE-X', 'Gävleborg'),
('50', '752', 'SE-Y', 'Västernorrland');
INSERT INTO `agsc_geo_zones` (`id`, `country_id`, `iso_code`, `name`) VALUES
('51', '752', 'SE-Z', 'Jämtland'),
('52', '752', 'SE-AC', 'Västerbotten'),
('53', '752', 'SE-BD', 'Norrbotten');

-- Baseline Seed Data for `agsc_address_format`
INSERT INTO `agsc_address_format` (`id`, `name`, `address_format`) VALUES
('1', 'Address Format', '{firstname} {lastname}\r\n{company}\r\n{address_1}, {number} , {address_2}\r\n {neighborhood} \r\n{city}- {zone} \r\n{postcode}'),
('2', 'Formato de Endereço', '{firstname} {lastname}\r\n{company}\r\n{address_1}, {number} , {address_2}\r\n {neighborhood} \r\n{city}- {zone} \r\n{postcode}');

SET FOREIGN_KEY_CHECKS=1;
