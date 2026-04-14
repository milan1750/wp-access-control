<?php
/**
 * AccessAjax
 *
 * @package WPAC
 * @since 1.0.0
 */

namespace WPAC\Ajax;

defined( 'ABSPATH' ) || exit;

use WPAC\Models\Role;
use WPAC\Models\Entity;
use WPAC\Models\Scope;
use WPAC\Models\UserCapability;
use WPAC\Services\EntityService;
use WPAC\Services\RoleService;
use WPAC\Services\SiteService;
use WPAC\Services\ScopeService;
use WPAC\Services\UserCapabilityService;

/**
 * AJAX handlers for access control operations
 *
 * @since 1.0.0
 */
class Ajax {

	/**
	 * Init.
	 *
	 * @since 1.0.0
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpac_save_access', array( self::class, 'save_access' ) );
		add_action( 'wp_ajax_wpac_get_access', array( self::class, 'get_access' ) );
		add_action( 'wp_ajax_wpac_revoke_access', array( self::class, 'revoke_access' ) );

		add_action( 'wp_ajax_wpac_save_role', array( self::class, 'save_role' ) );
		add_action( 'wp_ajax_wpac_delete_role', array( self::class, 'delete_role' ) );

		add_action( 'wp_ajax_wpac_save_entity', array( self::class, 'save_entity' ) );
		add_action( 'wp_ajax_wpac_delete_entity', array( self::class, 'delete_entity' ) );

		add_action( 'wp_ajax_wpac_save_site', array( self::class, 'save_site' ) );
		add_action( 'wp_ajax_wpac_delete_site', array( self::class, 'delete_site' ) );

		add_action( 'wp_ajax_wpac_save_scope', array( self::class, 'save_scope' ) );
		add_action( 'wp_ajax_wpac_delete_scope', array( self::class, 'delete_scope' ) );

		add_action( 'wp_ajax_wpac_get_role_caps', array( self::class, 'get_role_caps' ) );
		add_action( 'wp_ajax_wpac_save_user_caps', array( self::class, 'save_user_caps' ) );
		add_action( 'wp_ajax_wpac_revoke_user_caps', array( self::class, 'revoke_user_caps' ) );
	}

	/**
	 * Get Role Capabilities.
	 *
	 * @since 1.0.0
	 */
	public static function get_role_caps(): void {
		check_ajax_referer( 'wpac_nonce', 'nonce' );

		$role_id = intval( $_POST['role_id'] ?? 0 );

		if ( ! $role_id ) {
			self::json_error( 'Invalid role' );
		}

		try {
			// Get the RoleService from container or instantiate.
			$role_service = wpac()->container()->get( RoleService::class );

			// Fetch capabilities for the given role.
			$caps = $role_service->get_role_capabilities( $role_id );

			self::json_success( $caps );
		} catch ( \Exception $e ) {
			self::json_error( $e->getMessage() );
		}
	}

	/**
	 * Save Site.
	 *
	 * @since 1.0.0
	 */
	public static function save_site(): void {
		check_ajax_referer( 'wpac_nonce', 'nonce' );

		$id        = intval( $_POST['id'] ?? 0 );
		$entity_id = intval( $_POST['entity_id'] ?? 0 );
		$site_id   = sanitize_text_field( $_POST['site_id'] ?? '' );
		$name      = sanitize_text_field( $_POST['name'] ?? '' );
		$slug      = sanitize_title( $_POST['slug'] ?? $name );
		$location  = sanitize_text_field( $_POST['location'] ?? '' );
		$status    = isset( $_POST['status'] ) ? intval( $_POST['status'] ) : 1;

		// Get the SiteService from container.
		$service = wpac()->container()->get( SiteService::class );

		if ( ! $service->is_valid_site_id( $site_id ) ) {
			self::json_error( 'Invalid site ID format' );
		}

		try {
			// Create a Site model object.
			$site = new \WPAC\Models\Site(
				array(
					'id'        => $id,
					'entity_id' => $entity_id,
					'site_id'   => $site_id,
					'name'      => $name,
					'slug'      => $slug,
					'location'  => $location,
					'status'    => $status,
				)
			);

			// Save using the service.
			$saved_site = $service->save( $site );

			self::json_success(
				array(
					'id'        => $saved_site->id,
					'entity_id' => $saved_site->entity_id,
					'site_id'   => $saved_site->site_id,
					'name'      => $saved_site->name,
					'slug'      => $saved_site->slug,
					'location'  => $saved_site->location,
					'status'    => $saved_site->status,
				)
			);
		} catch ( \Exception $e ) {
			self::json_error( $e->getMessage() );
		}
	}


