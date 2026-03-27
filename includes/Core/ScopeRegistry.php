<?php

namespace WPAC\Core;

defined( 'ABSPATH' ) || exit;

class ScopeRegistry {

	private static array $scopes = array();

	/**
	 * Register scope
	 *
	 * global | entity | site | project | warehouse
	 */
	public static function register(
		string $scope,
		string $label
	): void {

		self::$scopes[ $scope ] = array(
			'key'   => $scope,
			'label' => $label,
		);
	}

	/**
	 * Get all scopes
	 */
	public static function all(): array {
		return self::$scopes;
	}

	/**
	 * Check valid scope
	 */
	public static function exists( string $scope ): bool {
		return isset( self::$scopes[ $scope ] );
	}
}
