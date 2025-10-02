<?php
/**
 * REST routes for Revmura Core.
 *
 * @package Revmura\Core
 */

declare(strict_types=1);

namespace Revmura\Core\Rest;

use Revmura\Core\Registry\CptRegistryWp;
use Revmura\Core\Export\Exporter;
use Revmura\Core\Export\Importer;

/**
 * Register REST API routes for Core.
 */
final class Routes {

	/**
	 * Hook all routes into rest_api_init.
	 */
	public static function register(): void {
		add_action(
			'rest_api_init',
			static function (): void {
				// Health route (public).
				register_rest_route(
					'revmura/v1',
					'/health',
					array(
						'methods'             => 'GET',
						'permission_callback' => '__return_true',
						'callback'            => static function () {
							return array(
								'ok'       => true,
								'core_api' => defined( 'REVMURA_CORE_API' ) ? REVMURA_CORE_API : '0.0.0',
								'ver'      => defined( 'REVMURA_CORE_VER' ) ? REVMURA_CORE_VER : '0.0.0',
							);
						},
					)
				);

				// Export route (admin only).
				register_rest_route(
					'revmura/v1',
					'/export',
					array(
						'methods'             => 'GET',
						'permission_callback' => static function (): bool {
							return current_user_can( REVMURA_CAP );
						},
						'callback'            => static function () {
							// NOTE: replace with your live registry instance if you keep one globally.
							$reg  = new CptRegistryWp();
							$json = Exporter::json( $reg );

							return new \WP_REST_Response(
								$json,
								200,
								array(
									'Content-Type' => 'application/json; charset=' . get_option( 'blog_charset' ),
								)
							);
						},
					)
				);

				// Shared permission callback for import endpoints (cap + nonce).
				$import_permission = static function ( \WP_REST_Request $req ) {
					if ( ! current_user_can( REVMURA_CAP ) ) {
						return new \WP_Error( 'rest_forbidden', __( 'Insufficient capability.', 'revmura' ), array( 'status' => 403 ) );
					}
					// Verify nonce from REST header (preferred for REST requests).
					$nonce_header = $req->get_header( 'x_wp_nonce' );
					$nonce        = is_string( $nonce_header ) ? sanitize_text_field( $nonce_header ) : '';
					if ( ! wp_verify_nonce( $nonce, 'revmura_import' ) ) {
						return new \WP_Error( 'rest_forbidden', __( 'Invalid or missing nonce.', 'revmura' ), array( 'status' => 403 ) );
					}
					return true;
				};

				// Dry-run import (validate & diff only).
				register_rest_route(
					'revmura/v1',
					'/import/dry-run',
					array(
						'methods'             => 'POST',
						'permission_callback' => $import_permission,
						'callback'            => static function ( \WP_REST_Request $req ) {
							$body = (string) $req->get_body();
							$data = Importer::parse( $body );
							if ( is_wp_error( $data ) ) {
								return $data;
							}
							// Simple illustrative diff: CPTs to create.
							$create = array_keys( (array) ( $data['cpts'] ?? array() ) );
							$create = array_map( 'sanitize_key', $create );

							return array(
								'ok'      => true,
								'dry_run' => true,
								'diff'    => array(
									'create' => $create,
								),
							);
						},
					)
				);

				// Apply import (commit + flush rewrites).
				register_rest_route(
					'revmura/v1',
					'/import/apply',
					array(
						'methods'             => 'POST',
						'permission_callback' => $import_permission,
						'callback'            => static function ( \WP_REST_Request $req ) {
							$body = (string) $req->get_body();
							$data = Importer::parse( $body );
							if ( is_wp_error( $data ) ) {
								return $data;
							}
							/**
							 * In a full implementation you would feed $data into the live registry
							 * then call ->commit(). For now, we persist snapshot for MU Guard and
							 * flush rewrite rules once.
							 */
							do_action( 'revmura_core_committed', $data );
							flush_rewrite_rules( false );

							return array( 'ok' => true );
						},
					)
				);
			}
		);
	}
}
