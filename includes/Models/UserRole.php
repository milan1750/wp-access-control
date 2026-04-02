<?php
/**
 * UserRole Model
 *
 * @package WPAC
 * @since 1.0.0
 */

namespace WPAC\Models;

defined( 'ABSPATH' ) || exit;

/**
 * UserRole Model
 *
 * Represents a single user role assignment in the system.
 *
 * @since 1.0.0
 */
class UserRole {

	/**
	 * UserRole ID.
	 *
	 * @var int
	 */
	public int $id;
	/**
	 * User ID.
	 *
	 * @var int
	 */
	public int $user_id;
	/**
	 * Role ID.
	 *
	 * @var int
	 */
	public int $role_id;
	/**
	 * Entity ID.
	 *
	 * @var ?int
	 */
	public ?int $entity_id;
	/**
	 * Site ID.
	 *
	 * @var ?int
	 */
	public ?int $site_id;

	/**
	 * Constructor
	 *
	 * @param array $data UserRole data, usually from DB row or repository.
	 */
	public function __construct( array $data = array() ) {
		$this->id        = (int) ( $data['id'] ?? 0 );
		$this->user_id   = (int) ( $data['user_id'] ?? 0 );
		$this->role_id   = (int) ( $data['role_id'] ?? 0 );
		$this->entity_id = isset( $data['entity_id'] ) ? (int) $data['entity_id'] : null;
		$this->site_id   = isset( $data['site_id'] ) ? (int) $data['site_id'] : null;
	}
}
