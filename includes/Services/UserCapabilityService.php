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
 * User Capabiolity Service.
 *
 * @since 1.0.0
 */
class UserCapabilityService {

	/**
	 * Repository.
	 *
	 * @var UserCapabilityRepository
	 */
	private UserCapabilityRepository $repo;

	/**
	 * Constructor.
	 *
	 * @param UserCapabilityRepository $repo User Capability Repository.
	 */
	public function __construct( UserCapabilityRepository $repo ) {
		$this->repo = $repo;
	}

	/**
	 * Save user capability record.
	 *
	 * @param UserCapability $model Model.
	 *
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
	 * @param int $user_id User id.
	 *
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

	/**
	 * Get user capability model.
	 *
	 * @param int $user_id User Id.
	 */
	public function get( int $user_id ): ?UserCapability {

		$data = $this->repo->find_by_user( $user_id );

		if ( ! $data ) {
			return null;
		}

		return new UserCapability( (array) $data );
	}

	/**
	 * Get user capabilities as array.
	 *
	 * @param int $user_id User id.
	 */
	public function get_user_capabilities( int $user_id ): array {

		$model = $this->get( $user_id );

		if ( ! $model ) {
			return array();
		}

		return $model->capabilities ?? array();
	}

	/**
	 * Get user role.
	 *
	 * @param int $user_id User id.
	 */
	public function get_role( int $user_id ): ?string {

		$model = $this->get( $user_id );

		return $model ? $model->role : null;
	}

	/**
	 * Get scope slug.
	 *
	 * @param int $user_id User id.
	 */
	public function get_scope( int $user_id ): ?string {

		$model = $this->get( $user_id );

		return $model ? $model->scope : null;
	}

	/**
	 * Check if user has capability.
	 *
	 * @param int    $user_id User id.
	 * @param string $capability User Caps.
	 */
	public function has_capability( int $user_id, string $capability ): bool {

		$caps = $this->get_user_capabilities( $user_id );

		if ( empty( $caps ) ) {
			return false;
		}

		return in_array( $capability, $caps, true );
	}
}
