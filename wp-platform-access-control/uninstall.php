<?php
/**
 * Uninstall.
 *
 * @package WP_Platform_Access_Control
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

/**
 * Cleanup plugin data on uninstall.
 */
delete_option( 'wpac_version' );

global $wpdb;

// Delete tables.
$tables = array(
	'wpac_entities',
	'wpac_sites',
	'wpac_user_capabilities',
	'wpac_roles',
	'wpac_role_capabilities',
	'wpac_user_roles',
	'wpac_scopes',
);

foreach ( $tables as $table ) {
	$table = $wpdb->prefix . $table;

	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$wpdb->query( "DROP TABLE IF EXISTS $table" );
}
