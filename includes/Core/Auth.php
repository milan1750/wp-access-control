<?php
/**
 * Auth File.
 *
 * @package WP_Platform_Access_Control
 */

namespace WPAC\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Runs authentication tasks.
 */
class Auth {

	public static function init() {
		add_shortcode( 'wpac_login', array( self::class, 'login_shortcode' ) );
	}

	public static function login_shortcode() {

		if ( is_user_logged_in() ) {
			return '<p>You are already logged in.</p>';
		}

		ob_start();

		include WPAC_PLUGIN_PATH . 'includes/Admin/Views/login.php';

		return ob_get_clean();
	}
}
