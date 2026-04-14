<?php
/**
 * Sites Page.
 *
 * @package WPAC
 */

namespace WPAC\Admin;

use WPAC\Services\EntityService;
use WPAC\Services\SiteService;

/**
 * Sites Page.
 *
 * @since 1.0.0
 */
class SitesPage {

	/**
	 * Render.
	 *
	 * @since 1.0.0
	 */
	public static function render() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Get repositories.
		$site_service   = wpac()->container()->get( SiteService::class );
		$entity_service = wpac()->container()->get( EntityService::class );

		// Fetch data.
		$sites    = $site_service->all();
		$entities = $entity_service->all();

		// Load template.
		include WPAC_PLUGIN_DIR . '/templates/admin/sites.php';
	}
}
