<?php
/**
 * Role Model
 *
 * @package WPAC
 * @since 1.0.0
 */

namespace WPAC\Models;

defined( 'ABSPATH' ) || exit;

/**
 * Role Model
 *
 * Represents a single role in the system.
 *
 * @since 1.0.0
 */
class Role {

	/**
	 * Role ID.
	 *
	 * @var int
	 */
	public int $id;

	/**
	 * Role name.
	 *
	 * @var string
	 */
	public string $name;

	/**
	 * Role slug (unique identifier).
	 *
	 * @var string
	 */
	public string $slug;

	/**
	 * Role description.
	 *
	 * @var string|null
	 */
	public ?string $description;

	/**
	 * Role creation timestamp.
	 *
	 * @var string
	 */
	public string $created_at;

	/**
	 * Constructor
	 *
	 * @param array $data Role data, usually from DB row or repository.
	 */
	public function __construct( array $data = array() ) {
		$this->id          = (int) ( $data['id'] ?? 0 );
		$this->name        = $data['name'] ?? '';
		$this->slug        = $data['slug'] ?? '';
		$this->description = $data['description'] ?? null;
		$this->created_at  = $data['created_at'] ?? current_time( 'mysql' );
	}

	/**
	 * Get a nicely formatted display name.
	 *
	 * @return string
	 */
	public function display_name(): string {
		return ucwords( $this->name );
	}

	/**
	 * Check if role is "admin" based on slug.
	 *
	 * @return bool
	 */
	public function is_admin(): bool {
		return 'admin' === $this->slug;
	}

	/**
	 * Convert model to array (useful for saving/updating via repository).
	 *
	 * @return array
	 */
	public function to_array(): array {
		return array(
			'id'          => $this->id,
			'name'        => $this->name,
			'slug'        => $this->slug,
			'description' => $this->description,
			'created_at'  => $this->created_at,
		);
	}
}
