<?php
/**
 * Importer: parse and validate incoming JSON.
 *
 * @package Revmura\Core
 */

declare(strict_types=1);

namespace Revmura\Core\Export;

final class Importer {

	/** @var array<string,bool> */
	private static array $supports_whitelist = array(
		'title'           => true,
		'editor'          => true,
		'thumbnail'       => true,
		'revisions'       => true,
		'excerpt'         => true,
		'author'          => true,
		'page-attributes' => true,
		'custom-fields'   => true,
		'comments'        => true,
		'trackbacks'      => true,
	);

	/**
	 * Parse and validate incoming JSON. Return array or WP_Error.
	 *
	 * @param string $body Raw JSON body.
	 * @return array|\WP_Error
	 */
	public static function parse( string $body ) {
		if ( '' === $body || strlen( $body ) > 512 * 1024 ) { // 512KB guard.
			return new \WP_Error( 'revmura_import_size', __( 'Import JSON is empty or too large.', 'revmura' ), array( 'status' => 400 ) );
		}

		$data = json_decode( $body, true );
		if ( ! is_array( $data ) ) {
			return new \WP_Error( 'revmura_import_json', __( 'Invalid JSON.', 'revmura' ), array( 'status' => 400 ) );
		}

		// Schema version.
		$ver = (string) ( $data['schema_version'] ?? '' );
		if ( '1.0' !== $ver ) {
			return new \WP_Error( 'revmura_import_schema', __( 'Unsupported schema_version.', 'revmura' ), array( 'status' => 400 ) );
		}

		// Normalize top-level.
		$cpts  = is_array( $data['cpts'] ?? null ) ? $data['cpts'] : array();
		$taxes = is_array( $data['taxes'] ?? null ) ? $data['taxes'] : array();

		$out_cpts  = array();
		$out_taxes = array();

		// Validate CPTs.
		foreach ( $cpts as $slug => $args ) {
			$slug = sanitize_key( (string) $slug );
			if ( '' === $slug ) {
				continue;
			}
			$args = is_array( $args ) ? $args : array();

			$label    = isset( $args['label'] ) ? (string) $args['label'] : $slug;
			$supports = array();
			if ( isset( $args['supports'] ) && is_array( $args['supports'] ) ) {
				foreach ( $args['supports'] as $s ) {
					$k = is_string( $s ) ? sanitize_key( $s ) : '';
					if ( isset( self::$supports_whitelist[ $k ] ) ) {
						$supports[] = $k;
					}
				}
				$supports = array_values( array_unique( $supports ) );
			}

			$rewrite = array();
			if ( isset( $args['rewrite'] ) && is_array( $args['rewrite'] ) ) {
				$slug_rewrite   = isset( $args['rewrite']['slug'] ) ? sanitize_title( (string) $args['rewrite']['slug'] ) : '';
				$with_front_raw = $args['rewrite']['with_front'] ?? false;
				$with_front     = ( true === $with_front_raw || '1' === $with_front_raw || 1 === $with_front_raw );
				$rewrite        = array(
					'slug'       => ( '' !== $slug_rewrite ) ? $slug_rewrite : $slug,
					'with_front' => $with_front,
				);
			}

			$out_cpts[ $slug ] = array(
				'label'    => $label,
				'supports' => $supports,
				'rewrite'  => $rewrite,
			);
		}

		// Validate taxes (basic skeleton).
		foreach ( $taxes as $slug => $def ) {
			$slug = sanitize_key( (string) $slug );
			if ( '' === $slug ) {
				continue;
			}
			$def = is_array( $def ) ? $def : array();

			$object_type = array();
			if ( isset( $def['object_type'] ) && is_array( $def['object_type'] ) ) {
				foreach ( $def['object_type'] as $pt ) {
					$pt = sanitize_key( (string) $pt );
					if ( '' !== $pt ) {
						$object_type[] = $pt;
					}
				}
				$object_type = array_values( array_unique( $object_type ) );
			}
			$args               = is_array( $def['args'] ?? null ) ? $def['args'] : array();
			$out_taxes[ $slug ] = array(
				'object_type' => $object_type ?: array( 'post' ),
				'args'        => $args,
			);
		}

		return array(
			'schema_version' => '1.0',
			'cpts'           => $out_cpts,
			'taxes'          => $out_taxes,
		);
	}
}
