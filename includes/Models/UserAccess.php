<?php

namespace WPAC\Models;

defined( 'ABSPATH' ) || exit;

/**
 * User Access Model.
 */
class UserAccess {

	public int $user_id;

	public string $scope; // global | entity | site

	public array $entities = array();

	public array $sites = array();

	public array $permissions = array();

	public function __construct( int $user_id = 0 ) {
		$this->user_id = $user_id;
	}
}
