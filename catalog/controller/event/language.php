<?php
namespace Opencart\Catalog\Controller\Event;

class Language extends \Opencart\System\Engine\Controller {
	
	// Alpha Engine Failsafe: Pilha em memória para evitar JSON Explosion
	private static array $backupStack = [];

	public function before(string &$route, array &$args): void {
		// Salvamos o array inteiro usando ponteiros de memória RAM (custo zero de bytes)
		self::$backupStack[] = $this->language->all();
		
		$this->language->load($route);
	}

	public function after(string &$route, array &$args, mixed &$output): void {
		if (!empty(self::$backupStack)) {
			$backup = array_pop(self::$backupStack);
			
			$this->language->clear();
			
			// Restaura o estado anterior instantaneamente
			foreach ($backup as $key => $value) {
				$this->language->set($key, $value);
			}
		}
	}
}
