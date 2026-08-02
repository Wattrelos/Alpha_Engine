-- Inserindo Países com suas PKs oficiais da ISO
INSERT IGNORE INTO `agsc_countries` (
        `id`,
        `name`,
        `iso_alpha2`,
        `iso_alpha3`,
        `is_active`
    )
VALUES (76, 'Brasil', 'BR', 'BRA', 1),
    (752, 'Suécia', 'SE', 'SWE', 1),
    -- Condado de Estocolmo
    (208, 'Dinamarca', 'DK', 'DNK', 1),
    (246, 'Finlândia', 'FI', 'FIN', 1),
    (578, 'Noruega', 'NO', 'NOK', 1),
    (826, 'Reino Unido', 'GB', 'GBR', 1);
-- Inclui Inglaterra e Escócia
INSERT INTO `agsc_zones`(`id`, `iso_code`, `country_id`, `name`)
VALUES -- Região da Capital da Dinamarca (Hovedstaden)
    (1, 'DK-84', 208, 'Hovedstaden'),
    -- Região de Uusimaa (Onde fica Helsinque, Finlândia)
    (2, 'FI-18', 246, 'Uusimaa'),
    -- Condado de Oslo (Noruega)
    (3, 'NO-03', 578, 'Oslo'),
    -- Nações constituintes do Reino Unido (Mapeadas como subdivisões ISO)
    (4, 'GB-ENG', 826, 'Inglaterra'),
    (5, 'GB-SCT', 826, 'Escócia');