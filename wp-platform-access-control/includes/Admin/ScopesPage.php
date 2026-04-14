<?php
/**
 * Scopes Page.
 *
 * @package WPAC
 */

namespace WPAC\Admin;

use Dom\Entity;
use WPAC\Services\ScopeService;
use WPAC\Services\SiteService;
use WPAC\Services\EntityService;

/**
 * Scope Page.
 *
 * @since 1.0.0
 */
class ScopesPage {

	/**
	 * Render.
	 */
	public static function render() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$scope_service  = wpac()->container()->get( ScopeService::class );
		$entity_service = wpac()->container()->get( EntityService::class );
		$site_service   = wpac()->container()->get( SiteService::class );

		$scopes       = $scope_service->all();
		$entities     = $entity_service->all();
		$entity_sites = $site_service->get_sites_grouped();

		include WPAC_PLUGIN_DIR . '/templates/admin/scopes.php';
	}
}
