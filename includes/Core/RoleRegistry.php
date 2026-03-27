<?php

namespace WPAC\Core;

defined( 'ABSPATH' ) || exit;

class RoleRegistry {

	/**
	 * Registered roles
	 */
	private static array $roles = array();

	/**
	 * Register role
	 *
	 * Example:
	 * RoleRegistry::register('manager', 'Manager', ['report:view'])
	 */
	public static function register(
		string $role,
		string $label,
		array $permissions = array()
	): void {

		self::$roles[ $role ] = array(
			'key'         => $role,
			'label'       => $label,
			'permissions' => $permissions,
		);
	}

	/**
	 * Get all roles
	 */
	public static function all(): array {
		return self::$roles;
	}

	/**
	 * Get single role
	 */
	public static function get( string $role ): ?array {
		return self::$roles[ $role ] ?? null;
	}

	/**
	 * Get role permissions
	 */
	public static function permissions( string $role ): array {
		return self::$roles[ $role ]['permissions'] ?? array();
	}
}
