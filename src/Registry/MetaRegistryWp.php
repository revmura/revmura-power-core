<?php
namespace Revmura\Core\Registry;

use Revmura\Core\Contracts\MetaRegistry;

final class MetaRegistryWp implements MetaRegistry {
	public function register( string $postType, array $fields ): void {
		foreach ( $fields as $f ) {
			$key = sanitize_key( (string) ( $f['key'] ?? '' ) );
			if ( ! $key ) {
				continue; }
			$type     = in_array( $f['type'] ?? '', array( 'string', 'number', 'boolean', 'integer', 'array', 'object' ), true )
			? $f['type'] : 'string';
			$single   = isset( $f['single'] ) ? (bool) $f['single'] : true;
			$sanitize = $f['sanitize'] ?? null;

			register_post_meta(
				$postType,
				$key,
				array(
					'type'              => $type,
					'single'            => $single,
					'show_in_rest'      => true,
					'auth_callback'     => fn() => current_user_can( 'edit_posts' ),
					'sanitize_callback' => is_string( $sanitize ) ? $sanitize : null,
				)
			);
		}
	}
}
