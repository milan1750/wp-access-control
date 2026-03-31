<?php
/**
 * Activator File.
 *
 * @package WP_Platform_Access_Control
 */

namespace WPAC\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Runs plugin activation tasks.
 */
class Activator {

	/**
	 * Run activation logic.
	 */
	public static function run(): void {

		add_option( 'wpac_version', WPAC_VERSION );

		self::create_tables();
		self::seed_super_user();
		self::add_capabilities();
	}

	private static function create_tables(): void {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate = $wpdb->get_charset_collate();

		$entities = $wpdb->prefix . 'wpac_entities';
		$sites    = $wpdb->prefix . 'wpac_sites';
		$user_cap = $wpdb->prefix . 'wpac_user_capabilities';
		$roles    = $wpdb->prefix . 'wpac_roles';
		$role_cap = $wpdb->prefix . 'wpac_role_capabilities';
		$user_rl  = $wpdb->prefix . 'wpac_user_roles';
		$scopes   = $wpdb->prefix . 'wpac_scopes';

		$sql = array();

		// ENTITIES table.
		$sql[] = "CREATE TABLE {$entities} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			name VARCHAR(255) NOT NULL,
			slug VARCHAR(255) NOT NULL,
			status TINYINT(1) DEFAULT 1,
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY slug (slug),
			KEY status (status)
		) {$charset_collate};";

		// SITES table.
		$sql[] = "CREATE TABLE {$sites} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			entity_id BIGINT UNSIGNED NOT NULL,
			site_id VARCHAR(255) NOT NULL,
			name VARCHAR(255) NOT NULL,
			slug VARCHAR(255) NOT NULL,
			location VARCHAR(255) NULL,
			status TINYINT(1) DEFAULT 1,
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY slug (slug),
			KEY entity_id (entity_id),
			KEY status (status)
		) {$charset_collate};";

		$sql[] = "CREATE TABLE {$user_cap} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id BIGINT UNSIGNED NOT NULL,
			role VARCHAR(100) NOT NULL,
			scope VARCHAR(100) NOT NULL DEFAULT 'global',
			capabilities LONGTEXT NULL,
			meta LONGTEXT NULL,
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY user_id (user_id)
		) {$charset_collate};";

		// ROLES table.
		$sql[] = "CREATE TABLE {$roles} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			name VARCHAR(100) NOT NULL,
			slug VARCHAR(100) NOT NULL,
			description TEXT NULL,
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY slug (slug)
		) {$charset_collate};";

		// ROLE CAPABILITIES.
		$sql[] = "CREATE TABLE {$role_cap} (
			role_id BIGINT UNSIGNED NOT NULL,
			capability VARCHAR(255) NOT NULL,
			PRIMARY KEY (role_id, capability),
			KEY role_id (role_id)
		) {$charset_collate};";

		// USER ROLE ASSIGNMENTS.
		$sql[] = "CREATE TABLE {$user_rl} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id BIGINT UNSIGNED NOT NULL,
			role_id BIGINT UNSIGNED NOT NULL,
			entity_id BIGINT UNSIGNED NULL,
			site_id BIGINT UNSIGNED NULL,
			PRIMARY KEY (id),
			KEY user_id (user_id),
			KEY role_id (role_id)
		) {$charset_collate};";

		// SCOPES.
		$sql[] = "CREATE TABLE {$scopes} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			name VARCHAR(100) NOT NULL,
			slug VARCHAR(100) NOT NULL,
			type VARCHAR(50) NOT NULL,
			config LONGTEXT NULL,
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY slug (slug),
			KEY type (type)
		) {$charset_collate};";

		// Execute all.
		foreach ( $sql as $query ) {
			dbDelta( $query );
		}
	}
	/**
	 * Seed super admin access.
	 */
	private static function seed_super_user(): void {

		global $wpdb;

		$table = $wpdb->prefix . 'wpac_user_access';

		$admins = get_users(
			array(
				'role'   => 'administrator',
				'fields' => array( 'ID' ),
			)
		);

		foreach ( $admins as $admin ) {

			$user_id = (int) $admin->ID;

			$exists = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT id FROM {$table} WHERE user_id = %d AND role = %s LIMIT 1",
					$user_id,
					'super_user'
				)
			);

			if ( $exists ) {
				continue;
			}

			$wpdb->insert(
				$table,
				array(
					'user_id'     => $user_id,
					'entity_id'   => null,
					'site_id'     => null,
					'role'        => 'super_user',
					'permissions' => wp_json_encode( array( '*' ) ),
					'meta'        => wp_json_encode(
						array(
							'scope' => 'global',
						)
					),
				)
			);
		}
	}

	/**
	 * Future capability seeding
	 */
	private static function add_capabilities(): void {
		// reserved for future role/cap system sync
	}
}
