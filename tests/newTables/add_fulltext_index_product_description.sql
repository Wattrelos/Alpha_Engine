-- Script de Migração: Adiciona Índice FULLTEXT na tabela de descrição de produtos
-- Permite buscas de alta performance utilizando MATCH(name, description, tag) AGAINST(? IN BOOLEAN MODE)

-- Remove o índice se já existir (para garantir idempotência da execução)
SET @exist := (SELECT COUNT(*) FROM information_schema.statistics 
               WHERE table_schema = DATABASE() 
                 AND table_name = 'agsc_product_description' 
                 AND index_name = 'idx_ft_product_search');
SET @sqlstmt := IF(@exist > 0, 'ALTER TABLE `agsc_product_description` DROP INDEX `idx_ft_product_search`', 'SELECT 1');
PREPARE stmt FROM @sqlstmt;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Adiciona o novo índice FULLTEXT composto
ALTER TABLE `agsc_product_description` 
ADD FULLTEXT INDEX `idx_ft_product_search` (`name`, `description`, `tag`);
