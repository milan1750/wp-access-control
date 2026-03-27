<?php
/**
 * Plugin Name: WP Platform Access Control
 * Description: Entity / Site / User based access control system for WordPress platform.
 * Version: 1.0.0
 * Author: Milan Malla
 * License: GPL-2.0-or-later
 *
 * @package WP_Platform_Access_Control
 */

defined( 'ABSPATH' ) || exit;

/**
 * Plugin constants.
 */
define( 'WPAC_VERSION', '1.0.0' );
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

/**
 * Plugin activation hook.
 */
function wpac_activate(): void {
	Plugin::activate();
}

/**
 * Plugin deactivation hook.
 */
function wpac_deactivate(): void {
	Plugin::deactivate();
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

		// Global instance for ecosystem usage.
		$GLOBALS['wpac'] = Plugin::instance();

		do_action( 'wpac_plugin_loaded' );
	}
);
