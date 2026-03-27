<?php

namespace WPAC\Core;

defined( 'ABSPATH' ) || exit;

class PermissionRegistry {

	/**
	 * Registered modules
	 *
	 * @var array
	 */
	private static array $modules = array();

	/**
	 * Register module permissions
	 *
	 * Example:
	 * PermissionRegistry::register_module('report', ['view','create'], 'Reports');
	 */
	public static function register_module(
		string $module,
		array $permissions,
		string $label = ''
	): void {

		self::$modules[ $module ] = array(
			'key'         => $module,
			'label'       => $label ?: ucfirst( $module ),
			'permissions' => self::normalize_permissions( $module, $permissions ),
		);
	}

	/**
	 * Normalize permissions into full format
	 *
	 * report + view => report:view
	 */
	private static function normalize_permissions(
		string $module,
		array $permissions
	): array {

		$normalized = array();

		foreach ( $permissions as $perm ) {

			// allow full format already
			if ( strpos( $perm, ':' ) !== false ) {
				$normalized[] = $perm;
				continue;
			}

			$normalized[] = $module . ':' . $perm;
		}

		return $normalized;
	}

	/**
	 * Get all modules
	 */
	public static function all(): array {
		return self::$modules;
	}

	/**
	 * Get module
	 */
	public static function get( string $module ): ?array {
		return self::$modules[ $module ] ?? null;
	}

	/**
	 * Check if permission exists globally
	 */
	public static function exists( string $permission ): bool {

		foreach ( self::$modules as $module ) {

			if ( in_array( $permission, $module['permissions'], true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get flat permission list
	 */
	public static function flatten(): array {

		$list = array();

		foreach ( self::$modules as $module ) {
			$list = array_merge( $list, $module['permissions'] );
		}

		return $list;
	}
}
