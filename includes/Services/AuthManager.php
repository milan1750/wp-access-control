<?php

namespace WPAC\Services;

defined( 'ABSPATH' ) || exit;

class AuthManager {

	public static function init() {
		add_action( 'init', array( self::class, 'handle_login' ) );
		add_action( 'init', array( self::class, 'handle_logout' ) );
	}

	public static function handle_login() {
		if ( ! isset( $_POST['wpac_login'] ) ) {
			return;
		}

		$creds = array(
			'user_login'    => sanitize_text_field( $_POST['username'] ),
			'user_password' => $_POST['password'],
			'remember'      => true,
		);

		$user = wp_signon( $creds );

		if ( is_wp_error( $user ) ) {
			wp_die( 'Invalid credentials' );
		}

		wp_set_current_user( $user->ID );
		wp_set_auth_cookie( $user->ID );

		$redirect = ! empty( $_GET['redirect_to'] ) ? esc_url_raw( $_GET['redirect_to'] ) : home_url( '/wpac-platform/dashboard' );
		wp_redirect( $redirect );
		exit;
	}

	public static function handle_logout() {
		if ( isset( $_GET['wpac_logout'] ) ) {
			wp_logout();
			wp_redirect( home_url( '/wpac-platform/login' ) );
			exit;
		}
	}
}
