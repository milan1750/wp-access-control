<?php
/**
 * Service Access Manager
 *
 * High-level wrapper for plugin permissions and scope handling.
 *
 * @package WP_Platform_Access_Control
 */

namespace WPAC\Services;

defined( 'ABSPATH' ) || exit;

class AccessManager {

    /**
     * Check permission for current user
     */
    public function can(string $permission, array $context = []): bool {
        $user_id = get_current_user_id();
        if (!$user_id) {
            return false;
        }
        return $this->user_can($user_id, $permission, $context);
    }

    /**
     * Check permission for specific user
     */
    public function user_can(int $user_id, string $permission, array $context = []): bool {
        global $wpdb;

        // Fetch user capabilities and scope slug
        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT capabilities, scope FROM {$wpdb->prefix}wpac_user_capabilities WHERE user_id = %d LIMIT 1",
                $user_id
            )
        );

        if (!$row) {
            return false;
        }

        // Decode capabilities
        $user_caps = maybe_unserialize($row->capabilities);
        if (is_string($user_caps)) {
            $decoded = json_decode($user_caps, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $user_caps = $decoded;
            }
        }

        if (!is_array($user_caps) || (!in_array($permission, $user_caps, true) && '*' !== $permission)) {
            return false;
        }

        if( empty($context)) {
            return true; // Just capability check.
        }

        // Entity and site context
        $entity_id = $context['entity_id'] ?? null;
        $site_id   = $context['site_id'] ?? null;

        // Check permission within scope (DB lookup happens inside)
        return $this->check_scope($user_caps, $permission, $row->scope, $entity_id, $site_id);
    }

    /**
     * Check if current user is super user
     */
    public function is_super_user(): bool {
        $user_id = get_current_user_id();
        if (!$user_id) {
            return false;
        }
        return $this->user_can($user_id, '*');
    }

    /**
     * Internal: Check capability within scope.
     *
     * @param array  $user_caps  User capabilities.
     * @param string $permission Capability to check.
     * @param string $scope_slug Scope slug stored in user_capabilities table.
     * @param int    $entity_id  Optional entity context.
     * @param int    $site_id    Optional site context.
     * @return bool
     */
    protected function check_scope(array $user_caps, string $permission, string $scope_slug, int $entity_id = null, int $site_id = null): bool {
        global $wpdb;

        // 1. Must have capability
        if (!in_array($permission, $user_caps, true) && '*' !== $permission) {
            return false;
        }



        // 2. Load scope config from DB
        $scope = [];
        if ($scope_slug) {
            $scope_row = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT config FROM {$wpdb->prefix}wpac_scopes WHERE slug = %s LIMIT 1",
                    $scope_slug
                )
            );

            if ($scope_row && !empty($scope_row->config)) {
                $scope = maybe_unserialize($scope_row->config);
                if (is_string($scope)) {
                    $decoded = json_decode($scope, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $scope = $decoded;
                    }
                }
            }
        }


        if (!is_array($scope)) {
            $scope = [];
        }

        // 3. Global grants access everywhere
        if (!empty($scope['global']) || '*' === $permission) {
            return true;
        }

        // 4. Site-level access (highest priority)
        if ($site_id && isset($scope['sites'][$site_id]) && $scope['sites'][$site_id] === true) {
            return true;
        }
                error_log( print_r( $scope, true ) );
                error_log( print_r( $site_id, true ) );
                error_log( print_r( $entity_id, true ) );


        // 5. Entity-level access
        if ($entity_id && isset($scope['entities'][$entity_id])) {
            $allowed_sites = $scope['entities'][$entity_id];

            // Empty array = all sites under this entity
            if (empty($allowed_sites)) {
                return true;
            }

            // Specific site must be included
            if ($site_id && in_array($site_id, $allowed_sites, true)) {
                return true;
            }
        }

        // 6. Access denied
        return false;
    }
}