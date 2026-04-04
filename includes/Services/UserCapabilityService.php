<?php

namespace WPAC\Services;

use WPAC\Models\UserCapability;
use WPAC\Repositories\UserCapabilityRepository;

defined( 'ABSPATH' ) || exit;

class UserCapabilityService {

	private UserCapabilityRepository $repo;

	public function __construct( UserCapabilityRepository $repo ) {
		$this->repo = $repo;
	}

	public function save( UserCapability $model ): UserCapability {

		if ( ! $model->user_id ) {
			throw new \Exception( 'User is required' );
		}

		$existing = $this->repo->find_by_user( $model->user_id );

		if ( $existing ) {

			error_log( print_r( $model, true ) );

			$updated = $this->repo->update(
				$model->user_id,
				$model->to_array()
			);

			if ( ! $updated ) {
				throw new \Exception( 'Failed to update user capabilities' );
			}
		} else {

			$id = $this->repo->create(
				$model->to_array()
			);

			if ( ! $id ) {
				throw new \Exception( 'Failed to assign user capabilities' );
			}

			$model->id = $id;
		}

		return $model;
	}

	/**
	 * Revoke user capabilities.
	 *
	 * @param int $user_id
	 * @throws \Exception
	 */
	public function revoke( int $user_id ): void {

		if ( ! $user_id ) {
			throw new \Exception( 'Invalid user ID' );
		}

		$deleted = $this->repo->delete_by_user( $user_id );

		if ( ! $deleted ) {
			throw new \Exception( 'Failed to revoke user capabilities' );
		}
	}
}
