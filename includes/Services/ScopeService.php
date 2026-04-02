<?php
/**
 * Scope Service
 *
 * @package WPAC
 * @since 1.0.0
 */

namespace WPAC\Services;

use WPAC\Models\Scope;
use WPAC\Repositories\ScopeRepository;

defined( 'ABSPATH' ) || exit;

/**
 * Service for managing scopes.
 *
 * @since 1.0.0
 */
class ScopeService {

	/**
	 * Repository for scope data access.
	 *
	 * @var ScopeRepository
	 */
	private ScopeRepository $scope_repo;


	/**
	 * Constructor.
	 *
	 * @param ScopeRepository $scope_repo Repository instance.
	 */
	public function __construct( ScopeRepository $scope_repo ) {
		$this->scope_repo = $scope_repo;
	}

	/**
	 * Save or update a scope. Validates required fields and unique slug.
	 *
	 * @param Scope $scope Scope model to save or update.
	 *
	 * @return Scope
	 *
	 * @throws \Exception If validation fails or save operation fails.
	 */
	public function save( Scope $scope ): Scope {

		if ( empty( $scope->name ) ) {
			throw new \Exception( 'Scope name required' );
		}

		if ( empty( $scope->slug ) ) {
			$scope->slug = sanitize_title( $scope->name );
		}

		$existing = $this->scope_repo->find_by_slug( $scope->slug );

		if ( $existing && $existing->id !== $scope->id ) {
			throw new \Exception( 'Scope slug already exists' );
		}

		if ( $scope->id ) {

			$updated = $this->scope_repo->update(
				$scope->id,
				$scope->to_array()
			);

			if ( ! $updated ) {
				throw new \Exception( 'Update failed' );
			}
		} else {

			$scope->id = $this->scope_repo->create(
				$scope->to_array()
			);

			if ( ! $scope->id ) {
				throw new \Exception( 'Insert failed' );
			}
		}

		return $scope;
	}


	/**
	 * Delete a scope by ID.
	 *
	 * @param int $id Scope ID to delete.
	 * @return void
	 * @throws \Exception If deletion fails or ID is invalid.
	 *
	 * @since 1.0.0
	 */
	public function delete( int $id ): void {

		if ( ! $id ) {
			throw new \Exception( 'Invalid ID' );
		}

		$deleted = $this->scope_repo->delete( $id );

		if ( ! $deleted ) {
			throw new \Exception( 'Delete failed' );
		}
	}
}
