-- 1. Countries Table (PK changed to BIGINT)
CREATE TABLE `agsc_countries` (
    `id` BIGINT UNSIGNED PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `iso_alpha2` CHAR(2) NOT NULL UNIQUE,
    `iso_alpha3` CHAR(3) NOT NULL UNIQUE,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
-- 2. Zones Table (Refactored: Now uses a numeric Surrogate Key as PK)
CREATE TABLE `agsc_zones` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `country_id` BIGINT UNSIGNED NOT NULL,
    `iso_code` VARCHAR(10) NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    FOREIGN KEY (`country_id`) REFERENCES `agsc_countries`(`id`) ON DELETE RESTRICT,
    UNIQUE KEY `uk_country_zone_iso` (`country_id`, `iso_code`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
-- 3. Cities Table (PK changed to BIGINT GeoNames ID, FK updated to numeric zone_id)
CREATE TABLE `agsc_cities` (
    `id` BIGINT UNSIGNED PRIMARY KEY,
    `zone_id` BIGINT UNSIGNED NOT NULL,
    `name` VARCHAR(150) NOT NULL,
    `is_served` TINYINT(1) NOT NULL DEFAULT 0,
    FOREIGN KEY (`zone_id`) REFERENCES `agsc_zones`(`id`) ON DELETE RESTRICT
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
-- 4. Customer Addresses Table (All PKs and FKs standardized to BIGINT)
CREATE TABLE `agsc_customer_addresses` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `customer_id` BIGINT UNSIGNED NOT NULL,
    `country_id` BIGINT UNSIGNED NOT NULL,
    `zone_id` BIGINT UNSIGNED NOT NULL,
    -- Numeric FK
    `city_id` BIGINT UNSIGNED NOT NULL,
    -- Numeric FK (GeoNames ID)
    `postal_code` VARCHAR(20) NOT NULL,
    `street` VARCHAR(255) NOT NULL,
    `number` VARCHAR(20) NOT NULL,
    `complement` VARCHAR(100) NULL,
    `district` VARCHAR(100) NULL,
    FOREIGN KEY (`country_id`) REFERENCES `agsc_countries`(`id`),
    FOREIGN KEY (`zone_id`) REFERENCES `agsc_zones`(`id`),
    FOREIGN KEY (`city_id`) REFERENCES `agsc_cities`(`id`),
    INDEX `idx_shipping_lookup` (`country_id`, `postal_code`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;