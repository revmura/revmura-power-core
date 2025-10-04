<?php
/**
 * Uninstall handler for Revmura Power Core.
 *
 * Removes the LKG cache and the custom capability from all roles.
 * Safe for single site and multisite.
 *
 * @package Revmura\Core
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Define here because plugin constants aren't loaded during uninstall.
if ( ! defined( 'REVMURA_CAP' ) ) {
	define( 'REVMURA_CAP', 'revmura_manage_core' );
}

require_once ABSPATH . 'wp-admin/includes/file.php';
WP_Filesystem();
global $wp_filesystem;

/**
 * Delete the LKG cache file for the current site.
 */
$delete_site_cache = static function (): void use ( $wp_filesystem ) {
	$uploads = wp_upload_dir( null, false ); // Current site uploads (multisite-aware).
	if ( ! empty( $uploads['error'] ) || empty( $uploads['basedir'] ) ) {
		return;
	}
	$basedir = (string) $uploads['basedir'];
	$dir     = trailingslashit( wp_normalize_path( $basedir ) ) . 'revmura';
	$file    = trailingslashit( $dir ) . 'cpt-cache.json';

	if ( $wp_filesystem instanceof \WP_Filesystem_Base ) {
		if ( $wp_filesystem->exists( $file ) ) {
			$wp_filesystem->delete( $file );
		}
		// Best-effort: remove directory if empty (fails silently if not empty).
		$wp_filesystem->rmdir( $dir );
	}
};

// Single site or multisite.
if ( is_multisite() ) {
	$site_ids = get_sites(
		array(
			'fields' => 'ids',
			'number' => 0,
		)
	);
	foreach ( $site_ids as $blog_id ) {
		switch_to_blog( (int) $blog_id );
		$delete_site_cache();
		restore_current_blog();
	}
} else {
	$delete_site_cache();
}

// Remove our capability from all roles.
if ( function_exists( 'wp_roles' ) ) {
	$wp_roles = wp_roles();
	if ( $wp_roles instanceof \WP_Roles ) {
		foreach ( array_keys( $wp_roles->roles ) as $role_name ) {
			$role = get_role( $role_name );
			if ( $role && $role->has_cap( REVMURA_CAP ) ) {
				$role->remove_cap( REVMURA_CAP );
			}
		}
	}
}

// (Optional) If you ever store options/transients, delete them here.
// Example:
// foreach ( array_keys( wp_load_alloptions() ) as $k ) {
//     if ( str_starts_with( $k, 'revmura_' ) ) {
//         delete_option( $k );
//     }
// }
