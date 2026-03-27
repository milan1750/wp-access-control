<?php

use WPAC\Plugin;

/**
 * Quick permission helper.
 *
 * @param string   $capability Capability key.
 * @param int|null $entity_id Entity ID.
 * @param int|null $site_id Site ID.
 */
function wpac_can( string $capability, ?int $entity_id = null, ?int $site_id = null ): bool {
	return Plugin::instance()->access()->can(
		get_current_user_id(),
		$capability,
		$entity_id,
		$site_id
	);
}
