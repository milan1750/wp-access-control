<?php

namespace WPAC\Repositories;

defined( 'ABSPATH' ) || exit;

/**
 * Handles Role storage.
 */
class RoleRepository {

	public function get_all(): array {
		return get_option( 'wpac_roles', array() );
	}

	public function find( int $id ): array {
		$roles = $this->get_all();
		return $roles[ $id ] ?? array();
	}

	public function save( int $id, array $data ): void {
		$roles        = $this->get_all();
		$roles[ $id ] = $data;

		update_option( 'wpac_roles', $roles );
	}
}
