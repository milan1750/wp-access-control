<?php
/**
 * Entity Manager.
 *
 * @package WP_Platform_Access_Control
 */

namespace WPAC\Services;

defined( 'ABSPATH' ) || exit;

/**
 * Handles entity management operations.
 *
 * @since 1.0.0
 */
class EntityManager {

	/**
	 * Table Name.
	 *
	 * @since 1.0.0
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
		$this->table = $wpdb->prefix . 'wpac_entities';
	}

	/**
	 * Get all entities.
	 *
	 * @param int|null $status Optional status filter (1 = active, 0 = inactive).
	 * @return array
	 */
	public function get_all( ?int $status = null ): array {
		global $wpdb;

		$sql    = "SELECT * FROM {$this->table}";
		$params = array();

		if ( null !== $status ) {
			$sql     .= ' WHERE status = %d';
			$params[] = $status;
		}

		$sql .= ' ORDER BY name ASC';

		if ( $params ) {
			return $wpdb->get_results( $wpdb->prepare( $sql, ...$params ), ARRAY_A );
		}

		return $wpdb->get_results( $sql, ARRAY_A );
	}

	/**
	 * Get a single entity by ID.
	 *
	 * @param int $id
	 * @return object|null
	 */
	public function get_entity( int $id ): ?object {
		global $wpdb;

		return $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$this->table} WHERE id = %d", $id )
		);
	}

	/**
	 * Get a single entity by slug.
	 *
	 * @param string $slug
	 * @return object|null
	 */
	public function get_entity_by_slug( string $slug ): ?object {
		global $wpdb;

		return $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$this->table} WHERE slug = %s", $slug )
		);
	}

	/**
	 * Insert or update an entity.
	 *
	 * @param string   $name
	 * @param string   $slug
	 * @param int|null $id Optional entity ID to update.
	 * @return int|false Inserted ID or false on failure
	 */
	public function save( string $name, string $slug, ?int $id = null ) {
		global $wpdb;

		if ( $id ) {
			$updated = $wpdb->update(
				$this->table,
				array(
					'name' => sanitize_text_field( $name ),
					'slug' => sanitize_title( $slug ),
				),
				array( 'id' => $id ),
				array( '%s', '%s' ),
				array( '%d' )
			);
			return $updated !== false ? $id : false;
		}

		$wpdb->insert(
			$this->table,
			array(
				'name'   => sanitize_text_field( $name ),
				'slug'   => sanitize_title( $slug ),
				'status' => 1,
			),
			array( '%s', '%s', '%d' )
		);

		return $wpdb->insert_id ?: false;
	}

	/**
	 * Delete an entity.
	 *
	 * @param int $id
	 * @return bool
	 */
	public function delete( int $id ): bool {
		global $wpdb;

		return (bool) $wpdb->delete( $this->table, array( 'id' => $id ), array( '%d' ) );
	}

	/**
	 * Set entity status (active/inactive)
	 *
	 * @param int $id
	 * @param int $status
	 * @return bool
	 */
	public function set_status( int $id, int $status ): bool {
		global $wpdb;

		return (bool) $wpdb->update(
			$this->table,
			array( 'status' => $status ),
			array( 'id' => $id ),
			array( '%d' ),
			array( '%d' )
		);
	}
}
