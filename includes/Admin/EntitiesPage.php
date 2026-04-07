<?php
/**
 * Entity Page.
 *
 * @package WPAC
 */

namespace WPAC\Admin;

use WPAC\Repositories\EntityRepository;
use WPAC\Services\EntityService;

/**
 * Entities Page.
 *
 * @since 1.0.0
 */
class EntitiesPage {

	/**
	 * Reder.
	 *
	 * @since 1.0.0
	 */
	public static function render() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$entity_service = wpac()->container()->get( EntityService::class );
		$entities       = $entity_service->all();

		include WPAC_PLUGIN_DIR . '/templates/admin/entities.php';
	}
}
