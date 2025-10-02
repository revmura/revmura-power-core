<?php
/**
 * Healthcheck utility.
 *
 * @package Revmura\Core
 */

declare(strict_types=1);

namespace Revmura\Core\Health;

final class Healthcheck {

	/**
	 * Return minimal health signal.
	 *
	 * @return array{ok:bool}
	 */
	public static function report(): array {
		return array( 'ok' => true );
	}
}
