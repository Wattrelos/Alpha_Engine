-- Tabela de Contatos (Vendedores)
CREATE TABLE agsc_contacts (
    id BIGINT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(120),
    phone VARCHAR(50),
    position VARCHAR(100),
    -- Ex: Vendedor, Gerente de Conta
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);