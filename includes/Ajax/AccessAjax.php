<?php

namespace WPAC\Ajax;

defined( 'ABSPATH' ) || exit;

class AccessAjax {

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
	}

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

	/*
	=========================================================
	 * ENTITY
	 * ========================================================= */
	/*
	=========================================================
	* ENTITY SAVE (CREATE + UPDATE)
	* ========================================================= */
	public static function save_entity(): void {

		check_ajax_referer( 'wpac_nonce', 'nonce' );

		global $wpdb;
		$table = $wpdb->prefix . 'wpac_entities';

		// Get POST data
		$id     = intval( $_POST['id'] ?? 0 ); // 0 means new
		$name   = sanitize_text_field( $_POST['name'] ?? '' );
		$slug   = sanitize_title( $_POST['slug'] ?? '' );
		$status = isset( $_POST['status'] ) ? intval( $_POST['status'] ) : 1;

		if ( empty( $name ) ) {
			self::json_error( 'Entity name required' );
		}

		$slug = $slug ?: sanitize_title( $name );

		// Duplicate check: skip current ID if updating
		$exists = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} WHERE slug = %s" . ( $id ? ' AND id != %d' : '' ),
				$id ? array( $slug, $id ) : array( $slug )
			)
		);

		if ( $exists ) {
			self::json_error( 'Entity with this slug already exists' );
		}

		if ( $id ) {
			// UPDATE
			$updated = $wpdb->update(
				$table,
				array(
					'name'   => $name,
					'slug'   => $slug,
					'status' => $status,
				),
				array( 'id' => $id ),
				array( '%s', '%s', '%d' ),
				array( '%d' )
			);

			if ( $updated === false ) {
				self::json_error( 'Update failed' );
			}

			self::json_success(
				array(
					'id'     => $id,
					'name'   => $name,
					'slug'   => $slug,
					'status' => $status,
				)
			);
		} else {
			// INSERT
			$inserted = $wpdb->insert(
				$table,
				array(
					'name'   => $name,
					'slug'   => $slug,
					'status' => $status,
				),
				array( '%s', '%s', '%d' )
			);

			if ( ! $inserted ) {
				self::json_error( 'Insert failed' );
			}

			self::json_success(
				array(
					'id'     => $wpdb->insert_id,
					'name'   => $name,
					'slug'   => $slug,
					'status' => $status,
				)
			);
		}
	}

	public static function delete_entity(): void {

		check_ajax_referer( 'wpac_nonce', 'nonce' );

		global $wpdb;

		$id = (int) ( $_POST['id'] ?? 0 );

		if ( ! $id ) {
			self::json_error( 'Invalid ID' );
		}

		$deleted = $wpdb->delete(
			$wpdb->prefix . 'wpac_entities',
			array( 'id' => $id ),
			array( '%d' )
		);

		if ( ! $deleted ) {
			self::json_error( 'Delete failed' );
		}

		self::json_success( true );
	}

	public static function save_scope(): void {
		check_ajax_referer( 'wpac_nonce', 'nonce' );

		global $wpdb;
		$table = $wpdb->prefix . 'wpac_scopes';

		$id     = intval( $_POST['id'] ?? 0 );
		$name   = sanitize_text_field( $_POST['name'] ?? '' );
		$slug   = sanitize_title( $_POST['slug'] ?? '' );
		$config = wp_unslash( $_POST['config'] ?? '{}' ); // JSON string

		if ( ! $name ) {
			self::json_error( 'Scope name required' );
		}

		$slug = $slug ?: sanitize_title( $name );

		// Check if slug exists for new scope or other scope
		$exists = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} WHERE slug = %s" . ( $id ? ' AND id != %d' : '' ),
				$slug,
				$id
			)
		);

		if ( $exists ) {
			self::json_error( 'Scope slug already exists' );
		}

		if ( $id ) {
			// Update
			$updated = $wpdb->update(
				$table,
				array(
					'name'   => $name,
					'slug'   => $slug,
					'config' => $config,
				),
				array( 'id' => $id ),
				array( '%s', '%s', '%s' ),
				array( '%d' )
			);

			if ( $updated === false ) {
				self::json_error( 'Update failed' );
			}
		} else {
			// Insert
			$inserted = $wpdb->insert(
				$table,
				array(
					'name'   => $name,
					'slug'   => $slug,
					'config' => $config,
				),
				array( '%s', '%s', '%s' )
			);

			if ( ! $inserted ) {
				self::json_error( 'Insert failed' );
			}
		}

		self::json_success( true );
	}

	/*
	=========================================================
	* SCOPE DELETE
	* =========================================================
	*/
	public static function delete_scope(): void {

		check_ajax_referer( 'wpac_nonce', 'nonce' );

		global $wpdb;
		$table = $wpdb->prefix . 'wpac_scopes';

		$id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;

		if ( ! $id ) {
			self::json_error( 'Invalid ID' );
		}

		$deleted = $wpdb->delete(
			$table,
			array( 'id' => $id ),
			array( '%d' )
		);

		if ( ! $deleted ) {
			self::json_error( 'Delete failed' );
		}

		self::json_success( true );
	}

	public static function save_role(): void {

		// Security check
		check_ajax_referer( 'wpac_nonce', 'nonce' );

		global $wpdb;
		$roles_table    = $wpdb->prefix . 'wpac_roles';
		$role_cap_table = $wpdb->prefix . 'wpac_role_capabilities';

		$id   = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
		$name = sanitize_text_field( $_POST['name'] ?? '' );
		$slug = sanitize_title( $_POST['slug'] ?? $name );
		$caps = $_POST['capabilities'] ?? array(); // array of capability keys

		if ( empty( $name ) || empty( $slug ) ) {
			self::json_error( 'Role name and slug are required' );
		}

		// Check for duplicate slug
		if ( $id ) {
			$exists = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$roles_table} WHERE slug = %s AND id != %d",
					$slug,
					$id
				)
			);
		} else {
			$exists = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$roles_table} WHERE slug = %s",
					$slug
				)
			);
		}

		if ( $exists ) {
			self::json_error( 'Role with this slug already exists' );
		}

		// --- Update existing role ---
		if ( $id ) {

			$updated = $wpdb->update(
				$roles_table,
				array(
					'name' => $name,
					'slug' => $slug,
				),
				array( 'id' => $id ),
				array( '%s', '%s' ),
				array( '%d' )
			);

			if ( $updated === false ) {
				self::json_error( 'Failed to update role' );
			}

			// Update capabilities
			$wpdb->delete( $role_cap_table, array( 'role_id' => $id ), array( '%d' ) );

			if ( ! empty( $caps ) ) {
				$insert_data = array();
				foreach ( $caps as $cap ) {
					$insert_data[] = array(
						'role_id'    => $id,
						'capability' => sanitize_key( $cap ),
					);
				}
				foreach ( $insert_data as $row ) {
					$wpdb->insert( $role_cap_table, $row, array( '%d', '%s' ) );
				}
			}

			self::json_success( compact( 'id', 'name', 'slug', 'caps' ) );

		}
		// --- Insert new role ---
		else {

			$inserted = $wpdb->insert(
				$roles_table,
				array(
					'name' => $name,
					'slug' => $slug,
				),
				array( '%s', '%s' )
			);

			if ( ! $inserted ) {
				self::json_error( 'Failed to insert role' );
			}

			$role_id = $wpdb->insert_id;

			if ( ! empty( $caps ) ) {
				$insert_data = array();
				foreach ( $caps as $cap ) {
					$insert_data[] = array(
						'role_id'    => $role_id,
						'capability' => sanitize_key( $cap ),
					);
				}
				foreach ( $insert_data as $row ) {
					$wpdb->insert( $role_cap_table, $row, array( '%d', '%s' ) );
				}
			}

			self::json_success( compact( 'id', 'name', 'slug', 'caps' ) );
		}
	}

	public static function delete_role(): void {

		check_ajax_referer( 'wpac_nonce', 'nonce' );

		global $wpdb;

		$id = (int) ( $_POST['id'] ?? 0 );

		if ( ! $id ) {
			self::json_error( 'Invalid ID' );
		}

		$roles_table     = $wpdb->prefix . 'wpac_roles';
		$role_cap_table  = $wpdb->prefix . 'wpac_role_capabilities';
		$user_role_table = $wpdb->prefix . 'wpac_user_roles';

		// Delete the role
		$deleted = $wpdb->delete(
			$roles_table,
			array( 'id' => $id ),
			array( '%d' )
		);

		if ( ! $deleted ) {
			self::json_error( 'Delete failed' );
		}

		// Delete related role capabilities
		$wpdb->delete(
			$role_cap_table,
			array( 'role_id' => $id ),
			array( '%d' )
		);

		// Delete user role assignments
		$wpdb->delete(
			$user_role_table,
			array( 'role_id' => $id ),
			array( '%d' )
		);

		self::json_success( true );
	}

	public static function save_user_caps(): void {
		// Verify nonce for security
		check_ajax_referer( 'wpac_nonce', 'nonce' );

		global $wpdb;

		// Sanitize inputs
		$user_id      = isset( $_POST['user_id'] ) ? intval( $_POST['user_id'] ) : 0;
		$role         = sanitize_text_field( $_POST['role'] ?? '' );
		$scope        = sanitize_text_field( $_POST['scope'] ?? 'global' );
		$capabilities = $_POST['capabilities'] ?? array();

		if ( is_string( $capabilities ) ) {
			$capabilities = stripslashes( $capabilities ); // remove the \ escaping
			$capabilities = json_decode( $capabilities, true );
		}

		if ( ! is_array( $capabilities ) ) {
			$capabilities = array();
		}

		if ( ! $user_id ) {
			self::json_error( 'User is required' );
		}

		$table = $wpdb->prefix . 'wpac_user_capabilities';

		// Encode capabilities as JSON
		$capabilities_json = wp_json_encode( $capabilities );

		// Check if the user already has a record
		$exists = $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE user_id = %d", $user_id )
		);

		if ( $exists ) {
			$updated = $wpdb->update(
				$table,
				array(
					'role'         => $role,
					'scope'        => $scope,
					'capabilities' => $capabilities_json,
				),
				array( 'user_id' => $user_id ),
				array( '%s', '%s', '%s' ),
				array( '%d' )
			);

			// Only fail if update returned false (query error)
			if ( false === $updated ) {
				self::json_error( 'Failed to update user capabilities' );
			}
		} else {
			$inserted = $wpdb->insert(
				$table,
				array(
					'user_id'      => $user_id,
					'role'         => $role,
					'scope'        => $scope,
					'capabilities' => $capabilities_json,
				),
				array( '%d', '%s', '%s', '%s' )
			);

			if ( ! $inserted ) {
				self::json_error( 'Failed to assign user capabilities' );
			}
		}

		// Return success response
		self::json_success(
			array(
				'user_id'      => $user_id,
				'role'         => $role,
				'scope'        => $scope,
				'capabilities' => $capabilities,
			)
		);
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
