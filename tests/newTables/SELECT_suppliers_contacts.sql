SELECT s.trade_name AS fornecedor,
    c.name AS vendedor,
    b.name AS marca_atendida
FROM agsc_supplier_contact_manufacturer scb
    JOIN agsc_suppliers s ON scb.supplier_id = s.id
    JOIN agsc_contact c ON scb.contact_id = c.id
    JOIN agsc_manufacturer b ON scb.manufacturer_id = b.id
WHERE scb.supplier_id = 1;