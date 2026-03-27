<?php

namespace WPAC\Admin;

defined( 'ABSPATH' ) || exit;

use WPAC\Core\PermissionRegistry;

class Menu {

	/**
	 * Boot admin menu
	 */
	public function init(): void {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Register WP Admin Menu
	 */
	public function register_menu(): void {

		add_menu_page(
			'WPAC RBAC',
			'Access Control',
			'manage_options',
			'wpac-menu',
			array( $this, 'render_users_page' ),
			'dashicons-lock',
			26
		);

		// ================= ROLES =================
		add_submenu_page(
			'wpac-menu',
			'Roles',
			'Roles',
			'manage_options',
			'wpac-roles',
			array( $this, 'render_roles_page' )
		);

		// ================= ENTITIES =================
		add_submenu_page(
			'wpac-menu',
			'Entities',
			'Entities',
			'manage_options',
			'wpac-entities',
			array( $this, 'render_entities_page' )
		);

		// ================= SITES =================
		add_submenu_page(
			'wpac-menu',
			'Sites',
			'Sites',
			'manage_options',
			'wpac-sites',
			array( $this, 'render_sites_page' )
		);

		// ================= SCOPES =================
		add_submenu_page(
			'wpac-menu',
			'Scopes',
			'Scopes',
			'manage_options',
			'wpac-scopes',
			array( $this, 'render_scopes_page' )
		);
	}

	public function enqueue_assets( $hook ): void {
		$wpac_scripts = array(
			'toplevel_page_wpac-menu'           => 'users.js',
			'access-control_page_wpac-entities' => 'entities.js',
			'access-control_page_wpac-sites'    => 'sites.js',
			'access-control_page_wpac-roles'    => 'roles.js',
			'access-control_page_wpac-scopes'   => 'scopes.js',
		);

		if ( isset( $wpac_scripts[ $hook ] ) ) {
			$script_file = $wpac_scripts[ $hook ];

			wp_enqueue_script(
				'wpac-admin-' . $hook,
				WPAC_PLUGIN_URL . 'assets/admin/' . $script_file,
				array( 'jquery', 'wpac-swal' ),
				'1.0',
				true
			);

			// Enqueue styles
			wp_enqueue_style(
				'wpac-admin',
				WPAC_PLUGIN_URL . 'assets/admin/admin.css',
				array(),
				'1.0'
			);

			wp_enqueue_style(
				'wpac-swal',
				WPAC_PLUGIN_URL . 'assets/sweetalert2/sweetalert2.css',
				array(),
				'11.0'
			);

			wp_enqueue_script(
				'wpac-swal',
				WPAC_PLUGIN_URL . 'assets/sweetalert2/sweetalert2.all.min.js',
				array( 'jquery' ),
				'11.0',
				true
			);

			// --- PASS AJAX URL & NONCE ---
			$data = array(
				'ajax'  => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'wpac_nonce' ),
			);

			if ( $hook === 'toplevel_page_wpac-menu' ) {
				global $wpdb;

				// ROLES CAPABILITIES
				$roles_table    = $wpdb->prefix . 'wpac_roles';
				$role_cap_table = $wpdb->prefix . 'wpac_role_capabilities';
				$roles          = $wpdb->get_results( "SELECT * FROM {$roles_table} ORDER BY id DESC" );
				$roles_caps     = array();
				foreach ( $roles as $role ) {
					$caps                      = $wpdb->get_col(
						$wpdb->prepare( "SELECT capability FROM {$role_cap_table} WHERE role_id = %d", $role->id )
					);
					$roles_caps[ $role->slug ] = $caps;
				}

				// USER OVERRIDES (capabilities)
				$users          = get_users();
				$user_overrides = array();
				foreach ( $users as $user ) {
					$row = $wpdb->get_row(
						$wpdb->prepare(
							"SELECT * FROM {$wpdb->prefix}wpac_user_capabilities WHERE user_id = %d",
							$user->ID
						),
						ARRAY_A
					);

					if ( $row ) {
						$capabilities = $row['capabilities'];

						// If stored as JSON
						if ( ! empty( $capabilities ) ) {
							$capabilities = json_decode( $capabilities, true );
						}

						// Fallback to empty array if decoding failed
						if ( ! is_array( $capabilities ) ) {
							$capabilities = array();
						}

						error_log( print_r( $capabilities, true ) );
						$user_overrides[ $user->ID ] = array(
							'role'         => $row['role'],
							'scope'        => $row['scope'] ?? 'global',
							'capabilities' => $capabilities, // flat array from DB
						);
					}
				}

				$data['rolesCaps']     = $roles_caps;
				$data['userOverrides'] = $user_overrides;
			}

			wp_localize_script(
				'wpac-admin-' . $hook,
				'WPAC',
				$data
			);
		}
	}

	// =================================================
	// USERS PAGE (MAIN)
	// =================================================
	public function render_users_page(): void {

		global $wpdb;

		$users = get_users(
			array(
				'fields' => array( 'ID', 'display_name', 'user_email' ),
			)
		);

		$selected_user = isset( $_GET['user_id'] )
			? (int) $_GET['user_id']
			: 0;

		$role        = '';
		$scope       = '';
		$permissions = array();

		if ( $selected_user ) {

			$role  = get_user_meta( $selected_user, '_wpac_role', true );
			$scope = get_user_meta( $selected_user, '_wpac_scope', true );

			$permissions = get_user_meta( $selected_user, '_wpac_permissions', true );

			if ( ! is_array( $permissions ) ) {
				$permissions = json_decode( $permissions, true ) ?: array();
			}
		}

		$modules = PermissionRegistry::all();

		include WPAC_PLUGIN_DIR . '/includes/Admin/Views/users.php';
	}

	// =================================================
	// ROLES PAGE
	// =================================================
	public function render_roles_page(): void {

		global $wpdb;

		$roles = $wpdb->get_results(
			"SELECT * FROM {$wpdb->prefix}wpac_roles"
		);

		$modules = PermissionRegistry::all();

		include WPAC_PLUGIN_DIR . '/includes/Admin/Views/roles.php';
	}

	// =================================================
	// ENTITIES PAGE
	// =================================================
	public function render_entities_page(): void {

		global $wpdb;

		$entities = $wpdb->get_results(
			"SELECT * FROM {$wpdb->prefix}wpac_entities"
		);

		include WPAC_PLUGIN_DIR . '/includes/Admin/Views/entities.php';
	}

	// =================================================
	// SITES PAGE
	// =================================================
	public function render_sites_page(): void {

		global $wpdb;

		$entities = $wpdb->get_results(
			"SELECT * FROM {$wpdb->prefix}wpac_entities"
		);

		$sites = $wpdb->get_results(
			"SELECT * FROM {$wpdb->prefix}wpac_sites"
		);

		include WPAC_PLUGIN_DIR . '/includes/Admin/Views/sites.php';
	}

	// =================================================
	// SCOPES PAGE
	// =================================================
	public function render_scopes_page(): void {

		global $wpdb;

		$scopes = $wpdb->get_results(
			"SELECT * FROM {$wpdb->prefix}wpac_scopes"
		);

		include WPAC_PLUGIN_DIR . '/includes/Admin/Views/scopes.php';
	}
}
