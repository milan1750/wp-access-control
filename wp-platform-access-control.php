<?php
/**
 * Plugin Name: WP Platform Access Control
 * Description: Entity / Site / User based access control system for WordPress platform.
 * Version: 1.0.1
 * Author: Milan Malla
 * License: GPL-2.0-or-later
 *
 * @package WP_Platform_Access_Control
 */

defined( 'ABSPATH' ) || exit;

/**
 * Plugin constants.
 */
define( 'WPAC_VERSION', '1.0.1' );
define( 'WPAC_PLUGIN_FILE', __FILE__ );
define( 'WPAC_PLUGIN_DIR', __DIR__ );
define( 'WPAC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );


/**
 * Composer autoloader (safe load).
 *
 * NOTE:
 * This plugin should be the ONLY place in your ecosystem
 * that loads shared Composer dependencies.
 */
$autoloader = WPAC_PLUGIN_DIR . '/vendor/autoload.php';

if ( is_readable( $autoloader ) ) {
	require_once $autoloader;
} else {

	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		error_log( 'WPAC: Composer autoload missing. Run composer install.' );
	}

	add_action(
		'admin_notices',
		function () {
			echo '<div class="notice notice-error"><p>';
			echo 'WP Platform Access Control: Missing dependencies. Run <code>composer install</code>.';
			echo '</p></div>';
		}
	);

	return;
}

use WPAC\Plugin;
use WPAC\Core\Router;

/**
 * Plugin activation hook.
 */
function wpac_activate(): void {
	Plugin::activate();
	Router::register_routes();  // Make sure Router is initialized.
	flush_rewrite_rules();
}

/**
 * Plugin deactivation hook.
 */
function wpac_deactivate(): void {
	Plugin::deactivate();
	flush_rewrite_rules();
}

register_activation_hook( __FILE__, 'wpac_activate' );
register_deactivation_hook( __FILE__, 'wpac_deactivate' );


/**
 * Initialize plugin.
 */
add_action(
	'plugins_loaded',
	function () {
		// Initialize core plugin.
		Plugin::init();
		do_action( 'wpac_plugin_loaded' );
	}
);


/**
 * Main instance of WPAC.
 *
 * Returns the main instance of WPAC to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return \WPAC\Plugin
 */
function wpac() {
	return Plugin::instance();
}

// Global for backwards compatibility.
$GLOBALS['wpac'] = wpac();
