<?php
namespace WPAC\Core;

defined( 'ABSPATH' ) || exit;

use WPAC\Core\AuthMiddleware;

class Router {

	public static function init() {
		add_action( 'init', array( self::class, 'register_routes' ) );
		add_filter( 'query_vars', array( self::class, 'query_vars' ) );
		add_action( 'wp_enqueue_scripts', array( self::class, 'maybe_enqueue_assets' ) );
		add_action( 'template_redirect', array( self::class, 'handle_routes' ) );
	}

	public static function register_routes() {
		error_log( print_r( 'Registering WPAC routes...', true ) );
		add_rewrite_rule( '^wpac-platform/?$', 'index.php?wpac=platform', 'top' );
		add_rewrite_rule( '^wpac-platform/login/?$', 'index.php?wpac=login', 'top' );
		add_rewrite_rule( '^wpac-platform/logout/?$', 'index.php?wpac=logout', 'top' );
	}

	public static function query_vars( $vars ) {
		$vars[] = 'wpac';
		return $vars;
	}

	// Enqueue assets based on current route
	public static function maybe_enqueue_assets() {
		$route = get_query_var( 'wpac' );
		if ( $route === 'platform' ) {
			wp_enqueue_style(
				'wpac-platform-css',
				WPAC_PLUGIN_URL . 'assets/platform.css',
				array(),
				'1.0.0'
			);

			wp_enqueue_script(
				'wpac-platform-js',
				WPAC_PLUGIN_URL . 'assets/platform.js',
				array( 'jquery' ),
				'1.0.0',
				true
			);
		}
	}

	public static function handle_routes() {
		$route      = get_query_var( 'wpac' );
		$views_path = WPAC_PLUGIN_DIR . '/views/';

		switch ( $route ) {
			case 'login':
				include $views_path . 'login.php';
				exit;

			case 'logout':
				wp_logout();
				wp_safe_redirect( home_url( '/wpac-platform/login' ) );
				exit;

			case 'platform':
				AuthMiddleware::require_login();
				include $views_path . 'platform.php';
				exit;

			default:
				if ( ! is_user_logged_in() ) {
					wp_safe_redirect( home_url( '/wpac-platform/login' ) );
				} else {
					wp_safe_redirect( home_url( '/wpac-platform' ) );
				}
				exit;
		}
	}
}
