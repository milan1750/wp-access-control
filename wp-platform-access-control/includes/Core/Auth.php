<?php
/**
 * Authentication Manager.
 *
 * @package WP_Platform_Access_Control
 */

namespace WPAC\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Handles user authentication, including login and logout processes.
 *
 * @since 1.0.0
 */
class Auth {

	/**
	 * Init.
	 *
	 * @since 1.0.0
	 */
	public static function init() {
		self::handle_login();
		self::handle_logout();
	}

	/**
	 * Handle user login.
	 *
	 * @since 1.0.0
	 */
	public static function handle_login() {

		if ( ! isset( $_POST['wpac_login_nonce'] ) || ! wp_verify_nonce( $_POST['wpac_login_nonce'], 'wpac_login_action' ) ) {
			return;
		}

		// Nonce is valid, proceed with login handling.
		if ( ! isset( $_POST['wpac_login'] ) ) {
			return;
		}

		$creds = array(
			'user_login'    => sanitize_text_field( $_POST['wpac_username'] ),
			'user_password' => $_POST['wpac_password'],
			'remember'      => true,
		);

		$user = wp_signon( $creds );

		if ( is_wp_error( $user ) ) {
			wp_die( 'Invalid credentials' );
		}

		wp_set_current_user( $user->ID );
		wp_set_auth_cookie( $user->ID );

		$redirect = ! empty( $_GET['redirect_to'] ) ? esc_url_raw( $_GET['redirect_to'] ) : home_url( '/wpac-platform/dashboard' );
		wp_safe_redirect( $redirect );
		exit;
	}

	/**
	 * Handle user logout.
	 *
	 * @since 1.0.0
	 */
	public static function handle_logout() {
		if ( isset( $_GET['wpac_logout'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			wp_logout();
			wp_safe_redirect( home_url( '/wpac-platform/login' ) );
			exit;
		}
	}
}
