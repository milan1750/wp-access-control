<?php

namespace WPAC\Admin\Controllers;

defined( 'ABSPATH' ) || exit;

/**
 * Handles user access admin screen.
 */
class UserAccessController {

	public function register_hooks(): void {
		add_action( 'admin_menu', array( $this, 'menu' ) );
	}

	public function menu(): void {
		add_menu_page(
			'User Access',
			'User Access',
			'manage_options',
			'wpac-users',
			array( $this, 'render' )
		);
	}

	public function render(): void {
		include WPAC_PLUGIN_DIR . 'src/Admin/Views/users.php';
	}
}
