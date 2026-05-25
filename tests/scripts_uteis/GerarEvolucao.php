<?php
/**
 * Script executor para o EvolutionGenerator.
 * Uso no terminal: php tests/scripts_uteis/GerarEvolucao.php
 */

$rootDir = dirname(__DIR__, 2);
require_once $rootDir . '/system/engine/autoloader.php';

$autoloader = new \Opencart\System\Engine\Autoloader();
$autoloader->register('Alpha', $rootDir . '/core/');

use Alpha\Support\EvolutionGenerator;

echo "🔍 Iniciando a varredura do Git em busca de commits [EVO]...\n";
$generator = new EvolutionGenerator();
$generator->run();
echo "✅ Processo concluído.\n";