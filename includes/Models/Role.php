<?php

namespace WPAC\Models;

defined( 'ABSPATH' ) || exit;

/**
 * Role Model.
 */
class Role {

	/**
	 * Role ID.
	 */
	public int $id;

	/**
	 * Role name.
	 */
	public string $name;

	/**
	 * Capabilities list.
	 */
	public array $capabilities = array();

	/**
	 * Constructor.
	 */
	public function __construct( int $id = 0, string $name = '', array $capabilities = array() ) {
		$this->id           = $id;
		$this->name         = $name;
		$this->capabilities = $capabilities;
	}
}
