<?php
require '/var/www/html/agsonhos/config.php';
$c = mysqli_connect(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_PORT);
$res = mysqli_query($c, 'SHOW TABLES');
while ($t = mysqli_fetch_row($res)) {
    $table = $t[0];
    $cols_res = mysqli_query($c, "SHOW COLUMNS FROM `$table`");
    while ($col = mysqli_fetch_assoc($cols_res)) {
        $type = strtolower($col['Type']);
        if (strpos($type, 'varchar') !== false || strpos($type, 'text') !== false) {
            $field = $col['Field'];
            $chk = mysqli_query($c, "SELECT COUNT(*) FROM `$table` WHERE `$field` LIKE 'img/%'");
            if ($chk) {
                $count = mysqli_fetch_row($chk)[0];
                if ($count > 0) {
                    echo "Table: $table, Column: $field, Count: $count\n";
                }
            }
        }
    }
}
