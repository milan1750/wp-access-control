<?php

namespace WPAC\Models;

defined( 'ABSPATH' ) || exit;

/**
 * Site Model (Branch Level).
 */
class Site {

	public int $id;

	public int $entity_id;

	public string $name;

	public function __construct( int $id = 0, int $entity_id = 0, string $name = '' ) {
		$this->id        = $id;
		$this->entity_id = $entity_id;
		$this->name      = $name;
	}
}
