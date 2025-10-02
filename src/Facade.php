<?php
/**
 * Facade for Core registries.
 *
 * @package Revmura\Core
 */

declare(strict_types=1);

namespace Revmura\Core;

use Revmura\Core\Registry\CptRegistryWp;
use Revmura\Core\Registry\MetaRegistryWp;

final class Facade {

	/** @var CptRegistryWp|null */
	private static ?CptRegistryWp $cpt = null;

	/** @var MetaRegistryWp|null */
	private static ?MetaRegistryWp $meta = null;

	public static function cpt(): CptRegistryWp {
		if ( null === self::$cpt ) {
			self::$cpt = new CptRegistryWp();
		}
		return self::$cpt;
	}

	public static function meta(): MetaRegistryWp {
		if ( null === self::$meta ) {
			self::$meta = new MetaRegistryWp();
		}
		return self::$meta;
	}
}
