<?php
namespace Revmura\Core\Export;

use Revmura\Core\Registry\CptRegistryWp;

final class Exporter {
	public static function json( CptRegistryWp $reg ): string {
		return (string) wp_json_encode( $reg->snapshot(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
	}
}
