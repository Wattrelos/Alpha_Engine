-- ==============================================================================
-- Alpha Engine: Seed Data para o Portal do Prestador de Serviços (RFQ / BoQ)
-- ==============================================================================

-- 1. Perfil do Prestador de Serviços (Carlos Silva - ID 16694)
INSERT INTO `agsc_service_provider_profile`
  (`id`, `customer_id`, `company_name`, `trade_name`, `document_number`, `specialties`, `service_radius_km`, `latitude`, `longitude`, `address_cep`, `address_city`, `address_state`, `rating`, `total_reviews`, `status`, `date_added`)
VALUES
  (1, 16694, 'Silva Reformas & Construções Eireli', 'Silva Empreiteira & Acabamentos', '12345678000199', 'Alvenaria Estrutural, Pisos e Porcelanatos, Pintura, Hidráulica, Drywall', 35.00, -23.55051990, -46.63330940, '01001-000', 'São Paulo', 'SP', 4.95, 18, 1, NOW())
ON DUPLICATE KEY UPDATE `trade_name` = VALUES(`trade_name`);

-- 2. Projeto RFQ 1 (Aberto - 2.5 km de distância)
INSERT INTO `agsc_project_rfq`
  (`id`, `customer_id`, `title`, `category`, `description`, `address_cep`, `address_street`, `address_number`, `address_neighborhood`, `address_city`, `address_state`, `latitude`, `longitude`, `budget_expectation`, `desired_deadline_days`, `status`, `date_added`)
VALUES
  (1, 16695, 'Reforma de Banheiro e Troca de Revestimento - 12m²', 'Reformas & Revestimentos', 'Demolição do piso e azulejos antigos, impermeabilização de área molhada do box, assentamento de porcelanato 60x60 no chão e paredes, instalação de nicho embutido e novos pontos de hidráulica para ducha higiênica e misturador monocomando.', '01310-100', 'Avenida Paulista', '1000', 'Bela Vista', 'São Paulo', 'SP', -23.56141400, -46.65588190, 4500.0000, 15, 'open', NOW())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`);

-- 3. Projeto RFQ 2 (Aberto - 6.4 km de distância)
INSERT INTO `agsc_project_rfq`
  (`id`, `customer_id`, `title`, `category`, `description`, `address_cep`, `address_street`, `address_number`, `address_neighborhood`, `address_city`, `address_state`, `latitude`, `longitude`, `budget_expectation`, `desired_deadline_days`, `status`, `date_added`)
VALUES
  (2, 16696, 'Instalação Elétrica Completa e Quadro de Distribuição - Sobrado', 'Elétrica & Infraestrutura', 'Troca de fiação antiga com passagem de cabos antichama de 2,5mm e 4mm, instalação de novo QDC com 16 disjuntores DIN e DPS, aterramento e instalação de 42 tomadas e interruptores modulares.', '04538-133', 'Rua Joaquim Floriano', '466', 'Itaim Bibi', 'São Paulo', 'SP', -23.58550000, -46.67820000, 6200.0000, 20, 'open', NOW())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`);

-- 4. Projeto RFQ 3 (Homologado para Carlos Silva - Awarded para Takeoff Tool)
INSERT INTO `agsc_project_rfq`
  (`id`, `customer_id`, `title`, `category`, `description`, `address_cep`, `address_street`, `address_number`, `address_neighborhood`, `address_city`, `address_state`, `latitude`, `longitude`, `budget_expectation`, `desired_deadline_days`, `status`, `selected_provider_id`, `date_added`)
VALUES
  (3, 16697, 'Construção de Espaço Gourmet com Churrasqueira e Bancada', 'Construção & Lazer', 'Alvenaria com tijolo aparente para churrasqueira, balcão em L com acabamento em granito São Gabriel, piso em porcelanato acetinado e forro de gesso acartonado.', '02012-000', 'Rua Voluntários da Pátria', '1200', 'Santana', 'São Paulo', 'SP', -23.50420000, -46.62750000, 12000.0000, 30, 'awarded', 1, NOW())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`);

-- 5. Proposta Comercial Aceita do RFQ 3
INSERT INTO `agsc_project_bid`
  (`id`, `rfq_id`, `provider_id`, `labor_price`, `estimated_duration_days`, `proposal_notes`, `status`, `date_added`)
VALUES
  (1, 3, 1, 9500.0000, 25, 'Equipe com 1 encarregado e 2 pedreiros experientes. Fornecimento de ferramentas especializadas, nivelamento a laser e descarte de resíduos.', 'accepted', NOW())
ON DUPLICATE KEY UPDATE `labor_price` = VALUES(`labor_price`);

-- 6. Lista de Materiais (BoQ) do RFQ 3
INSERT INTO `agsc_project_boq`
  (`id`, `rfq_id`, `provider_id`, `title`, `notes`, `total_estimated_amount`, `status`, `date_added`)
VALUES
  (1, 3, 1, 'Levantamento de Materiais - Espaço Gourmet', 'Lista elaborada com base no projeto arquitetônico', 3840.5000, 'draft', NOW())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`);

-- 7. Itens do BoQ
INSERT INTO `agsc_project_boq_item`
  (`id`, `boq_id`, `product_id`, `item_name`, `unit`, `quantity`, `unit_price`, `total_price`, `notes`, `date_added`)
VALUES
  (1, 1, NULL, 'Cimento CP II-E-32 50kg Votoran', 'saco', 30.0000, 34.9000, 1047.0000, 'Para fundação e estrutura', NOW()),
  (2, 1, NULL, 'Areia Média Lavada', 'm³', 4.0000, 120.0000, 480.0000, 'Para argamassa de assentamento', NOW()),
  (3, 1, NULL, 'Argamassa AC-III Cinza 20kg Quartzolit', 'saco', 15.0000, 42.9000, 643.5000, 'Para piso e revestimentos', NOW()),
  (4, 1, NULL, 'Tijolo Aparente Vermelho Refratário', 'un', 850.0000, 1.9600, 1670.0000, 'Para corpo da churrasqueira', NOW())
ON DUPLICATE KEY UPDATE `item_name` = VALUES(`item_name`);
