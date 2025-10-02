<?php
/**
 * Feature flags / runtime switches.
 *
 * @package Revmura\Core
 */

declare(strict_types=1);

namespace Revmura\Core\Support;

final class Flags {

	/**
	 * Safe mode flag.
	 */
	public static function safe_mode(): bool {
		return defined( 'REVMURA_SAFE_MODE' ) && REVMURA_SAFE_MODE;
	}
}
