<?php
/**
 * User Capability Service.
 *
 * @package WPAC
 */

namespace WPAC\Services;

use WPAC\Models\UserCapability;
use WPAC\Repositories\UserCapabilityRepository;

defined( 'ABSPATH' ) || exit;

/**
 * User Capability Service.
 *
 * @since 1.0.0
 */
class UserCapabilityService {

	/**
	 * User Capability Repository.
	 *
	 * @since 1.0.0
	 * @var UserCapabilityRepository
	 */
	private UserCapabilityRepository $repo;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param  UserCapabilityRepository $repo User Capability Repo.
	 */
	public function __construct( UserCapabilityRepository $repo ) {
		$this->repo = $repo;
	}

	/**
	 * Save Capability.
	 *
	 * @since 1.0.0
	 *
	 * @param  UserCapability $model User Capibility Model.
	 *
	 * @return UserCapability
	 * @throws \Exception Exception.
	 */
	public function save( UserCapability $model ): UserCapability {

		if ( ! $model->user_id ) {
			throw new \Exception( 'User is required' );
		}

		$existing = $this->repo->find_by_user( $model->user_id );

		if ( $existing ) {

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
	 * @param int $user_id User ID.
	 * @throws \Exception Exception.
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
