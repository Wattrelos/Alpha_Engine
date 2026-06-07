<?php
require '/var/www/html/agsonhos/vendor/autoload.php';
define('APPLICATION', 'catalog');
require_once '/var/www/html/agsonhos/config.php';
echo "Checking ShowSetupAction existence and namespace:\n";
if (class_exists(\Alpha\Admin\Controllers\Actions\Auth\ShowSetupAction::class)) {
    echo "ShowSetupAction compiled and loaded successfully.\n";
} else {
    echo "ShowSetupAction class not found!\n";
}
echo "\nChecking SetupAction existence and namespace:\n";
if (class_exists(\Alpha\Admin\Controllers\Actions\Auth\SetupAction::class)) {
    echo "SetupAction compiled and loaded successfully.\n";
} else {
    echo "SetupAction class not found!\n";
}
echo "\nChecks passed successfully!\n";
