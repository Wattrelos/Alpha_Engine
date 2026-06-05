<?php
require '/var/www/html/agsonhos/config.php';
$c = mysqli_connect(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_PORT);

echo "Settings containing 'img/':\n";
$res = mysqli_query($c, "SELECT `id`, `key`, `value` FROM `tbkk_setting` WHERE `value` LIKE 'img/%'");
while ($row = mysqli_fetch_assoc($res)) {
    print_r($row);
}

// Perform updates
echo "\nUpdating tbkk_product...\n";
$upd1 = mysqli_query($c, "UPDATE `tbkk_product` SET `image` = REPLACE(`image`, 'img/', 'image/') WHERE `image` LIKE 'img/%'");
echo "Affected rows: " . mysqli_affected_rows($c) . "\n";

echo "Updating tbkk_setting...\n";
$upd2 = mysqli_query($c, "UPDATE `tbkk_setting` SET `value` = REPLACE(`value`, 'img/', 'image/') WHERE `value` LIKE 'img/%'");
echo "Affected rows: " . mysqli_affected_rows($c) . "\n";
