<?php
/**
 * RoleCapability Model
 *
 * @package WPAC
 * @since 1.0.0
 */

namespace WPAC\Models;

defined( 'ABSPATH' ) || exit;

/**
 * RoleCapability Model
 *
 * @package WPAC
 * @since 1.0.0
 */
class RoleCapability {

	/**
	 * Role ID.
	 *
	 * @var int
	 */
	public int $role_id;

	/**
	 * Capability.
	 *
	 * @var string
	 */
	public string $capability;

	/**
	 * Constructor.
	 *
	 * @param array $data RoleCapability data.
	 */
	public function __construct( array $data = array() ) {
		$this->role_id    = (int) ( $data['role_id'] ?? 0 );
		$this->capability = $data['capability'] ?? '';
	}
}