	/**
	 * Delete Site.
	 *
	 * @since 1.0.0
	 */
	public static function delete_site(): void {
		check_ajax_referer( 'wpac_nonce', 'nonce' );

		$id = intval( $_POST['id'] ?? 0 );
		if ( ! $id ) {
			self::json_error( 'Invalid ID' );
		}

		try {
			// Get SiteService from container.
			$service = wpac()->container()->get( SiteService::class );

			$deleted = $service->delete( $id );

			if ( ! $deleted ) {
				self::json_error( 'Delete failed' );
			}

			self::json_success( true );
		} catch ( \Exception $e ) {
			self::json_error( $e->getMessage() );
		}
	}

	/**
	 * Save Entity.
	 *
	 * @since 1.0.0
	 */
	public static function save_entity(): void {
		check_ajax_referer( 'wpac_nonce', 'nonce' );

		$id     = intval( $_POST['id'] ?? 0 );
		$name   = sanitize_text_field( $_POST['name'] ?? '' );
		$slug   = sanitize_title( $_POST['slug'] ?? $name );
		$status = isset( $_POST['status'] ) ? intval( $_POST['status'] ) : 1;

		if ( ! $name ) {
			self::json_error( 'Entity name required' );
		}

		try {
			$service = wpac()->container()->get( EntityService::class );

			$entity = new Entity(
				array(
					'id'     => $id,
					'name'   => $name,
					'slug'   => $slug,
					'status' => $status,
				)
			);

			$entity = $service->create_or_update( $entity );

			self::json_success(
				array(
					'id'     => $entity->id,
					'name'   => $entity->name,
					'slug'   => $entity->slug,
					'status' => $entity->status,
				)
			);
		} catch ( \Exception $e ) {
			self::json_error( $e->getMessage() );
		}
	}

	/**
	 * Delete Entity.
	 *
	 * @since 1.0.0
	 */
	public static function delete_entity(): void {
		check_ajax_referer( 'wpac_nonce', 'nonce' );

		$id = intval( $_POST['id'] ?? 0 );
		if ( ! $id ) {
			self::json_error( 'Invalid ID' );
		}

		try {
			$service = wpac()->container()->get( EntityService::class );
			$service->delete( $id );

			self::json_success( true );
		} catch ( \Exception $e ) {
			self::json_error( $e->getMessage() );
		}
	}

	/**
	 * Save Scope.
	 *
	 * @since 1.0.0
	 */
	public static function save_scope(): void {
		check_ajax_referer( 'wpac_nonce', 'nonce' );

		try {
			$scope = new Scope(
				array(
					'id'     => intval( $_POST['id'] ?? 0 ),
					'name'   => sanitize_text_field( $_POST['name'] ?? '' ),
					'slug'   => sanitize_title( $_POST['slug'] ?? '' ),
					'config' => wp_unslash( $_POST['config'] ?? '{}' ),
				)
			);

			$service = wpac()->container()->get( ScopeService::class );
			$service->save( $scope );

			self::json_success( true );
		} catch ( \Exception $e ) {
			self::json_error( $e->getMessage() );
		}
	}

	/**
	 * Delete Scope.
	 *
	 * @since 1.0.0
	 */
	public static function delete_scope(): void {
		check_ajax_referer( 'wpac_nonce', 'nonce' );

		try {
			$id = intval( $_POST['id'] ?? 0 );

			$service = wpac()->container()->get( ScopeService::class );
			$service->delete( $id );

			self::json_success( true );
		} catch ( \Exception $e ) {
			self::json_error( $e->getMessage() );
		}
	}

	/**
	 * Save Role.
	 *
	 * @since 1.0.0
	 */
	public static function save_role(): void {
		check_ajax_referer( 'wpac_nonce', 'nonce' );

		$id   = intval( $_POST['id'] ?? 0 );
		$name = sanitize_text_field( $_POST['name'] ?? '' );
		$slug = sanitize_title( $_POST['slug'] ?? $name );
		$caps = array_map( 'sanitize_key', $_POST['capabilities'] ?? array() );

		if ( ! $name || ! $slug ) {
			self::json_error( 'Role name and slug are required' );
		}

		try {
			$service = wpac()->container()->get( RoleService::class );

			$role = new Role(
				array(
					'id'   => $id,
					'name' => $name,
					'slug' => $slug,
				)
			);
			$role = $service->create_or_update( $role, $caps );

			self::json_success(
				array(
					'id'   => $role->id,
					'name' => $role->name,
					'slug' => $role->slug,
					'caps' => $caps,
				)
			);
		} catch ( \Exception $e ) {
			self::json_error( $e->getMessage() );
		}
	}

	/**
	 * Delete Role
	 *
	 * @since 1.0.0
	 */
	public static function delete_role(): void {
		check_ajax_referer( 'wpac_nonce', 'nonce' );

		$id = intval( $_POST['id'] ?? 0 );
		if ( ! $id ) {
			self::json_error( 'Invalid ID' );
		}

		try {
			$service = wpac()->container()->get( RoleService::class );
			$service->delete( $id );

			self::json_success( true );
		} catch ( \Exception $e ) {
			self::json_error( $e->getMessage() );
		}
	}

