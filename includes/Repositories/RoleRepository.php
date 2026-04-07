<?php
/**
 * RoleRepository
 *
 * @package WPAC
 * @since 1.0.0
 */

namespace WPAC\Repositories;

defined( 'ABSPATH' ) || exit;

use WPAC\Models\Role;

/**
 * Handles Role storage.
 */
class RoleRepository {

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
		$this->table = $wpdb->prefix . 'wpac_roles';
	}

	/**
	 * Find a role by ID.
	 *
	 * @param int $id The role ID.
	 * @return object|null The role object or null if not found.
	 */
	public function find( int $id ) {
		global $wpdb;
		return $wpdb->get_row(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->prepare( "SELECT * FROM {$this->table} WHERE id = %d", $id )
		);
	}

	/**
	 * Create a new role.
	 *
	 * @param array $data The role data.
	 * @return int The ID of the created role.
	 */
	public function create( array $data ): int {
		global $wpdb;
		$wpdb->insert( $this->table, $data );
		return (int) $wpdb->insert_id;
	}

	/**
	 * Update an existing role.
	 *
	 * @param int   $id The role ID.
	 * @param array $data The updated role data.
	 * @return bool True if the role was updated, false otherwise.
	 */
	public function update( int $id, array $data ): bool {
		global $wpdb;
		return (bool) $wpdb->update( $this->table, $data, array( 'id' => $id ) );
	}

	/**
	 * Delete a role.
	 *
	 * @param int $id The role ID.
	 * @return bool True if the role was deleted, false otherwise.
	 */
	public function delete( int $id ): bool {
		global $wpdb;
		return (bool) $wpdb->delete( $this->table, array( 'id' => $id ) );
	}

	/**
	 * Get all roles with optional pagination.
	 *
	 * @param int $limit The number of roles to retrieve (0 for no limit).
	 * @param int $offset The offset for pagination.
	 * @return array An array of role objects.
	 */
	public function find_all( int $limit = 0, int $offset = 0 ): array {
		global $wpdb;

		$sql = "SELECT * FROM {$this->table}";

		if ( empty( $limit ) ) {
			$limit = get_option( 'posts_per_page', 10 );
		}

		if ( empty( $offset ) ) {
			$offset = 0;
		}

		if ( $limit > 0 ) {
			$sql .= $wpdb->prepare( ' LIMIT %d OFFSET %d', $limit, $offset );
		}

		return $wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}


	/**
	 * Count the total number of roles.
	 *
	 * @return int The total number of roles.
	 */
	public function count(): int {
		global $wpdb;

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$this->table}" );
	}

	/**
	 * Find a role by its slug.
	 *
	 * @param string $slug The role slug.
	 * @return Role|null The role object or null if not found.
	 */
	public function find_by_slug( string $slug ): ?Role {
		global $wpdb;
		$row = $wpdb->get_row(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->prepare( "SELECT * FROM {$this->table} WHERE slug = %s", $slug )
		);
		return $row ? new Role( (array) $row ) : null;
	}
}
