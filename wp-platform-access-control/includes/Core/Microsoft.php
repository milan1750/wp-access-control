<?php
/**
 * Activator File.
 *
 * @package WP_Platform_Access_Control
 */

namespace WPAC\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Handles Microsoft Authentication.
 */
class Microsoft {

	/**
	 * Initialize Microsoft login routes.
	 */
	public static function init(): void {
		self::check_request();
		self::redirect_to_microsoft_login();
	}

	/**
	 * Check if the current request is for Microsoft login callback and handle authentication.
	 */
	public static function check_request(): void {
		if ( ! str_contains( $_SERVER['REQUEST_URI'], '/wpac-microsoft-callback' ) ) {
			return;
		}

		if ( empty( $_GET['code'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			wp_die( 'Microsoft login failed: missing code.' );
		}

		$code     = sanitize_text_field( $_GET['code'] );
		$settings = get_option( 'wrm_auth_settings', array() );

		$tenant        = $settings['tenant_id'] ?? '';
		$client_id     = $settings['client_id'] ?? '';
		$client_secret = $settings['client_secret'] ?? '';
		$redirect_uri  = $settings['redirect_uri'] ?? home_url( '/wpac-microsoft-callback' );

		// Request access token from Microsoft.
		$response = wp_safe_remote_post(
			"https://login.microsoftonline.com/$tenant/oauth2/v2.0/token",
			array(
				'body'    => array(
					'client_id'     => $client_id,
					'client_secret' => $client_secret,
					'code'          => $code,
					'grant_type'    => 'authorization_code',
					'redirect_uri'  => $redirect_uri,
				),
				'headers' => array(
					'Content-Type' => 'application/x-www-form-urlencoded',
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			wp_die( 'Token request failed: ' . esc_html( $response->get_error_message() ) );
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( empty( $body['access_token'] ) ) {
			wp_die( 'Token error: access_token missing' );
		}

		// Get user profile from Microsoft Graph.
		$user_response = wp_safe_remote_get(
			'https://graph.microsoft.com/v1.0/me',
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $body['access_token'],
				),
			)
		);

		if ( is_wp_error( $user_response ) ) {
			wp_die( 'Failed to fetch user profile: ' . esc_html( $user_response->get_error_message() ) );
		}

		$profile = json_decode( wp_remote_retrieve_body( $user_response ), true );
		$email   = $profile['mail'] ?? $profile['userPrincipalName'] ?? '';

		if ( empty( $email ) ) {
			wp_die( 'Email not found in Microsoft profile.' );
		}

		$wp_user = get_user_by( 'email', $email );

		if ( ! $wp_user ) {
			wp_die( 'User with Email ' . esc_html( $email ) . ' not found.' );
		}

		wp_set_current_user( $wp_user->ID );
		wp_set_auth_cookie( $wp_user->ID );

		wp_safe_redirect( home_url( '/wpac-platform/dashboard' ) );
		exit;
	}

	/**
	 * Redirect to Microsoft login page.
	 */
	public static function redirect_to_microsoft_login(): void {
		if ( ! str_contains( $_SERVER['REQUEST_URI'], '/wpac-microsoft-login' ) ) {
			return;
		}

		$settings     = get_option( 'wrm_auth_settings', array() );
		$tenant       = $settings['tenant_id'] ?? '';
		$client_id    = $settings['client_id'] ?? '';
		$redirect_uri = $settings['redirect_uri'] ?? home_url( '/wpac-microsoft-callback' );

		$auth_url = add_query_arg(
			array(
				'client_id'     => $client_id,
				'response_type' => 'code',
				'redirect_uri'  => $redirect_uri,
				'response_mode' => 'query',
				'scope'         => 'openid profile email User.Read',
			),
			"https://login.microsoftonline.com/$tenant/oauth2/v2.0/authorize"
		);
		wp_safe_redirect( $auth_url );
		exit;
	}
}
