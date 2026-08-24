-- ==============================================================================
-- Alpha Engine: Módulo de Cotação de Projetos e Prestadores de Serviço (RFQ / BoQ)
-- Requisitos Funcionais: RF033 a RF037
-- ==============================================================================

DROP TABLE IF EXISTS `agsc_project_boq_item`;
DROP TABLE IF EXISTS `agsc_project_boq`;
DROP TABLE IF EXISTS `agsc_project_bid`;
DROP TABLE IF EXISTS `agsc_project_rfq`;
DROP TABLE IF EXISTS `agsc_service_provider_profile`;

CREATE TABLE `agsc_service_provider_profile` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `trade_name` varchar(255) DEFAULT NULL,
  `document_number` varchar(32) NOT NULL,
  `specialties` text DEFAULT NULL,
  `service_radius_km` decimal(8,2) NOT NULL DEFAULT '25.00',
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `address_cep` varchar(10) DEFAULT NULL,
  `address_city` varchar(100) DEFAULT NULL,
  `address_state` varchar(2) DEFAULT NULL,
  `rating` decimal(3,2) NOT NULL DEFAULT '5.00',
  `total_reviews` int(11) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `date_added` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_customer_id` (`customer_id`),
  KEY `idx_geo` (`latitude`, `longitude`),
  KEY `idx_cep` (`address_cep`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `agsc_project_rfq` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL DEFAULT 'geral',
  `description` text NOT NULL,
  `address_cep` varchar(10) NOT NULL,
  `address_street` varchar(255) DEFAULT NULL,
  `address_number` varchar(50) DEFAULT NULL,
  `address_neighborhood` varchar(100) DEFAULT NULL,
  `address_city` varchar(100) NOT NULL,
  `address_state` varchar(2) NOT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `budget_expectation` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `desired_deadline_days` int(11) NOT NULL DEFAULT '30',
  `status` varchar(32) NOT NULL DEFAULT 'open',
  `selected_provider_id` int(11) DEFAULT NULL,
  `date_added` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_customer_rfq` (`customer_id`),
  KEY `idx_rfq_status` (`status`),
  KEY `idx_rfq_geo` (`latitude`, `longitude`),
  KEY `idx_rfq_cep` (`address_cep`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `agsc_project_bid` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rfq_id` int(11) NOT NULL,
  `provider_id` int(11) NOT NULL,
  `labor_price` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `estimated_duration_days` int(11) NOT NULL DEFAULT '1',
  `proposal_notes` text DEFAULT NULL,
  `status` varchar(32) NOT NULL DEFAULT 'submitted',
  `date_added` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_bid_rfq` (`rfq_id`),
  KEY `idx_bid_provider` (`provider_id`),
  KEY `idx_bid_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `agsc_project_boq` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rfq_id` int(11) NOT NULL,
  `provider_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL DEFAULT 'Levantamento de Materiais (BoQ)',
  `notes` text DEFAULT NULL,
  `total_estimated_amount` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `status` varchar(32) NOT NULL DEFAULT 'draft',
  `date_added` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_boq_rfq` (`rfq_id`),
  KEY `idx_boq_provider` (`provider_id`),
  KEY `idx_boq_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `agsc_project_boq_item` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `boq_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `item_name` varchar(255) NOT NULL,
  `unit` varchar(20) NOT NULL DEFAULT 'un',
  `quantity` decimal(12,4) NOT NULL DEFAULT '1.0000',
  `unit_price` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `total_price` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `notes` varchar(255) DEFAULT NULL,
  `date_added` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_boq_item_boq` (`boq_id`),
  KEY `idx_boq_item_product` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
