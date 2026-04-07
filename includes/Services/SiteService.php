<?php
/**
 * Site Service
 *
 * @package WPAC
 */

namespace WPAC\Services;

use WPAC\Models\Site;
use WPAC\Repositories\SiteRepository;

defined( 'ABSPATH' ) || exit;


/**
 * Site service.
 *
 * @since 1.0.0
 */
class SiteService {

	/**
	 * Repository
	 *
	 * @var SiteRepository
	 */
	private SiteRepository $repo;

	/**
	 * Constructor.
	 *
	 * @param SiteRepository $repo Repository.
	 */
	public function __construct( SiteRepository $repo ) {
		$this->repo = $repo;
	}

	/**
	 * Get all sites.
	 *
	 * @return Site[]
	 */
	public function all(): array {

		$data = $this->repo->find_all();

		$sites = array();

		foreach ( $data as $row ) {
			$sites[] = new Site( $row );
		}

		return $sites;
	}

	/**
	 * Get site by ID.
	 *
	 * @param int $id ID.
	 */
	public function get( int $id ): ?Site {

		$data = $this->repo->find_by_id( $id );

		return $data ? new Site( $data ) : null;
	}

	/**
	 * Get site by slug
	 *
	 * @param string $slug Slug.
	 */
	public function get_by_slug( string $slug ): ?Site {

		$data = $this->repo->find_by_slug( $slug );

		return $data ? new Site( $data ) : null;
	}

	/**
	 * Get sites by entity.
	 *
	 * @param int $entity_id ID.
	 *
	 * @return Site[]
	 */
	public function get_by_entity( int $entity_id ): array {

		$data = $this->repo->find_by_entity( $entity_id );

		$sites = array();

		foreach ( $data as $row ) {
			$sites[] = new Site( $row );
		}

		return $sites;
	}

	/**
	 * Create or update site
	 *
	 * @param Site $site Site.
	 */
	public function save( Site $site ): ?Site {

		$data = array(
			'entity_id' => $site->entity_id,
			'site_id'   => sanitize_text_field( $site->site_id ),
			'name'      => sanitize_text_field( $site->name ),
			'slug'      => sanitize_title( $site->slug ),
			'location'  => sanitize_text_field( $site->location ),
			'status'    => $site->status ?? 1,
		);

		if ( $site->id ) {

			$updated = $this->repo->update( $site->id, $data );

			return $updated ? $site : null;
		}

		$id = $this->repo->create( $data );

		if ( ! $id ) {
			return null;
		}

		$site->id = $id;

		return $site;
	}

	/**
	 * Delete site.
	 *
	 * @param int $id ID.
	 */
	public function delete( int $id ): bool {
		return $this->repo->delete( $id );
	}

	/**
	 * Validate site ID.
	 *
	 * @param string $site_id ID.
	 */
	public function is_valid_site_id( string $site_id ): bool {

		if ( empty( $site_id ) ) {
			return false;
		}

		return (bool) preg_match( '/^[a-zA-Z0-9_-]{3,50}$/', $site_id );
	}

	/**
	 * Get all sites as plain arrays (for JS/localize).
	 *
	 * @return array[]
	 */
	public function all_array(): array {
		return array_map( fn( Site $site ) => $site->to_array(), $this->all() );
	}

	/**
	 * Get sites grouped by entity_id.
	 *
	 * @return array
	 */
	public function get_sites_grouped(): array {
		$sites   = $this->repo->find_all(); // returns array of site arrays.
		$grouped = array();

		foreach ( $sites as $site ) {
			// Ensure site is an array.
			$entity_id = isset( $site['entity_id'] ) ? $site['entity_id'] : 0;

			if ( ! isset( $grouped[ $entity_id ] ) ) {
				$grouped[ $entity_id ] = array();
			}

			// Directly push site array, not wrapped in another array.
			$grouped[ $entity_id ][] = new Site( (array) $site );
		}

		return $grouped;
	}
}
