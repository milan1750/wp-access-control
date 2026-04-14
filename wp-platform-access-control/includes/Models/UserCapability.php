<?php
/**
 * UserCapability Model
 *
 * @package WPAC
 * @since 1.0.0
 */

namespace WPAC\Models;

defined( 'ABSPATH' ) || exit;

/**
 * UserCapability Model
 *
 * Represents a single user capability in the system.
 *
 * @since 1.0.0
 */
class UserCapability {

	/**
	 * UserCapability ID.
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
	 * User role.
	 *
	 * @var string
	 */
	public string $role;

	/**
	 * User scope.
	 *
	 * @var string
	 */
	public string $scope;

	/**
	 * User capabilities.
	 *
	 * @var ?array
	 */
	public ?array $capabilities;

	/**
	 * User meta.
	 *
	 * @var ?array
	 */
	public ?array $meta;
	/**
	 * UserCapability creation timestamp.
	 *
	 * @var string
	 */
	public string $created_at;
	/**
	 * UserCapability update timestamp.
	 *
	 * @var ?string
	 */
	public ?string $updated_at;

	/**
	 * Constructor
	 *
	 * @param array $data UserCapability data, usually from DB row or repository.
	 */
	public function __construct( array $data = array() ) {
		$this->id         = (int) ( $data['id'] ?? 0 );
		$this->user_id    = (int) ( $data['user_id'] ?? 0 );
		$this->role       = $data['role'] ?? '';
		$this->scope      = $data['scope'] ?? 'global';
		$this->meta       = isset( $data['meta'] ) ? $data['meta'] : array();
		$this->created_at = $data['created_at'] ?? current_time( 'mysql' );
		$this->updated_at = $data['updated_at'] ?? null;

		$capabilities = isset( $data['capabilities'] ) ? $data['capabilities'] : array();
		if ( is_string( $capabilities ) ) {
			$this->capabilities = (array) json_decode( $capabilities, true );
		} else {
			$this->capabilities = $capabilities;
		}
	}

	/**
	 * Check if the user has a specific capability.
	 *
	 * @param string $cap The capability to check.
	 * @return bool True if the user has the capability, false otherwise.
	 */
	public function has_capability( string $cap ): bool {
		return in_array( '*', $this->capabilities ?? array(), true ) || in_array( $cap, $this->capabilities ?? array(), true );
	}

	/**
	 * To Array.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function to_array(): array {
		return array(
			'user_id'      => $this->user_id,
			'role'         => $this->role,
			'scope'        => $this->scope,
			'capabilities' => wp_json_encode( $this->capabilities ),
		);
	}
}
