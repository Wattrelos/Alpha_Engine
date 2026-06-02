<?php
// Query SQL para trazer o endereço montado de forma legível
$sql = "SELECT 
            ca.id AS address_id,
            co.name AS country,
            zo.iso_code AS state_iso,
            ci.name AS city,
            ca.postal_code,
            ca.street,
            ca.number,
            ca.complement,
            ca.district
        FROM agsc_customer_addresses ca
        JOIN agsc_cities ci ON ca.city_id = ci.id
        JOIN agsc_zones zo ON ca.zone_id = zo.id
        JOIN agsc_countries co ON ca.country_id = co.id
        WHERE ca.customer_id = :customer_id";

$stmt = $pdo->prepare($sql); // Ja temos conexão implementada ConnectionDB::getInstance()->getConnection()
$stmt->execute(['customer_id' => 2001]);
$addresses = $stmt->fetchAll();

foreach ($addresses as $addr) {
    echo "Delivery to: {$addr['street']}, {$addr['number']} - {$addr['district']} | {$addr['city']} - {$addr['state_iso']}<br>";
}
