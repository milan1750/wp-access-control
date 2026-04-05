<?php
/**
 *
 */
namespace WPAC\Core;

defined( 'ABSPATH' ) || exit;

class AccessManager {

	/**
	 * Check if user has permission
	 */
	public static function can(
		int $user_id,
		string $permission,
		array $context = array()
	): bool {

		global $wpdb;

		$table = $wpdb->prefix . 'wpac_user_access';

		// ✅ SAFE QUERY (NO %s FOR TABLE NAME)
		$sql = "SELECT * FROM {$table} WHERE user_id = %d";

		$rows = $wpdb->get_results(
			$wpdb->prepare( $sql, $user_id )
		);

		if ( empty( $rows ) ) {
			return false;
		}

		foreach ( $rows as $row ) {

			// 1. SUPER USER (global bypass)
			if ( $row->role === 'super_user' ) {
				return true;
			}

			// 2. Decode permissions safely
			$permissions = json_decode( $row->permissions ?? '[]', true );

			if ( ! is_array( $permissions ) ) {
				$permissions = array();
			}

			// 3. SCOPE CHECK FIRST (IMPORTANT FIX)
			if ( ! self::scope_match( $row, $context ) ) {
				continue;
			}

			// 4. Wildcard permission
			if ( in_array( '*', $permissions, true ) ) {
				return true;
			}

			// 5. Exact permission check
			if ( in_array( $permission, $permissions, true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Scope validation
	 */
	private static function scope_match( $row, array $context ): bool {

		// GLOBAL SCOPE
		if ( empty( $row->entity_id ) && empty( $row->site_id ) ) {
			return true;
		}

		// ENTITY SCOPE
		if ( ! empty( $row->entity_id ) ) {

			if (
				isset( $context['entity_id'] ) &&
				(int) $context['entity_id'] === (int) $row->entity_id
			) {
				return true;
			}

			return false;
		}

		// SITE SCOPE
		if ( ! empty( $row->site_id ) ) {

			if (
				isset( $context['site_id'] ) &&
				(int) $context['site_id'] === (int) $row->site_id
			) {
				return true;
			}

			return false;
		}

		return true;
	}
}
