<?php
namespace Revmura\Core\Export;

use WP_Error;

final class Importer {
	/** Validate JSON payload; return array or WP_Error. */
	public static function parse( string $json ) {
		$max = 1024 * 1024 * 2; // 2MB guard
		if ( strlen( $json ) > $max ) {
			return new WP_Error( 'revmura_import_size', 'Import too large.' );
		}
		$data = json_decode( $json, true );
		if ( ! is_array( $data ) || ! isset( $data['schema_version'] ) ) {
			return new WP_Error( 'revmura_import_schema', 'Invalid schema.' );
		}
		return $data;
	}
}
