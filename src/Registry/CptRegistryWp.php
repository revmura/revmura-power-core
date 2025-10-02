<?php
namespace Revmura\Core\Registry;

use Revmura\Core\Contracts\CptRegistry;

final class CptRegistryWp implements CptRegistry {
	private array $cpts  = array();
	private array $taxes = array();

	public function registerPostType( string $slug, array $args ): void {
		$slug                = sanitize_key( $slug );
		$args                = is_array( $args ) ? $args : array();
		$this->cpts[ $slug ] = $args;
	}

	public function registerTaxonomy( string $slug, array $objectTypes, array $args ): void {
		$slug                 = sanitize_key( $slug );
		$objectTypes          = array_map( 'sanitize_key', (array) $objectTypes );
		$args                 = is_array( $args ) ? $args : array();
		$this->taxes[ $slug ] = array(
			'object_type' => $objectTypes,
			'args'        => $args,
		);
	}

	public function snapshot(): array {
		return array(
			'schema_version' => '1.0',
			'cpts'           => $this->cpts,
			'taxes'          => $this->taxes,
		);
	}

	public function commit(): void {
		add_action(
			'init',
			function () {
				foreach ( $this->cpts as $slug => $args ) {
					$defaults = array(
						'show_in_rest' => true,
						'public'       => true,
					);
					register_post_type( $slug, array_merge( $defaults, $args ) );
				}
				foreach ( $this->taxes as $slug => $data ) {
					$args     = $data['args'] ?? array();
					$types    = $data['object_type'] ?? array( 'post' );
					$defaults = array(
						'show_in_rest' => true,
						'public'       => true,
					);
					register_taxonomy( $slug, $types, array_merge( $defaults, $args ) );
				}
			},
			0
		);

		/** let MU Guard capture LKG */
		do_action( 'revmura_core_committed', $this->snapshot() );
	}
}
