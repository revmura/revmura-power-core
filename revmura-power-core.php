<?php
/**
 * Plugin Name: Revmura Power Core
 * Description: Engine for CPT/Tax schemas, meta registry, REST, import/export, caps, and LKG cache. No UI.
 * Version: 1.0.0
 * Requires at least: 6.5
 * Requires PHP: 8.3
 * Author: Saleh Bamatraf
 * License: GPL-2.0-or-later
 * Text Domain: revmura
 *
 * @package Revmura\Core
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const REVMURA_CORE_VER = '1.0.0';
const REVMURA_CORE_API = '1.0.0';
const REVMURA_CAP      = 'revmura_manage_core';

// Composer autoload.
$autoload = __DIR__ . '/vendor/autoload.php';
if ( is_readable( $autoload ) ) {
	require_once $autoload;
}

/**
 * Map capability to administrators on activation.
 */
register_activation_hook(
	__FILE__,
	static function (): void {
		$role = get_role( 'administrator' );
		if ( $role && ! $role->has_cap( REVMURA_CAP ) ) {
			$role->add_cap( REVMURA_CAP );
		}
	}
);

/**
 * Healthy boot flag (MU Guard reads this).
 */
add_action(
	'plugins_loaded',
	static function (): void {
		if ( ! defined( 'REVMURA_CORE_OK' ) ) {
			define( 'REVMURA_CORE_OK', true );
		}
	}
);

/**
 * Write Last-Known-Good cache when the registry commits.
 *
 * @param array<string,mixed> $snapshot Registry snapshot.
 */
add_action(
	'revmura_core_committed',
	static function ( array $snapshot ): void {
		$json = wp_json_encode( $snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
		if ( empty( $json ) ) {
			return;
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		WP_Filesystem();
		global $wp_filesystem;

		$dir  = wp_normalize_path( WP_CONTENT_DIR . '/uploads/revmura' );
		$path = trailingslashit( $dir ) . 'cpt-cache.json';

		if ( wp_mkdir_p( $dir ) && $wp_filesystem instanceof \WP_Filesystem_Base ) {
			// Use WP_Filesystem (WPCS: no direct file writes).
			$wp_filesystem->put_contents( $path, $json, FS_CHMOD_FILE );
		}
	}
);
