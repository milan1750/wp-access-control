<?php
/**
 * Service Access Manager
 *
 * High-level wrapper for plugin permissions and scope handling.
 *
 * @package WP_Platform_Access_Control
 */

namespace WPAC\Services;

defined( 'ABSPATH' ) || exit;

use WPAC\Repositories\UserCapabilityRepository;
use WPAC\Repositories\ScopeRepository;

/**
 * Class AccessManager
 *
 * Provides methods to check user permissions and scope.
 *
 * @since 1.0.0
 */
class PermissionService {

	/**
	 * User Capability Repo.
	 *
	 * @since 1.0.0
	 * @var UserCapabilityRepository
	 */
	protected UserCapabilityRepository $user_cap_repo;

	/**
	 * Scope Repository.
	 *
	 * @since 1.0.0
	 * @var ScopeRepository
	 */
	protected ScopeRepository $scope_repo;

	/**
	 * Constructor.
	 *
	 * @param UserCapabilityRepository $user_cap_repo User Capability Repository.
	 * @param ScopeRepository          $scope_repo Scope Repository.
	 */
	public function __construct(
		UserCapabilityRepository $user_cap_repo,
		ScopeRepository $scope_repo
	) {
		$this->user_cap_repo = $user_cap_repo;
		$this->scope_repo    = $scope_repo;
	}

	/**
	 * Check permission for current user
	 *
	 * @param string $permission Permission to check.
	 * @param array  $context    Optional context (entity_id, site_id).
	 * @return bool
	 */
	public function can( string $permission, array $context = array() ): bool {
		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return false;
		}
		return $this->user_can( $user_id, $permission, $context );
	}

	/**
	 * Check permission for specific user
	 *
	 * @param int    $user_id    User ID.
	 * @param string $permission Permission to check.
	 * @param array  $context    Optional context (entity_id, site_id).
	 * @return bool
	 */
	public function user_can( int $user_id, string $permission, array $context = array() ): bool {
		$user_caps = $this->user_cap_repo->find_by_user( $user_id );
		if ( null === $user_caps || ( ! in_array( $permission, $user_caps->capabilities, true ) && '*' !== $permission ) ) {
			return false;
		}

		if ( empty( $context ) ) {
			return true;
		}

		error_log( print_r( $user_caps, true ) );

		$scope_slug = $user_caps->scope;

		return $this->check_scope( $user_caps->capabilities, $permission, $scope_slug, $context['entity_id'] ?? null, $context['site_id'] ?? null );
	}

	/**
	 * Check if current user is super user
	 *
	 * @return bool
	 */
	public function is_super_user(): bool {
		return $this->can( '*' );
	}

	/**
	 * Internal: Check capability within scope.
	 *
	 * @param array  $user_caps  User capabilities.
	 * @param string $permission Capability to check.
	 * @param string $scope_slug Scope slug stored in user_capabilities table.
	 * @param int    $entity_id  Optional entity context.
	 * @param int    $site_id    Optional site context.
	 * @return bool
	 */
	protected function check_scope(
		array $user_caps,
		string $permission,
		?string $scope_slug,
		?int $entity_id = null,
		?int $site_id = null
	): bool {
		// 1. Must have capability
		if ( ! in_array( $permission, $user_caps, true ) && '*' !== $permission ) {
			return false;
		}

		// 2. Load scope config from repository.
		$scope = $scope_slug ? wpac()->scopes()->get_scope_config( $scope_slug ) : array();

		// Ensure array.
		if ( ! is_array( $scope ) ) {
			$scope = array();
		}

		// 3. Global access.
		if ( ! empty( $scope['global'] ) || '*' === $permission ) {
			return true;
		}

		// 4. Site-level access.
		if ( $site_id && ! empty( $scope['sites'][ $site_id ] ) ) {
			return true;
		}

		// 5. Entity-level access.
		if ( $entity_id && isset( $scope['entities'][ $entity_id ] ) ) {
			$allowed_sites = $scope['entities'][ $entity_id ];
			if ( empty( $allowed_sites ) || ( $site_id && in_array( $site_id, $allowed_sites, true ) ) ) {
				return true;
			}
		}

		return false;
	}
}
