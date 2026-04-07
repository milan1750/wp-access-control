<?php
/**
 * Users access management page.
 *
 * @package WPAC
 */

namespace WPAC\Admin;

use WPAC\Services\RoleService;
use WPAC\Services\ScopeService;
use WPAC\Services\UserCapabilityService;

/**
 * User Page.
 *
 * @since 1.0.0
 */
class UsersPage {

	/**
	 * Render
	 *
	 * @since 1.0.0
	 */
	public static function render() {

		$users = get_users();

		$role_service  = wpac()->container()->get( RoleService::class );
		$scope_service = wpac()->container()->get( ScopeService::class );
		$user_service  = wpac()->container()->get( UserCapabilityService::class );

		// Roles.
		$roles = $role_service->all();

		// Scopes.
		$scopes = $scope_service->all();

		// Fetch all capabilities via filter.
		$all_caps = apply_filters( 'wpac_get_capabilities', array() );

		// User overrides.
		$user_overrides = array();

		foreach ( $users as $user ) {

			$data = $user_service->get_user_capabilities( $user->ID );

			if ( $data ) {
				$user_overrides[ $user->ID ] = array(
					'role'         => $data['role'] ?? '',
					'scope'        => $data['scope'] ?? 'global',
					'capabilities' => $data['capabilities'] ?? array(),
				);
			}
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

		error_log( print_r( $modules, true ) );

		include WPAC_PLUGIN_DIR . '/templates/admin/users.php';
	}
}
