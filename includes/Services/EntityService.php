<?php
/**
 * Entity Service
 *
 * Handles business logic for Entities.
 *
 * @package WPAC
 */

namespace WPAC\Services;

use WPAC\Models\Entity;
use WPAC\Repositories\EntityRepository;

defined( 'ABSPATH' ) || exit;

/**
 * Service for managing entities.
 *
 * @since 1.0.0
 */
class EntityService {

	/**
	 * Entity repository instance.
	 *
	 * @var EntityRepository
	 */
	private EntityRepository $repo;

	/**
	 * Constructor.
	 *
	 * @param EntityRepository $repo Repository instance.
	 */
	public function __construct( EntityRepository $repo ) {
		$this->repo = $repo;
	}

	/**
	 * Create or update an entity.
	 *
	 * @param Entity $entity Entity model.
	 * @return Entity
	 * @throws \Exception If validation fails.
	 */
	public function create_or_update( Entity $entity ): Entity {

		// Ensure name exists.
		if ( empty( $entity->name ) ) {
			throw new \Exception( 'Entity name required' );
		}

		// Generate slug if missing.
		if ( empty( $entity->slug ) ) {
			$entity->slug = sanitize_title( $entity->name );
		}

		// Check duplicate slug.
		$existing = $this->repo->find_by_slug( $entity->slug );

		if ( $existing && $existing->id !== $entity->id ) {
			throw new \Exception( 'Entity with this slug already exists' );
		}

		if ( $entity->id ) {

			$result = $this->repo->update(
				$entity->id,
				$entity->to_array()
			);

			if ( false === $result ) {
				throw new \Exception( 'Failed to update entity' );
			}
		} else {

			$entity->id = $this->repo->create(
				$entity->to_array()
			);

			if ( ! $entity->id ) {
				throw new \Exception( 'Failed to create entity' );
			}
		}

		return $entity;
	}

	/**
	 * Delete an entity.
	 *
	 * @param int $id Entity ID.
	 * @return void
	 * @throws \Exception If deletion fails.
	 */
	public function delete( int $id ): void {

		if ( ! $id ) {
			throw new \Exception( 'Invalid entity ID' );
		}

		$deleted = $this->repo->delete( $id );

		if ( ! $deleted ) {
			throw new \Exception( 'Delete failed' );
		}
	}

	/**
	 * Get all entities.
	 *
	 * @return Entity[]
	 */
	public function all(): array {
		$data_list = $this->repo->find_all();

		$entities = array();
		foreach ( $data_list as $data ) {
			$entities[] = new Entity( (array) $data );
		}

		return $entities;
	}

	/**
	 * Get an entity by ID.
	 *
	 * @param int $id Entity ID.
	 * @return Entity|null
	 * @throws \Exception If retrieval fails.
	 */
	public function get( int $id ): ?Entity {
		$data = $this->repo->find_by_id( $id );
		return $data ? new Entity( $data ) : null;
	}

	/**
	 * Get an entity by slug.
	 *
	 * @param string $slug Entity slug.
	 * @return Entity|null
	 */
	public function get_by_slug( string $slug ): ?Entity {
		$data = $this->repo->find_by_slug( $slug );
		return $data ? new Entity( $data ) : null;
	}

	/**
	 * Get all entities as plain arrays (for JS/localize).
	 *
	 * @return array[]
	 */
	public function all_array(): array {
		return array_map( fn( Entity $entity ) => $entity->to_array(), $this->all() );
	}
}
