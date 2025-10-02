<?php
namespace Revmura\Core\Contracts;

interface CptRegistry {
	/** Register a custom post type (deferred to `init`). */
	public function registerPostType( string $slug, array $args ): void;

	/** Register a taxonomy (deferred to `init`). */
	public function registerTaxonomy( string $slug, array $objectTypes, array $args ): void;

	/** Return a normalized snapshot for export/LKG cache. */
	public function snapshot(): array;

	/**
	 * Commit the current registry (hooks into `init` to call `register_*`) and
	 * fires `revmura_core_committed` so the MU Guard receives the LKG snapshot.
	 */
	public function commit(): void;
}
