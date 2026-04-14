<?php
/**
 * RoleCapabilityRepository
 *
 * @package WPAC
 * @since 1.0.0
 */

namespace WPAC\Repositories;

/**
 * Repository for managing role capabilities in the database.
 *
 * @since 1.0.0
 */
class RoleCapabilityRepository {

	/**
	 * The table name.
	 *
	 * @var string
	 */
	private string $table;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		global $wpdb;
		$this->table = $wpdb->prefix . 'wpac_role_capabilities';
	}

	/**
	 * Set capabilities for a role.
	 *
	 * @param int   $role_id The role ID.
	 * @param array $caps    Array of capability slugs.
	 */
	public function set_role_capabilities( int $role_id, array $caps ): void {
		global $wpdb;
		// Remove old.
		$wpdb->delete( $this->table, array( 'role_id' => $role_id ), array( '%d' ) );
		// Insert new.
		foreach ( $caps as $cap ) {
			$wpdb->insert(
				$this->table,
				array(
					'role_id'    => $role_id,
					'capability' => sanitize_key( $cap ),
				),
				array( '%d', '%s' )
			);
		}
	}


	/**
	 * Get capabilities for a role.
	 *
	 * @param int $role_id The role ID.
	 * @return array Array of capability slugs.
	 */
	public function get_role_capabilities( int $role_id ): array {
		global $wpdb;
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$rows = $wpdb->get_results( $wpdb->prepare( "SELECT capability FROM {$this->table} WHERE role_id = %d", $role_id ) );
		return array_map( fn( $r ) => $r->capability, $rows );
	}

	/**
	 * Delete capabilities for a role.
	 *
	 * @param int $role_id The role ID.
	 */
	public function delete_by_role( int $role_id ): void {
		global $wpdb;
		$wpdb->delete( $this->table, array( 'role_id' => $role_id ), array( '%d' ) );
	}
}
