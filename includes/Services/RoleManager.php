<?php
/**
 * Role Manager File.
 *
 * @package WP_Platform_Access_Control
 */

namespace WPAC\Services;

defined( 'ABSPATH' ) || exit;

/**
 * Handles role + capability checks.
 */
class RoleManager {

	/**
	 * Initialize service.
	 */
	public function init(): void {}

	/**
	 * Check capability.
	 */
	public function has_capability( int $user_id, string $capability ): bool {

		if ( user_can( $user_id, $capability ) ) {
			return true;
		}

		$extra = get_user_meta( $user_id, 'wpac_extra_permissions', true );

		return is_array( $extra ) && in_array( $capability, $extra );
	}
}
