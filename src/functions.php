<?php
/**
 * Global helper functions for modules (autoloaded).
 *
 * @package Revmura\Core
 */

declare(strict_types=1);

use Revmura\Core\Facade;

/**
 * Register CPT via Core.
 *
 * @param string               $slug CPT slug.
 * @param array<string,mixed>  $args Args.
 */
function revmura_register_cpt( string $slug, array $args ): void {
	Facade::cpt()->registerPostType( $slug, $args );
}

/**
 * Register taxonomy via Core.
 *
 * @param string               $slug         Taxonomy slug.
 * @param array<int,string>    $object_types Object types.
 * @param array<string,mixed>  $args         Args.
 */
function revmura_register_tax( string $slug, array $object_types, array $args ): void {
	Facade::cpt()->registerTaxonomy( $slug, $object_types, $args );
}

/**
 * Register post meta fields via Core.
 *
 * @param string              $post_type Post type.
 * @param array<int,array>    $fields    Field definitions.
 */
function revmura_register_meta( string $post_type, array $fields ): void {
	Facade::meta()->register( $post_type, $fields );
}

/**
 * Commit the current registry (calls register_* on init and writes LKG).
 */
function revmura_commit(): void {
	Facade::cpt()->commit();
}
