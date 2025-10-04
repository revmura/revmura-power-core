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

if ( ! ( $wp_filesystem instanceof \WP_Filesystem_Base ) ) {
	// Cannot safely modify files; abort quietly.
	return;
}

/**
 * Delete the LKG cache file for the current site.
 *
 * @return void
 */
function revmura_core_delete_current_site_cache(): void {
	global $wp_filesystem;

	$uploads = wp_upload_dir( null, false ); // Current site uploads (multisite-aware).
	if ( ! empty( $uploads['error'] ) || empty( $uploads['basedir'] ) ) {
		return;
	}

	$basedir = (string) $uploads['basedir'];
	$dir     = trailingslashit( wp_normalize_path( $basedir ) ) . 'revmura';
	$file    = trailingslashit( $dir ) . 'cpt-cache.json';

	// Delete the cache file if present (use WP_Filesystem to satisfy WPCS).
	if ( $wp_filesystem->exists( $file ) ) {
		$wp_filesystem->delete( $file );
	}

	// Best-effort: remove directory if empty (silently fails if not empty).
	$wp_filesystem->rmdir( $dir );
}

// Delete cache on all sites (or the single site).
if ( is_multisite() ) {
	$site_ids = get_sites(
		array(
			'fields' => 'ids',
			'number' => 0,
		)
	);
	foreach ( $site_ids as $site_id ) {
		switch_to_blog( (int) $site_id );
		revmura_core_delete_current_site_cache();
		restore_current_blog();
	}
} else {
	revmura_core_delete_current_site_cache();
}

// Remove our capability from all roles.
if ( function_exists( 'wp_roles' ) ) {
	$roles = wp_roles();
	if ( $roles instanceof \WP_Roles ) {
		foreach ( array_keys( $roles->roles ) as $role_name ) {
			$role_obj = get_role( $role_name );
			if ( $role_obj && $role_obj->has_cap( REVMURA_CAP ) ) {
				$role_obj->remove_cap( REVMURA_CAP );
			}
		}
	}
}
