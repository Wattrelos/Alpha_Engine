-- Tabela de Relacionamento Triplo (N:N:N)
CREATE TABLE agsc_supplier_contact_manufacturer (
    supplier_id BIGINT NOT NULL,
    contact_id BIGINT NOT NULL,
    manufacturer_id BIGINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    -- Chave Primária Composta
    PRIMARY KEY (supplier_id, contact_id, manufacturer_id),
    -- Chaves Estrangeiras com exclusão em cascata
    CONSTRAINT fk_scb_supplier FOREIGN KEY (supplier_id) REFERENCES agsc_suppliers(id) ON DELETE CASCADE,
    CONSTRAINT fk_scb_contact FOREIGN KEY (contact_id) REFERENCES agsc_contacts(id) ON DELETE CASCADE,
    CONSTRAINT fk_scb_manufacturer FOREIGN KEY (manufacturer_id) REFERENCES agsc_manufacturers(id) ON DELETE CASCADE
);
-- Índices para otimizar as consultas de busca
CREATE INDEX idx_scb_supplier ON agsc_supplier_contact_manufacturers(supplier_id);
CREATE INDEX idx_scb_contact ON agsc_supplier_contact_manufacturers(contact_id);
CREATE INDEX idx_scb_manufacturer ON agsc_supplier_contact_manufacturers(manufacturer_id);