	/**
	 * Save User Caps.
	 *
	 * @since 1.0.0
	 */
	public static function save_user_caps(): void {
		check_ajax_referer( 'wpac_nonce', 'nonce' );

		try {
			$capabilities = $_POST['capabilities'] ?? array();
			if ( is_string( $capabilities ) ) {
				$capabilities = json_decode( stripslashes( $capabilities ), true );
			}

			$user_cap = new UserCapability(
				array(
					'user_id'      => intval( $_POST['user_id'] ?? 0 ),
					'role'         => sanitize_text_field( $_POST['role'] ?? '' ),
					'scope'        => sanitize_text_field( $_POST['scope'] ?? 'global' ),
					'capabilities' => is_array( $capabilities ) ? $capabilities : array(),
				)
			);

			$service = wpac()->container()->get( UserCapabilityService::class );
			$result  = $service->save( $user_cap );

			self::json_success(
				array(
					'user_id'      => $result->user_id,
					'role'         => $result->role,
					'scope'        => $result->scope,
					'capabilities' => $result->capabilities,
				)
			);
		} catch ( \Exception $e ) {
			self::json_error( $e->getMessage() );
		}
	}

	/**
	 * Revoke User Caps.
	 *
	 * @since 1.0.0
	 */
	public static function revoke_user_caps(): void {
		check_ajax_referer( 'wpac_nonce', 'nonce' );

		$user_id = intval( $_POST['user_id'] ?? 0 );
		if ( ! $user_id ) {
			self::json_error( 'Invalid user ID' );
		}

		try {
			$service = wpac()->container()->get( UserCapabilityService::class );
			$service->revoke( $user_id );

			self::json_success( array( 'user_id' => $user_id ) );
		} catch ( \Exception $e ) {
			self::json_error( $e->getMessage() );
		}
	}

	/**
	 * Save Access.
	 *
	 * @since 1.0.0
	 */
	public static function save_access(): void {
		check_admin_referer( 'wpac_save_access' );
		global $wpdb;

		$user_id = intval( $_POST['user_id'] ?? 0 );
		$role    = sanitize_text_field( $_POST['role'] ?? '' );
		$scope   = sanitize_text_field( $_POST['scope'] ?? '' );

		if ( ! $user_id ) {
			wp_die( 'Invalid user' );
		}

		$permissions = wp_json_encode( wp_unslash( $_POST['permissions'] ?? array() ) );

		$table = $wpdb->prefix . 'wpac_user_access';

		$wpdb->delete( $table, array( 'user_id' => $user_id ), array( '%d' ) );
		$wpdb->insert(
			$table,
			array(
				'user_id'     => $user_id,
				'role'        => $role,
				'entity_id'   => null,
				'site_id'     => null,
				'permissions' => $permissions,
				'meta'        => wp_json_encode( array( 'scope' => $scope ) ),
			)
		);

		wp_safe_redirect( admin_url( "admin.php?page=wpac-access&user_id={$user_id}" ) );
		exit;
	}


	/**
	 * Get Access.
	 *
	 * @since 1.0.0
	 */
	public static function get_access(): void {
		check_ajax_referer( 'wpac_nonce', 'nonce' );
		global $wpdb;

		$user_id = intval( $_POST['user_id'] ?? 0 );

		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}wpac_user_access WHERE user_id=%d ORDER BY id DESC LIMIT 1",
				$user_id
			),
			ARRAY_A
		);

		if ( ! $row ) {
			self::json_success(
				array(
					'role'        => '',
					'scope'       => '',
					'permissions' => array(),
				)
			);
		}

		$meta        = json_decode( $row['meta'] ?? '{}', true );
		$permissions = json_decode( $row['permissions'] ?? '[]', true );

		self::json_success(
			array(
				'role'        => $row['role'] ?? '',
				'scope'       => $meta['scope'] ?? '',
				'permissions' => is_array( $permissions ) ? $permissions : array(),
			)
		);
	}

	/**
	 * Revoke Access.
	 *
	 * @since 1.0.0
	 */
	public static function revoke_access(): void {
		check_ajax_referer( 'wpac_nonce', 'nonce' );
		global $wpdb;

		$user_id = intval( $_POST['user_id'] ?? 0 );
		if ( ! $user_id ) {
			self::json_error( 'Invalid user' );
		}

		$wpdb->delete( $wpdb->prefix . 'wpac_user_access', array( 'user_id' => $user_id ), array( '%d' ) );

		self::json_success( array( 'message' => 'Access revoked' ) );
	}

	/**
	 * Json Error.
	 *
	 * @since 1.0.0
	 *
	 * @param  string $msg String.
	 * @param  int    $code Code.
	 */
	private static function json_error( $msg, $code = 400 ) {
		wp_send_json_error( array( 'message' => $msg ), $code );
	}

	/**
	 * Json Success.
	 *
	 * @since 1.0.0
	 *
	 * @param  bool $data Data.
	 */
	private static function json_success( $data = true ) {
		wp_send_json_success( $data );
	}
}
