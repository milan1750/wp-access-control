<?php
/**
 * RoleService.php
 *
 * @package WPAC
 * @subpackage Services
 * @since 1.0.0
 */

namespace WPAC\Services;

use WPAC\Models\Role;
use WPAC\Repositories\RoleRepository;
use WPAC\Repositories\RoleCapabilityRepository;

/**
 * Service for managing roles and their capabilities
 *
 * @since 1.0.0
 */
class RoleService {

	/**
	 * Repository for role data access
	 *
	 * @var RoleRepository
	 */
	private RoleRepository $role_repo;

	/**
	 * Repository for role capability data access.
	 *
	 * @var RoleCapabilityRepository
	 */
	private RoleCapabilityRepository $cap_repo;

	/**
	 * Constructor.
	 *
	 * @param RoleRepository           $role_repo Role repository instance.
	 * @param RoleCapabilityRepository $cap_repo Role capability repository instance.
	 */
	public function __construct( RoleRepository $role_repo, RoleCapabilityRepository $cap_repo ) {
		$this->role_repo = $role_repo;
		$this->cap_repo  = $cap_repo;
	}


	/**
	 * Save or update a role and its capabilities.
	 *
	 * @param Role  $role The role to save or update.
	 * @param array $capabilities Array of capability slugs to assign to the role.
	 * @return Role The saved or updated role.
	 * @throws \Exception If a role with the same slug already exists.
	 */
	public function create_or_update( Role $role, array $capabilities = array() ): Role {
		// Check unique slug.
		$existing = $this->role_repo->find_by_slug( $role->slug );
		if ( $existing && $existing->id !== $role->id ) {
			throw new \Exception( 'Role with this slug already exists' );
		}

		if ( $role->id ) {
			// Update.
			$this->role_repo->update( $role->id, $role->to_array() );
		} else {
			// Insert.
			$role->id = $this->role_repo->create( $role->to_array() );
		}

		// Update capabilities.
		$this->cap_repo->set_role_capabilities( $role->id, $capabilities );

		return $role;
	}

	/**
	 * Delete a role and all related data.
	 *
	 * @param int $role_id The ID of the role to delete.
	 * @throws \Exception If deletion fails or ID is invalid.
	 */
	public function delete( int $role_id ): void {
		if ( ! $role_id ) {
			throw new \Exception( 'Invalid role ID' );
		}

		// Delete the role itself.
		$deleted = $this->role_repo->delete( $role_id );
		if ( ! $deleted ) {
			throw new \Exception( 'Failed to delete role' );
		}

		// Delete related role capabilities.
		$this->cap_repo->delete_by_role( $role_id );
	}
}
