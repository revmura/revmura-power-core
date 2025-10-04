<?php
/**
 * Exporter: produce a registry snapshot.
 *
 * @package Revmura\Core
 */

declare(strict_types=1);

namespace Revmura\Core\Export;

use Revmura\Core\Facade;

final class Exporter {
	/**
	 * Return the current registry snapshot as an array (JSON-serializable).
	 *
	 * @return array<string,mixed>
	 */
	public static function array(): array {
		// Prefer the live registry's snapshot if available.
		if ( is_callable( array( Facade::cpt(), 'snapshot' ) ) ) {
			/** @var array<string,mixed> $snap */
			$snap = Facade::cpt()->snapshot();
			return $snap;
		}
		// Fallback minimal structure.
		return array(
			'schema_version' => '1.0',
			'cpts'           => array(),
			'taxes'          => array(),
		);
	}

	/**
	 * Return JSON string (pretty).
	 */
	public static function json(): string {
		return wp_json_encode( self::array(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
	}
}
