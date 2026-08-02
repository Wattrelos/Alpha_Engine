SELECT s.id AS SupplierId,
    s.trade_name AS SupplierName,
    sa.street AS Street,
    sa.number AS Number,
    sa.postal_code AS PostalCode,
    c.name AS CityName,
    z.iso_code AS ZoneIso,
    co.name AS CountryName
FROM agsc_suppliers s
    JOIN agsc_supplier_addresses sa ON s.id = sa.supplier_id
    JOIN agsc_geo_cities c ON sa.city_id = c.id
    JOIN agsc_geo_zones z ON sa.zone_id = z.id
    JOIN agsc_geo_countries co ON sa.country_id = co.id
WHERE s.is_active = 1;