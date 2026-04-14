<?php
/**
 * Router.
 *
 * @package WPAC
 */

namespace WPAC\Core;

defined( 'ABSPATH' ) || exit;

use WPAC\Middleware\AuthMiddleware;

/**
 * Router Class.
 *
 * @since 1.0.0
 */
class Router {

	/**
	 * Register.
	 *
	 * @since 1.0.0
	 */
	public static function register() {
		add_action( 'init', array( self::class, 'register_routes' ) );
		// Restrict wp-admin for non-admins.
		add_action( 'admin_init', array( self::class, 'restrict_admin' ) );

		// Block all non-platform pages.
		add_action( 'template_redirect', array( self::class, 'template_redirect' ) );

		add_filter( 'query_vars', array( self::class, 'query_vars' ) );
		add_action( 'wp_enqueue_scripts', array( self::class, 'maybe_enqueue_assets' ) );
		add_action( 'template_redirect', array( self::class, 'handle_routes' ) );
	}

	/**
	 * Restrict Admin Pages Access.
	 *
	 * @since 1.0.0
	 */
	public static function restrict_admin() {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_safe_redirect( home_url( '/wpac-platform' ) );
			exit;
		}
	}

	/**
	 * Template Redirect.
	 *
	 * @since 1.0.0
	 */
	public static function template_redirect() {
		$allowed = array(
			'/wpac-platform',
			'/wpac-platform/login',
			'/wpac-platform/logout',
		);

		// phpcs:ignore WordPress.WP.AlternativeFunctions.parse_url_parse_url
		$path = '/' . trim( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ), '/' );

		if ( ! in_array( $path, $allowed, true ) ) {
			if ( ! is_user_logged_in() ) {
				wp_safe_redirect( home_url( '/wpac-platform/login' ) );
			} else {
				wp_safe_redirect( home_url( '/wpac-platform' ) );
			}
			exit;

		}
	}

	/**
	 * Register Routes.
	 *
	 * @since 1.0.0
	 */
	public static function register_routes() {
		add_rewrite_rule( '^wpac-platform/?$', 'index.php?wpac=platform', 'top' );
		add_rewrite_rule( '^wpac-platform/login/?$', 'index.php?wpac=login', 'top' );
		add_rewrite_rule( '^wpac-platform/logout/?$', 'index.php?wpac=logout', 'top' );
	}

	/**
	 * Query Vars.
	 *
	 * @since 1.0.0
	 *
	 * @param  array $vars Vars.
	 */
	public static function query_vars( $vars ) {
		$vars[] = 'wpac';
		return $vars;
	}

	/**
	 * Enqueue Scripts.
	 *
	 * @since 1.0.0
	 */
	public static function maybe_enqueue_assets() {
		$route = get_query_var( 'wpac' );
		if ( 'platform' === $route ) {
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
		} elseif ( 'login' === $route ) {
			wp_enqueue_style(
				'wpac-login-css',
				WPAC_PLUGIN_URL . 'assets/frontend/login.css',
				array(),
				'1.0.0'
			);
		}
	}

	/**
	 * Handle Routes.
	 *
	 * @since 1.0.0
	 */
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
