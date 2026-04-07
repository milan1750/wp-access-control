<?php
/**
 * Roles Page.
 *
 * @package WPAC
 */

namespace WPAC\Admin;

use WPAC\Services\RoleService;

/**
 * Roles Admin Page.
 *
 * @since 1.0.0
 */
class RolesPage {

	/**
	 * Render the Roles page.
	 */
	public static function render() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Get the RoleService.
		$role_service = wpac()->container()->get( RoleService::class );

		// Fetch all roles.
		$roles = $role_service->all();

		// Roles Caps.
		$caps_by_roles = array();

		foreach ( $roles as $role ) {
			$role_caps                  = $role_service->role_capabilities( $role->id );
			$caps_by_roles[ $role->id ] = $role_caps;

		}

		// Fetch all capabilities via filter.
		$all_caps = apply_filters( 'wpac_get_capabilities', array() );
		// Organize capabilities by module.
		$modules = array();
		foreach ( $all_caps as $key => $cap ) {
			$module = $cap['module'] ? $cap['module'] : 'general';
			if ( ! isset( $modules[ $module ] ) ) {
				$modules[ $module ] = array(
					'label'        => ucfirst( $module ),
					'capabilities' => array(),
				);
			}
			$modules[ $module ]['capabilities'][ $key ] = $cap['label'];
		}

		include WPAC_PLUGIN_DIR . '/templates/admin/roles.php';
	}
}
