<?php

namespace Alpha\Support;

/**
 * Class RequestHelper
 *
 * Centraliza a detecção e higienização de metadados de requisição HTTP,
 * garantindo consistência em toda a Alpha Engine.
 *
 * @package Alpha\Support
 */
class RequestHelper {
	/**
	 * Obtém a URL completa da requisição atual.
	 *
	 * @param array<string, mixed> $server Array de dados do servidor (ex: $this->request->server)
	 *
	 * @return string
	 */
	public static function getFullUrl(array $server): string {
		if (!isset($server['HTTP_HOST']) || !isset($server['REQUEST_URI'])) {
			return '';
		}

		$protocol = (isset($server['HTTPS']) && ($server['HTTPS'] === 'on' || $server['HTTPS'] == '1' || $server['HTTPS'] === true)) ? 'https://' : 'http://';

		return $protocol . $server['HTTP_HOST'] . $server['REQUEST_URI'];
	}

	/**
	 * Obtém a URL de origem (Referer) da requisição.
	 *
	 * @param array<string, mixed> $server
	 *
	 * @return string
	 */
	public static function getReferer(array $server): string {
		return $server['HTTP_REFERER'] ?? '';
	}

	/**
	 * Verifica se o Referer pertence ao domínio atual.
	 *
	 * @param array<string, mixed> $server
	 *
	 * @return bool
	 */
	public static function isInternalReferer(array $server): bool {
		$referer = self::getReferer($server);

		if (!$referer || !isset($server['HTTP_HOST'])) {
			return false;
		}

		// Extrai o host de ambas as fontes para uma comparação limpa (sem portas ou protocolos)
		return parse_url($referer, PHP_URL_HOST) === parse_url('https://' . $server['HTTP_HOST'], PHP_URL_HOST);
	}
}