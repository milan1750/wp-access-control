<?php

namespace WPAC\Models;

defined( 'ABSPATH' ) || exit;

/**
 * Entity Model (Company Level).
 */
class Entity {

	public int $id;

	public string $name;

	public function __construct( int $id = 0, string $name = '' ) {
		$this->id   = $id;
		$this->name = $name;
	}
}
