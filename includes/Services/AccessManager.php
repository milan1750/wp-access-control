<?php
/**
 * Service Access Manager
 *
 * High-level wrapper for plugins.
 *
 * @package WP_Platform_Access_Control
 */

namespace WPAC\Services;

defined( 'ABSPATH' ) || exit;

use WPAC\Core\AccessManager as CoreAccessManager;

class AccessManager {

	/**
	 * Check permission for current user
	 */
	public function can( string $permission, array $context = array() ): bool {

		$user_id = get_current_user_id();

		if ( ! $user_id ) {
			return false;
		}

		return CoreAccessManager::can( $user_id, $permission, $context );
	}

	/**
	 * Check permission for specific user
	 */
	public function user_can(
		int $user_id,
		string $permission,
		array $context = array()
	): bool {

		return CoreAccessManager::can( $user_id, $permission, $context );
	}

	/**
	 * Check if current user is super user
	 */
	public function is_super_user(): bool {

		$user_id = get_current_user_id();

		if ( ! $user_id ) {
			return false;
		}

		return CoreAccessManager::can( $user_id, '*' );
	}
}
