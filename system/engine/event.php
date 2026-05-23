<?php
/**
 * @package		OpenCart
 *
 * @author		Daniel Kerr
 * @copyright	Copyright (c) 2005 - 2022, OpenCart, Ltd. (https://www.opencart.com/)
 * @license		https://opensource.org/licenses/GPL-3.0
 *
 * @see		https://www.opencart.com
 */
namespace Opencart\System\Engine;
/**
 * Class Event
 *
 * https://github.com/opencart/opencart/wiki/Events-(script-notifications)-2.2.x.x
 */
class Event {
	/**
	 * @var \Opencart\System\Engine\Registry
	 */
	protected \Opencart\System\Engine\Registry $registry;
	/**
	 * @var array<int, array<string, mixed>>
	 */
	protected array $data = [];

	/**
	 * Constructor
	 *
	 * @param \Opencart\System\Engine\Registry $registry
	 */
	public function __construct(\Opencart\System\Engine\Registry $registry) {
		$this->registry = $registry;
	}

	/**
	 * Register
	 *
	 * @param string                         $trigger
	 * @param \Opencart\System\Engine\Action $action
	 * @param int                            $priority
	 *
	 * @return void
	 */
	public function register(string $trigger, \Opencart\System\Engine\Action $action, int $priority = 0): void {
		$this->data[] = [
			'trigger'  => $trigger,
			'action'   => $action,
			'priority' => $priority,
			// Alpha Engine: Pré-compilação da Regex para evitar sobrecarga de CPU no trigger
			'regex'    => '/^' . str_replace(['\*', '\?'], ['.*', '.'], preg_quote($trigger, '/')) . '/'
		];

		$sort_order = [];

		foreach ($this->data as $key => $value) {
			$sort_order[$key] = $value['priority'];
		}

		array_multisort($sort_order, SORT_ASC, $this->data);
	}

	/**
	 * Trigger
	 *
	 * @param string       $event
	 * @param array<mixed> $args
	 *
	 * @return mixed
	 */
	public function trigger(string $event, array $args = []) {
		// Alpha Engine: Event Loop / Recursion Trap
		static $event_recursion_depth = [];
		$event_recursion_depth[$event] = ($event_recursion_depth[$event] ?? 0) + 1;
		
		if ($event_recursion_depth[$event] > 30) {
			throw new \Exception("Alpha Engine Trap: O evento '{$event}' entrou em recursão infinita (mais de 30 loops). Verifique as extensões ativas.");
		}

		try {
			foreach ($this->data as $value) {
				// Alpha Engine: Usa a regex pré-compilada para economizar operações de string massivas
				$pattern = $value['regex'] ?? '/^' . str_replace(['\*', '\?'], ['.*', '.'], preg_quote($value['trigger'], '/')) . '/';
				
				if (preg_match($pattern, $event)) {
					$value['action']->execute($this->registry, $args);
				}
			}
		} finally {
			$event_recursion_depth[$event]--;
		}

		return '';
	}

	/**
	 * Unregister
	 *
	 * @param string $trigger
	 * @param string $route
	 *
	 * @return void
	 */
	public function unregister(string $trigger, string $route): void {
		foreach ($this->data as $key => $value) {
			if ($trigger == $value['trigger'] && $value['action']->getId() == $route) {
				unset($this->data[$key]);
			}
		}
	}

	/**
	 * Clear
	 *
	 * @param string $trigger
	 *
	 * @return void
	 */
	public function clear(string $trigger): void {
		foreach ($this->data as $key => $value) {
			if ($trigger == $value['trigger']) {
				unset($this->data[$key]);
			}
		}
	}
}
