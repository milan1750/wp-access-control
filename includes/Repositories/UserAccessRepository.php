<?php

namespace WPAC\Repositories;

defined( 'ABSPATH' ) || exit;

/**
 * Handles user access mapping.
 */
class UserAccessRepository {

	public function get( int $user_id ): array {
		return get_user_meta( $user_id, 'wpac_access', true ) ?: array();
	}

	public function save( int $user_id, array $data ): void {
		update_user_meta( $user_id, 'wpac_access', $data );
	}
}
