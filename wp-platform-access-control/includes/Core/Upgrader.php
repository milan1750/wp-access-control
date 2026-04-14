<?php
/**
 * Upgrader.
 *
 * @package WPAC
 */

namespace WPAC\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Handles plugin upgrades and migrations.
 */
class Upgrader {

	/**
	 * Run upgrade logic.
	 *
	 * @param string|null $old_version Old version.
	 * @param string|null $new_version New version.
	 */
	public static function run( $old_version, $new_version ): void {
		// future migrations.
	}
}
