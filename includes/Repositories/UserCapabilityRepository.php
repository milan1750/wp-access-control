<?php

namespace WPAC\Repositories;

use WPAC\Models\UserCapability;

defined( 'ABSPATH' ) || exit;

class UserCapabilityRepository {

	private string $table;

	public function __construct() {
		global $wpdb;
		$this->table = $wpdb->prefix . 'wpac_user_capabilities';
	}

	public function find_by_user( int $user_id ): ?UserCapability {

		global $wpdb;

		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$this->table} WHERE user_id = %d LIMIT 1",
				$user_id
			)
		);

		return $row ? new UserCapability( (array) $row ) : null;
	}

	public function create( array $data ): int {

		global $wpdb;

		$wpdb->insert(
			$this->table,
			$data,
			array( '%d', '%s', '%s', '%s' )
		);

		return (int) $wpdb->insert_id;
	}

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
	 * @param int $user_id
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
