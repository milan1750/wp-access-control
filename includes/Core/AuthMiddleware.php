<?php
namespace WPAC\Core;

defined( 'ABSPATH' ) || exit;

class AuthMiddleware {
	public static function require_login() {
		if ( ! is_user_logged_in() ) {
			wp_safe_redirect( home_url( '/wpac-platform/login' ) );
			exit;
		}
	}
}
