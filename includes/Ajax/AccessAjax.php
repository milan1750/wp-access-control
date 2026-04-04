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
use WPAC\Services\ScopeService;
use WPAC\Services\RoleService;
use WPAC\Services\UserCapabilityService;
use WPAC\Repositories\RoleRepository;
use WPAC\Repositories\UserRoleRepository;
use WPAC\Repositories\RoleCapabilityRepository;
use WPAC\Repositories\EntityRepository;
use WPAC\Repositories\ScopeRepository;
use WPAC\Repositories\UserCapabilityRepository;


/**
 * AJAX handlers for access control operations
 *
 * @since 1.0.0
 */
class AccessAjax {


	/**
	 * Init.
	 *
	 * @since 1.0.0
	 */
	public static function init(): void {

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
	 * Get Role caps
	 *
	 * @since 1.0.0
	 */
	public static function get_role_caps() {
		check_ajax_referer( 'wpac_nonce', 'nonce' );

		global $wpdb;
		$role_id        = intval( $_POST['role_id'] ?? 0 );
		$role_cap_table = $wpdb->prefix . 'wpac_role_capabilities';

		if ( ! $role_id ) {
			wp_send_json_error( 'Invalid role' );
		}

		$caps = $wpdb->get_col(
			$wpdb->prepare( "SELECT capability FROM {$role_cap_table} WHERE role_id = %d", $role_id )
		);

		wp_send_json_success( $caps );
	}

	/*
	=========================================================
	* SITE SAVE (CREATE / UPDATE)
	* =========================================================
	*/
	public static function save_site(): void {
		global $wpdb;
			// Verify AJAX nonce
		check_ajax_referer( 'wpac_nonce', 'nonce' );

		$table = $wpdb->prefix . 'wpac_sites';

		$id        = intval( $_POST['id'] ?? 0 );
		$entity_id = intval( $_POST['entity_id'] ?? 0 );
		$site_id   = sanitize_text_field( $_POST['site_id'] ?? '' );
		$name      = sanitize_text_field( $_POST['name'] ?? '' );
		$slug      = sanitize_title( $_POST['slug'] ?? '' );
		$location  = sanitize_text_field( $_POST['location'] ?? '' );
		$status    = isset( $_POST['status'] ) ? intval( $_POST['status'] ) : 1;

		// Duplicate check
		$exists = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} WHERE site_id = %s AND id != %d",
				$site_id,
				$id
			)
		);
		if ( $exists ) {
			self::json_error( 'Site with this ID already exists' );
		}

		$data = array(
			'entity_id' => $entity_id,
			'site_id'   => $site_id,
			'name'      => $name,
			'slug'      => $slug,
			'location'  => $location,
			'status'    => $status,
		);

		if ( $id ) {
			$updated = $wpdb->update( $table, $data, array( 'id' => $id ), array( '%d', '%s', '%s', '%s', '%s', '%d' ), array( '%d' ) );
			if ( $updated === false ) {
				self::json_error( 'Update failed' );
			}
			self::json_success( array_merge( $data, array( 'id' => $id ) ) );
		} else {
			$inserted = $wpdb->insert( $table, $data, array( '%d', '%s', '%s', '%s', '%s', '%d' ) );
			if ( ! $inserted ) {
				self::json_error( 'Insert failed' );
			}
			self::json_success( array_merge( $data, array( 'id' => $wpdb->insert_id ) ) );
		}
	}


	public static function delete_site(): void {

		check_ajax_referer( 'wpac_nonce', 'nonce' );

		global $wpdb;

		$id = (int) ( $_POST['id'] ?? 0 );

		if ( ! $id ) {
			wp_send_json_error( 'Invalid ID' );
		}

		$deleted = $wpdb->delete(
			$wpdb->prefix . 'wpac_sites',
			array( 'id' => $id ),
			array( '%d' )
		);

		if ( ! $deleted ) {
			wp_send_json_error( 'Delete failed' );
		}

		wp_send_json_success( true );
	}

	/*
	=========================================================
	 * HELPERS
	 * ========================================================= */

	private static function json_error( $msg, $code = 400 ) {
		wp_send_json_error( array( 'message' => $msg ), $code );
	}

	private static function json_success( $data = true ) {
		wp_send_json_success( $data );
	}


	/**
	 * Save Entity.
	 */
	public static function save_entity() {

		check_ajax_referer( 'wpac_nonce', 'nonce' );

		$id     = intval( $_POST['id'] ?? 0 );
		$name   = sanitize_text_field( $_POST['name'] ?? '' );
		$slug   = sanitize_title( $_POST['slug'] ?? $name );
		$status = isset( $_POST['status'] ) ? intval( $_POST['status'] ) : 1;

		if ( ! $name ) {
			self::json_error( 'Entity name required' );
		}

		try {

			$service = new EntityService(
				new EntityRepository()
			);

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

		$id = (int) ( $_POST['id'] ?? 0 );

		if ( ! $id ) {
			self::json_error( 'Invalid ID' );
		}

		$enity_repo = new EntityRepository();
		$deleted    = $enity_repo->delete( $id );
		if ( ! $deleted ) {
			self::json_error( 'Delete failed' );
		}
		self::json_success( true );
	}

	/**
	 * Save Scope
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

			$scope_service = new ScopeService(
				new ScopeRepository()
			);

			$scope_service->save( $scope );

			self::json_success( true );

		} catch ( \Exception $e ) {

			self::json_error( $e->getMessage() );

		}
	}

	/**
	 * Delete Scope
	 *
	 * @since 1.0.0
	 */
	public static function delete_scope(): void {

		check_ajax_referer( 'wpac_nonce', 'nonce' );

		try {

			$id = intval( $_POST['id'] ?? 0 );

			$scope_service = new ScopeService(
				new ScopeRepository()
			);

			$scope_service->delete( $id );

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
		// Security check.
		check_ajax_referer( 'wpac_nonce', 'nonce' );

		$id   = intval( $_POST['id'] ?? 0 );
		$name = sanitize_text_field( $_POST['name'] ?? '' );
		$slug = sanitize_title( $_POST['slug'] ?? $name );
		$caps = array_map( 'sanitize_key', $_POST['capabilities'] ?? array() );

		if ( ! $name || ! $slug ) {
			self::json_error( 'Role name and slug are required' );
		}

		$role_repo    = new RoleRepository();
		$cap_repo     = new RoleCapabilityRepository();
		$role_service = new RoleService( $role_repo, $cap_repo );

		$role = new Role(
			array(
				'id'   => $id,
				'name' => $name,
				'slug' => $slug,
			)
		);

		try {
			$role = $role_service->create_or_update( $role, $caps );
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
	 * Delete Role.
	 *
	 * @since 1.0.0
	 */
	public static function delete_role(): void {

		check_ajax_referer( 'wpac_nonce', 'nonce' );

		$id = intval( $_POST['id'] ?? 0 );

		try {
			$role_repo = new RoleRepository();
			$cap_repo  = new RoleCapabilityRepository();
			$user_role = new UserRoleRepository();

			$role_service = new RoleService( $role_repo, $cap_repo, $user_role );
			$role_service->delete( $id );

			self::json_success( true );
		} catch ( \Exception $e ) {
			self::json_error( $e->getMessage() );
		}
	}

	// public static function save_user_caps(): void {
	// Verify nonce for security.
	// check_ajax_referer( 'wpac_nonce', 'nonce' );

	// global $wpdb;

	// Sanitize inputs.
	// $user_id      = isset( $_POST['user_id'] ) ? intval( $_POST['user_id'] ) : 0;
	// $role         = sanitize_text_field( $_POST['role'] ?? '' );
	// $scope        = sanitize_text_field( $_POST['scope'] ?? 'global' );
	// $capabilities = $_POST['capabilities'] ?? array();

	// if ( is_string( $capabilities ) ) {
	// $capabilities = stripslashes( $capabilities ); // remove the \ escaping
	// $capabilities = json_decode( $capabilities, true );
	// }

	// if ( ! is_array( $capabilities ) ) {
	// $capabilities = array();
	// }

	// if ( ! $user_id ) {
	// self::json_error( 'User is required' );
	// }

	// $table = $wpdb->prefix . 'wpac_user_capabilities';

	// Encode capabilities as JSON.
	// $capabilities_json = wp_json_encode( $capabilities );

	// Check if the user already has a record.
	// $exists = $wpdb->get_var(
	// $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE user_id = %d", $user_id )
	// );

	// if ( $exists ) {
	// $updated = $wpdb->update(
	// $table,
	// array(
	// 'role'         => $role,
	// 'scope'        => $scope,
	// 'capabilities' => $capabilities_json,
	// ),
	// array( 'user_id' => $user_id ),
	// array( '%s', '%s', '%s' ),
	// array( '%d' )
	// );

	// Only fail if update returned false (query error)
	// if ( false === $updated ) {
	// self::json_error( 'Failed to update user capabilities' );
	// }
	// } else {
	// $inserted = $wpdb->insert(
	// $table,
	// array(
	// 'user_id'      => $user_id,
	// 'role'         => $role,
	// 'scope'        => $scope,
	// 'capabilities' => $capabilities_json,
	// ),
	// array( '%d', '%s', '%s', '%s' )
	// );

	// if ( ! $inserted ) {
	// self::json_error( 'Failed to assign user capabilities' );
	// }
	// }

	// Return success response
	// self::json_success(
	// array(
	// 'user_id'      => $user_id,
	// 'role'         => $role,
	// 'scope'        => $scope,
	// 'capabilities' => $capabilities,
	// )
	// );
	// }

	public static function save_user_caps(): void {

		check_ajax_referer( 'wpac_nonce', 'nonce' );

		try {

			$capabilities = $_POST['capabilities'] ?? array();

			if ( is_string( $capabilities ) ) {
				$capabilities = json_decode( stripslashes( $capabilities ), true );
			}

			$user_capability = new UserCapability(
				array(
					'user_id'      => intval( $_POST['user_id'] ?? 0 ),
					'role'         => sanitize_text_field( $_POST['role'] ?? '' ),
					'scope'        => sanitize_text_field( $_POST['scope'] ?? 'global' ),
					'capabilities' => is_array( $capabilities ) ? $capabilities : array(),
				)
			);

			$user_cap_service = new UserCapabilityService(
				new UserCapabilityRepository()
			);

			$result = $user_cap_service->save( $user_capability );

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

	public static function revoke_user_caps(): void {

		check_ajax_referer( 'wpac_nonce', 'nonce' );

		try {

			$user_id = intval( $_POST['user_id'] ?? 0 );

			if ( ! $user_id ) {
				self::json_error( 'Invalid user ID' );
			}

			$service = new UserCapabilityService(
				new UserCapabilityRepository()
			);

			$service->revoke( $user_id );

			self::json_success(
				array(
					'user_id' => $user_id,
				)
			);

		} catch ( \Exception $e ) {

			self::json_error( $e->getMessage() );

		}
	}

	/*
	=========================================================
	 * ACCESS
	 * ========================================================= */

	public static function save_access(): void {

		check_admin_referer( 'wpac_save_access' );

		global $wpdb;

		$table = $wpdb->prefix . 'wpac_user_access';

		$user_id = (int) ( $_POST['user_id'] ?? 0 );
		$role    = sanitize_text_field( $_POST['role'] ?? '' );
		$scope   = sanitize_text_field( $_POST['scope'] ?? '' );

		if ( ! $user_id ) {
			wp_die( 'Invalid user' );
		}

		$permissions = wp_json_encode(
			wp_unslash( $_POST['permissions'] ?? array() )
		);

		// delete old
		$wpdb->delete(
			$table,
			array( 'user_id' => $user_id ),
			array( '%d' )
		);

		// insert new
		$wpdb->insert(
			$table,
			array(
				'user_id'     => $user_id,
				'role'        => $role,
				'entity_id'   => null,
				'site_id'     => null,
				'permissions' => $permissions,
				'meta'        => wp_json_encode(
					array(
						'scope' => $scope,
					)
				),
			)
		);

		wp_redirect( admin_url( "admin.php?page=wpac-access&user_id={$user_id}" ) );
		exit;
	}

	public static function get_access(): void {

		check_ajax_referer( 'wpac_nonce', 'nonce' );

		global $wpdb;

		$user_id = (int) ( $_POST['user_id'] ?? 0 );

		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}wpac_user_access WHERE user_id = %d ORDER BY id DESC LIMIT 1",
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

		$meta        = json_decode( $row['meta'] ?? '', true ) ?: array();
		$permissions = json_decode( $row['permissions'] ?? '', true );

		self::json_success(
			array(
				'role'        => $row['role'] ?? '',
				'scope'       => $meta['scope'] ?? '',
				'permissions' => is_array( $permissions ) ? $permissions : array(),
			)
		);
	}

	public static function revoke_access(): void {

		check_ajax_referer( 'wpac_nonce', 'nonce' );

		global $wpdb;

		$user_id = (int) ( $_POST['user_id'] ?? 0 );

		if ( ! $user_id ) {
			self::json_error( 'Invalid user' );
		}

		$wpdb->delete(
			$wpdb->prefix . 'wpac_user_access',
			array( 'user_id' => $user_id ),
			array( '%d' )
		);

		self::json_success(
			array(
				'message' => 'Access revoked',
			)
		);
	}
}
