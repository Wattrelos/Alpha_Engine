INSERT INTO agsc_suppliers (company_name, trade_name, tax_id, email, phone, is_active) VALUES
('Atacadão de Eletrônicos S.A.', 'Eletrônicos Atacado', '12345678000101', 'comercial@eletratacado.com', '11999991111', 1),
('Distribuidora de Variedades Ltda', 'Mega Distri', '98765432000199', 'vendas@megadistri.com', '11999992222', 1);


INSERT INTO agsc_contacts (name, email, phone, position, is_active) VALUES
('Carlos Silva', 'carlos.silva@emailvendedor.com', '11988883333', 'Gerente de Contas', 1),
('Mariana Costa', 'mariana.costa@emailvendedor.com', '11988884444', 'Consultor Técnico', 1),
('Roberto Souza', 'roberto.souza@emailvendedor.com', '11988885555', 'Executivo de Vendas', 1);

// Tabela teste:
INSERT INTO agsc_brands (name, is_active) VALUES
('Fame', 1),
('Tigre', 1),
('Krona', 1),
('Radial', 1);
('DECA', 1);

INSERT INTO `agsc_brand`(`id`, `name`, `image`, `is_active` ) VALUES 
(311, 'LIZ Cimentos', 'image/manufacturer/empresa_de_cimentos_liz_sa_logo.jpeg', 1),
(313, 'Cerâmica Almeida', 'image/manufacturer/manufacturer_1780934619_6a26e7dbddab6.png', 1),
(316, 'Grupo Formigres', 'image/manufacturer/manufacturer_1780934874_6a26e8dae7567.webp', 1),
(317, 'Incopisos', 'image/manufacturer/manufacturer_1780935068_6a26e99cbd676.png', 1);



INSERT INTO agsc_supplier_contact_brands (supplier_id, contact_id, brand_id) VALUES
-- Cenário 1: Fornecedor 1 (Eletrônicos Atacado)
(1, 1, 1), -- Carlos atende Fame no Fornecedor 1
(1, 1, 2), -- Carlos atende Tigre no Fornecedor 1

-- Cenário 2: Fornecedor 1 (Eletrônicos Atacado)
(1, 2, 3), -- Mariana atende Krona no Fornecedor 1

-- Cenário 3: Fornecedor 2 (Mega Distri)
(2, 1, 4), -- Carlos também trabalha no Fornecedor 2, atendendo Radial

-- Cenário 4: Fornecedor 2 (Mega Distri)
(2, 3, 1), -- Roberto atende Fame no Fornecedor 2
(2, 3, 3); -- Roberto atende Krona no Fornecedor 2
