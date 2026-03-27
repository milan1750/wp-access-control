<?php

namespace WPAC\Admin\Controllers;

defined( 'ABSPATH' ) || exit;

/**
 * Handles Role admin actions.
 */
class RoleController {

	public function register_hooks(): void {
		add_action( 'admin_menu', array( $this, 'menu' ) );
	}

	public function menu(): void {
		add_menu_page(
			'Access Roles',
			'Access Roles',
			'manage_options',
			'wpac-roles',
			array( $this, 'render' )
		);
	}

	public function render(): void {
		include WPAC_PLUGIN_DIR . 'src/Admin/Views/roles.php';
	}
}
