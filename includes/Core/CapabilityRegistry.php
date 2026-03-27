<?php

namespace WPAC\Core;

defined( 'ABSPATH' ) || exit;

class CapabilityRegistry {

	private static array $modules = array();

	/**
	 * Register module with actions
	 */
	public static function register_module( string $key, array $actions, string $label ): void {

		self::$modules[ $key ] = array(
			'label'   => $label,
			'actions' => $actions,
		);
	}

	/**
	 * Get all modules
	 */
	public static function all(): array {
		return self::$modules;
	}

	/**
	 * Get flat capabilities (optional helper)
	 */
	public static function capabilities(): array {

		$caps = array();

		foreach ( self::$modules as $moduleKey => $module ) {
			foreach ( $module['actions'] as $action ) {

				$key = $moduleKey . '.' . $action;

				$caps[ $key ] = array(
					'label'  => ucfirst( $action ),
					'module' => $moduleKey,
					'group'  => $module['label'],
				);
			}
		}

		return $caps;
	}
}
