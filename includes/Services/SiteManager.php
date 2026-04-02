<?php

namespace WPAC\Services;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SiteManager {

    private string $table;

    public function __construct() {
        global $wpdb;
        $this->table = $wpdb->prefix . 'wpac_sites';
    }

    /**
     * Get all sites.
     *
     * @param int|null $status Optional status filter (1 = active, 0 = inactive)
     * @return array
     */
    public function get_all( ?int $status = null ): array {
        global $wpdb;

        $sql = "SELECT * FROM {$this->table}";
        $params = array();

        if ( null !== $status ) {
            $sql .= ' WHERE status = %d';
            $params[] = $status;
        }

        $sql .= ' ORDER BY name ASC';

        if ( $params ) {
            return $wpdb->get_results( $wpdb->prepare( $sql, ...$params ), ARRAY_A );
        }

        return $wpdb->get_results( $sql, ARRAY_A );
    }

    /**
     * Get a single site by ID.
     *
     * @param int $id
     * @return object|null
     */
    public function get_site( int $id ): ?object {
        global $wpdb;

        return $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM {$this->table} WHERE id = %d", $id )
        );
    }

    /**
     * Get a single site by slug.
     *
     * @param string $slug
     * @return object|null
     */
    public function get_site_by_slug( string $slug ): ?object {
        global $wpdb;

        return $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM {$this->table} WHERE slug = %s", $slug )
        );
    }

    /**
     * Get all sites for a given entity ID.
     *
     * @param int $entity_id
     * @param int|null $status Optional status filter
     * @return array
     */
    public function get_sites_by_entity( int $entity_id, ?int $status = null ): array {
        global $wpdb;

        $sql = "SELECT * FROM {$this->table} WHERE entity_id = %d";
        $params = [$entity_id];

        if ( null !== $status ) {
            $sql .= ' AND status = %d';
            $params[] = $status;
        }

        $sql .= ' ORDER BY name ASC';

        return $wpdb->get_results( $wpdb->prepare( $sql, ...$params ), ARRAY_A );
    }

    /**
     * Insert or update a site.
     *
     * @param int $entity_id
     * @param string $site_id
     * @param string $name
     * @param string $slug
     * @param string|null $location
     * @param int|null $id Optional site ID to update
     * @return int|false Inserted ID or false on failure
     */
    public function save( int $entity_id, string $site_id, string $name, string $slug, ?string $location = null, ?int $id = null ) {
        global $wpdb;

        $data = [
            'entity_id' => $entity_id,
            'site_id'   => sanitize_text_field( $site_id ),
            'name'      => sanitize_text_field( $name ),
            'slug'      => sanitize_title( $slug ),
            'location'  => $location ? sanitize_text_field( $location ) : null,
            'status'    => 1,
        ];

        if ( $id ) {
            $updated = $wpdb->update(
                $this->table,
                $data,
                ['id' => $id],
                ['%d','%s','%s','%s','%s','%d'],
                ['%d']
            );
            return $updated !== false ? $id : false;
        }

        $wpdb->insert(
            $this->table,
            $data,
            ['%d','%s','%s','%s','%s','%d']
        );

        return $wpdb->insert_id ?: false;
    }

    /**
     * Delete a site.
     *
     * @param int $id
     * @return bool
     */
    public function delete( int $id ): bool {
        global $wpdb;

        return (bool) $wpdb->delete( $this->table, ['id' => $id], ['%d'] );
    }

    /**
     * Set site status (active/inactive)
     *
     * @param int $id
     * @param int $status
     * @return bool
     */
    public function set_status( int $id, int $status ): bool {
        global $wpdb;

        return (bool) $wpdb->update(
            $this->table,
            ['status' => $status],
            ['id' => $id],
            ['%d'],
            ['%d']
        );
    }
}
