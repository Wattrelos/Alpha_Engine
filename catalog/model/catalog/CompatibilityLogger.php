<?php
namespace Alpha\Support;

/**
 * Class CompatibilityLogger
 *
 * Utilitário para rastrear tentativas de carregamento de componentes legados.
 * 
 * @package Alpha\Support
 */
class CompatibilityLogger {
	/**
	 * Registra uma tentativa de carregamento de modelo desativado.
	 *
	 * @param string $model_route A rota do modelo (ex: 'account/customer')
	 *
	 * @return void
	 */
	public static function log(string $model_route): void {
		$log_file = DIR_LOGS . 'alpha_compatibility.log';
		
		$ip = function_exists('oc_get_ip') ? oc_get_ip() : ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
		$url = $_SERVER['REQUEST_URI'] ?? 'CLI/Cron';
		$timestamp = date('Y-m-d H:i:s');
		
		$message = sprintf(
			"[%s] [IP: %s] ATTEMPTED LEGACY LOAD: %s | Context URL: %s" . PHP_EOL,
			$timestamp,
			$ip,
			$model_route,
			$url
		);
		
		file_put_contents($log_file, $message, FILE_APPEND);
	}
}