<?php
/**
 * UserRoleRepository
 *
 * @package WPAC
 * @since 1.0.0
 */

namespace WPAC\Repositories;

/**
 * Repository for managing user roles in the database.
 *
 * @since 1.0.0
 */
class UserRoleRepository {

	/**
	 * The table name.
	 *
	 * @var string
	 */
	private string $table;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		global $wpdb;
		$this->table = $wpdb->prefix . 'wpac_user_roles';
	}

	/**
	 * Set roles for a user.
	 *
	 * @param int $role_id  The role ID.
	 */
	public function delete_by_role( int $role_id ): void {
		global $wpdb;
		$wpdb->delete( $this->table, array( 'role_id' => $role_id ), array( '%d' ) );
	}
}
