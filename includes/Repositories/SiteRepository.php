<?php
/**
 * Site Repository
 *
 * @package WPAC
 */

namespace WPAC\Repositories;

defined( 'ABSPATH' ) || exit;

/**
 * Site Repository.
 *
 * @since 1.0.0
 */
class SiteRepository {

	/**
	 * Table name
	 *
	 * @var string
	 */
	private string $table;

	/**
	 * Constructor
	 */
	public function __construct() {
		global $wpdb;
		$this->table = $wpdb->prefix . 'wpac_sites';
	}

	/**
	 * Get all sites
	 *
	 * @return array[]
	 */
	public function find_all(): array {

		global $wpdb;

		return $wpdb->get_results(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			"SELECT * FROM {$this->table} ORDER BY name ASC",
			ARRAY_A
		);
	}

	/**
	 * Find site by ID.
	 *
	 * @param int $id ID.
	 *
	 * @return array|null
	 */
	public function find_by_id( int $id ): ?array {

		global $wpdb;

		return $wpdb->get_row(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT * FROM {$this->table} WHERE id = %d LIMIT 1",
				$id
			),
			ARRAY_A
		);
	}

	/**
	 * Find site by slug
	 *
	 * @param string $slug Slug.
	 *
	 * @return array|null
	 */
	public function find_by_slug( string $slug ): ?array {

		global $wpdb;

		return $wpdb->get_row(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT * FROM {$this->table} WHERE slug = %s LIMIT 1",
				$slug
			),
			ARRAY_A
		);
	}

	/**
	 * Get sites by entity
	 *
	 * @param int $entity_id $id.
	 *
	 * @return array[]
	 */
	public function find_by_entity( int $entity_id ): array {

		global $wpdb;

		return $wpdb->get_results(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT * FROM {$this->table} WHERE entity_id = %d ORDER BY name ASC",
				$entity_id
			),
			ARRAY_A
		);
	}

	/**
	 * Create site.
	 *
	 * @param array $data Data.
	 */
	public function create( array $data ): int {

		global $wpdb;

		$wpdb->insert(
			$this->table,
			$data,
			array( '%d', '%s', '%s', '%s', '%s', '%d' )
		);

		return (int) $wpdb->insert_id;
	}

	/**
	 * Update site.
	 *
	 * @param int   $id Id.
	 * @param array $data Data.
	 */
	public function update( int $id, array $data ): bool {

		global $wpdb;

		return (bool) $wpdb->update(
			$this->table,
			$data,
			array( 'id' => $id ),
			array( '%d', '%s', '%s', '%s', '%s', '%d' ),
			array( '%d' )
		);
	}

	/**
	 * Delete site.
	 *
	 * @param int $id ID.
	 */
	public function delete( int $id ): bool {

		global $wpdb;

		return (bool) $wpdb->delete(
			$this->table,
			array( 'id' => $id ),
			array( '%d' )
		);
	}
}
