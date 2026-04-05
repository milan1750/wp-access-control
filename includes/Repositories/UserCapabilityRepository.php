<?php
/**
 * User Capability Repo.
 *
 * @package WPAC
 */

namespace WPAC\Repositories;

use WPAC\Models\UserCapability;

defined( 'ABSPATH' ) || exit;

/**
 * User Capability Repository
 *
 * @since 1.0.0
 */
class UserCapabilityRepository {

	/**
	 * Table Name
	 *
	 * @since 1.0.0
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
		$this->table = $wpdb->prefix . 'wpac_user_capabilities';
	}

	/**
	 * Find By User.
	 *
	 * @since 1.0.0
	 *
	 * @param  int $user_id User ID.
	 *
	 * @return UserCapability|null
	 */
	public function find_by_user( int $user_id ): ?UserCapability {

		global $wpdb;

		$row = $wpdb->get_row(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT * FROM {$this->table} WHERE user_id = %d LIMIT 1",
				$user_id
			)
		);

		return $row ? new UserCapability( (array) $row ) : null;
	}


	/**
	 * Create.
	 *
	 * @since 1.0.0
	 *
	 * @param  array $data Data.
	 *
	 * @return int
	 */
	public function create( array $data ): int {

		global $wpdb;

		$wpdb->insert(
			$this->table,
			$data,
			array( '%d', '%s', '%s', '%s' )
		);

		return (int) $wpdb->insert_id;
	}

	/**
	 * Update.
	 *
	 * @since 1.0.0
	 *
	 * @param  int   $user_id User Id.
	 * @param  array $data Data.
	 *
	 * @return bool
	 */
	public function update( int $user_id, array $data ): bool {

		global $wpdb;

		return (bool) $wpdb->update(
			$this->table,
			$data,
			array( 'user_id' => $user_id ),
			array( '%s', '%s', '%s' ),
			array( '%d' )
		);
	}

	/**
	 * Delete capability record for a user.
	 *
	 * @param int $user_id User Id.
	 * @return bool
	 */
	public function delete_by_user( int $user_id ): bool {

		global $wpdb;

		return (bool) $wpdb->delete(
			$this->table,
			array(
				'user_id' => $user_id,
			),
			array( '%d' )
		);
	}
}
