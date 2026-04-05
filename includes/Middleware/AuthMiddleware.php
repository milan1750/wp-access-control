<?php
/**
 * Authentication Middleware.
 *
 * @package WPAC
 */

namespace WPAC\Middleware;

defined( 'ABSPATH' ) || exit;

/**
 * Middleware to ensure user is authenticated before accessing certain routes.
 *
 * @since 1.0.0
 */
class AuthMiddleware {

	/***
	 * Require user to be logged in.
	 *
	 * @return void
	 */
	public static function require_login() {
		if ( ! is_user_logged_in() ) {
			wp_safe_redirect( home_url( '/wpac-platform/login' ) );
			exit;
		}
	}
}
