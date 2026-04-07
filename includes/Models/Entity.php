<?php
/**
 * Entity Base Model
 *
 * @package WPAC
 * @since 1.0.0
 */

namespace WPAC\Models;

defined( 'ABSPATH' ) || exit;

/**
 * Entity Base Model
 *
 * @package WPAC
 * @since 1.0.0
 */
class Entity {

	/**
	 * Entity ID.
	 *
	 * @var int
	 */
	public int $id;

	/**
	 * Entity name.
	 *
	 * @var string
	 */
	public string $name;

	/**
	 * Entity slug.
	 *
	 * @var string
	 */
	public string $slug;

	/**
	 * Entity status.
	 *
	 * @var int
	 */
	public int $status;

	/**
	 * Entity creation timestamp.
	 *
	 * @var string
	 */
	public string $created_at;

	/**
	 * Entity update timestamp.
	 *
	 * @var string|null
	 */
	public ?string $updated_at;

	/**
	 * Constructor
	 *
	 * @param array $data Entity data, usually from DB row or repository.
	 */
	public function __construct( array $data = array() ) {
		$this->id         = (int) ( $data['id'] ?? 0 );
		$this->name       = $data['name'] ?? '';
		$this->slug       = $data['slug'] ?? '';
		$this->status     = (int) ( $data['status'] ?? 1 );
		$this->created_at = $data['created_at'] ?? current_time( 'mysql' );
		$this->updated_at = $data['updated_at'] ?? null;
	}

	/**
	 * Check if the entity is active.
	 *
	 * @return bool
	 */
	public function is_active(): bool {
		return 1 === $this->status;
	}

	/**
	 * Convert model to database array.
	 *
	 * @return array
	 */
	public function to_array(): array {
		return array(
			'name'   => $this->name,
			'slug'   => $this->slug,
			'status' => $this->status,
			'id'     => $this->id,
		);
	}
}
