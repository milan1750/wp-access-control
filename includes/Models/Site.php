<?php
/**
 * Site Model
 *
 * @package WPAC
 * @since 1.0.0
 */

namespace WPAC\Models;

defined( 'ABSPATH' ) || exit;

/**
 * Site Model
 *
 * Represents a single site in the system.
 *
 * @since 1.0.0
 */
class Site {

	/**
	 * Site ID.
	 *
	 * @var int
	 */
	public int $id;

	/**
	 * Entity ID.
	 *
	 * @var int
	 */
	public int $entity_id;

	/**
	 * Site ID.
	 *
	 * @var string
	 */
	public string $site_id;

	/**
	 * Site name.
	 *
	 * @var string
	 */
	public string $name;

	/**
	 * Site slug.
	 *
	 * @var string
	 */
	public string $slug;

	/**
	 * Site location.
	 *
	 * @var ?string
	 */
	public ?string $location;

	/**
	 * Site status.
	 *
	 * @var int
	 */
	public int $status;

	/**
	 * Site creation timestamp.
	 *
	 * @var string
	 */
	public string $created_at;

	/**
	 * Site update timestamp.
	 *
	 * @var ?string
	 */
	public ?string $updated_at;

	/**
	 * Constructor
	 *
	 * @param array $data Site data, usually from DB row or repository.
	 */
	public function __construct( array $data = array() ) {
		$this->id         = (int) ( $data['id'] ?? 0 );
		$this->entity_id  = (int) ( $data['entity_id'] ?? 0 );
		$this->site_id    = $data['site_id'] ?? '';
		$this->name       = $data['name'] ?? '';
		$this->slug       = $data['slug'] ?? '';
		$this->location   = $data['location'] ?? null;
		$this->status     = (int) ( $data['status'] ?? 1 );
		$this->created_at = $data['created_at'] ?? current_time( 'mysql' );
		$this->updated_at = $data['updated_at'] ?? null;
	}

	/**
	 * Check if the site is active.
	 *
	 * @return bool
	 */
	public function is_active(): bool {
		return 1 === $this->status;
	}
}
