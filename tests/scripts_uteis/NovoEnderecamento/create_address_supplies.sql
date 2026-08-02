CREATE TABLE `agsc_supplier_addresses` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `supplier_id` BIGINT UNSIGNED NOT NULL,
    -- FK para sua tabela de fornecedores (agsc_suppliers)
    `country_id` BIGINT UNSIGNED NOT NULL,
    -- Reutiliza a agsc_countries (76 para Brasil)
    `zone_id` BIGINT UNSIGNED NOT NULL,
    -- Reutiliza a agsc_zones (Surrogate Key)
    `city_id` BIGINT UNSIGNED NOT NULL,
    -- Reutiliza a agsc_cities (GeoNames ID)
    `postal_code` VARCHAR(20) NOT NULL,
    `street` VARCHAR(255) NOT NULL,
    `number` VARCHAR(20) NOT NULL,
    `complement` VARCHAR(100) NULL,
    `district` VARCHAR(100) NULL,
    FOREIGN KEY (`country_id`) REFERENCES `agsc_countries`(`id`),
    FOREIGN KEY (`zone_id`) REFERENCES `agsc_zones`(`id`),
    FOREIGN KEY (`city_id`) REFERENCES `agsc_cities`(`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;