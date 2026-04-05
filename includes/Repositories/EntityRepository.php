<?php
/**
 * Entity Repository
 *
 * @package WPAC
 */

namespace WPAC\Repositories;

use WPAC\Models\Entity;

defined( 'ABSPATH' ) || exit;

/**
 * Entity Repository
 */
class EntityRepository {

	/**
	 * Table name
	 *
	 * @var string
	 */
	private string $table;

	/**
	 * Constructor.
	 */
	public function __construct() {
		global $wpdb;
		$this->table = $wpdb->prefix . 'wpac_entities';
	}

	/**
	 * Get all entities.
	 *
	 * @return Entity[]
	 */
	public function find_all(): array {

		global $wpdb;

		return $wpdb->get_results(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			"SELECT * FROM {$this->table} ORDER BY name ASC"
		);
	}

	/**
	 * Create entity.
	 *
	 * @param array $data Entity data.
	 * @return int
	 */
	public function create( array $data ): int {

		global $wpdb;

		$wpdb->insert(
			$this->table,
			$data,
			array( '%s', '%s', '%d' )
		);

		return (int) $wpdb->insert_id;
	}

	/**
	 * Update entity.
	 *
	 * @param int   $id Entity ID.
	 * @param array $data Entity data.
	 * @return bool
	 */
	public function update( int $id, array $data ): bool {

		global $wpdb;

		return (bool) $wpdb->update(
			$this->table,
			$data,
			array( 'id' => $id ),
			array( '%s', '%s', '%d' ),
			array( '%d' )
		);
	}

	/**
	 * Delete entity.
	 *
	 * @param int $id Entity ID.
	 * @return bool
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
	 * Find by slug.
	 *
	 * @param string $slug Entity slug.
	 * @return Entity|null
	 */
	public function find_by_slug( string $slug ): ?Entity {

		global $wpdb;

		$row = $wpdb->get_row(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT * FROM {$this->table} WHERE slug = %s LIMIT 1",
				$slug
			)
		);

		return $row ? new Entity( (array) $row ) : null;
	}

	/**
	 * Find by ID.
	 *
	 * @param int $id Entity ID.
	 * @return array|null
	 */
	public function find_by_id( int $id ): ?array {
		global $wpdb;
		$row = $wpdb->get_row(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->prepare( "SELECT * FROM {$this->table} WHERE id=%d LIMIT 1", $id ),
			ARRAY_A
		);
		return $row ? $row : null;
	}
}
