<?php
/**
 * Scope Repository
 *
 * @package WPAC
 * @since 1.0.0
 */

namespace WPAC\Repositories;

use WPAC\Models\Scope;

defined( 'ABSPATH' ) || exit;

/**
 * Repository for managing scope data access.
 *
 * @since 1.0.0 *
 */
class ScopeRepository {

	/**
	 * Database table name for scopes.
	 *
	 * @var string
	 */
	private string $table;

	/**
	 * Constructor.
	 */
	public function __construct() {
		global $wpdb;
		$this->table = $wpdb->prefix . 'wpac_scopes';
	}

	/**
	 * Get all scopes.
	 *
	 * @return Scope[]
	 */
	public function find_all(): array {
		global $wpdb;

		$rows = $wpdb->get_results(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			"SELECT * FROM {$this->table}"
		);

		return array_map(
			function ( $row ) {
				return new Scope( (array) $row );
			},
			$rows
		);
	}

	/**
	 * Create a new scope.
	 *
	 * @param array $data Scope data to insert.
	 * @return int Inserted scope ID.
	 */
	public function create( array $data ): int {

		global $wpdb;

		$wpdb->insert(
			$this->table,
			$data,
			array( '%s', '%s', '%s' )
		);

		return (int) $wpdb->insert_id;
	}

	/**
	 * Update an existing scope.
	 *
	 * @param int   $id   Scope ID to update.
	 * @param array $data Scope data to update.
	 * @return bool True on success, false on failure.
	 */
	public function update( int $id, array $data ): bool {

		global $wpdb;

		return (bool) $wpdb->update(
			$this->table,
			$data,
			array( 'id' => $id ),
			array( '%s', '%s', '%s' ),
			array( '%d' )
		);
	}

	/**
	 * Delete a scope by ID.
	 *
	 * @param int $id Scope ID to delete.
	 * @return bool True on success, false on failure.
	 */
	public function delete( int $id ): bool {

		global $wpdb;

		return (bool) $wpdb->delete(
			$this->table,
			array( 'id' => $id ),
			array( '%d' )
		);
	}

	/**
	 * Find a scope by slug.
	 *
	 * @param string $slug Scope slug to search for.
	 * @return Scope|null Scope object if found, null if not found.
	 */
	public function find_by_slug( string $slug ): ?Scope {

		global $wpdb;

		$row = $wpdb->get_row(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT * FROM {$this->table} WHERE slug = %s LIMIT 1",
				$slug
			)
		);

		return $row ? new Scope( (array) $row ) : null;
	}
}
