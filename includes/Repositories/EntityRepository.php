<?php

namespace WPAC\Repositories;

defined( 'ABSPATH' ) || exit;

/**
 * Entity repository (company layer).
 */
class EntityRepository {

	public function all(): array {
		return get_option( 'wpac_entities', array() );
	}

	public function save( int $id, array $data ): void {
		$items        = $this->all();
		$items[ $id ] = $data;

		update_option( 'wpac_entities', $items );
	}
}
