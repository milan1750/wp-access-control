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
}
