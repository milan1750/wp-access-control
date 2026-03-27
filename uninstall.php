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
