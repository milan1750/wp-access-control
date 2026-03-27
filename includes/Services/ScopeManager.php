<?php

namespace WPAC\Services;

defined( 'ABSPATH' ) || exit;

/**
 * Handles Entity / Site / Global access scope.
 */
class ScopeManager {

	/**
	 * Initialize service.
	 */
	public function init(): void {}

	/**
	 * Check access scope.
	 *
	 * @param int      $user_id User ID.
	 * @param int|null $entity_id Entity ID.
	 * @param int|null $site_id Site ID.
	 */
	public function can_access( int $user_id, ?int $entity_id, ?int $site_id ): bool {

		$scope = get_user_meta( $user_id, 'wpac_scope', true );

		if ( $scope === 'global' ) {
			return true;
		}

		if ( $scope === 'entity' ) {
			$entities = get_user_meta( $user_id, 'wpac_entities', true ) ?: array();
			return in_array( $entity_id, $entities );
		}

		if ( $scope === 'site' ) {
			$sites = get_user_meta( $user_id, 'wpac_sites', true ) ?: array();
			return in_array( $site_id, $sites );
		}

		return false;
	}
}
