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
	private ScopeRepository $repo;


	/**
	 * Constructor.
	 *
	 * @param ScopeRepository $repo Repository instance.
	 */
	public function __construct( ScopeRepository $repo ) {
		$this->repo = $repo;
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

		$existing = $this->repo->find_by_slug( $scope->slug );

		if ( $existing && $existing->id !== $scope->id ) {
			throw new \Exception( 'Scope slug already exists' );
		}

		if ( $scope->id ) {

			$updated = $this->repo->update(
				$scope->id,
				$scope->to_array()
			);

			if ( ! $updated ) {
				throw new \Exception( 'Update failed' );
			}
		} else {

			$scope->id = $this->repo->create(
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

		$deleted = $this->repo->delete( $id );

		if ( ! $deleted ) {
			throw new \Exception( 'Delete failed' );
		}
	}

	/**
	 * Get scope configuration by slug
	 *
	 * @param string $scope_slug Scope identifier.
	 * @return array Scope config or empty array.
	 */
	public function get_scope_config( string $scope_slug ): array {
		if ( empty( $scope_slug ) ) {
			return array();
		}

		$row = $this->repo->find_by_slug( $scope_slug );

		if ( ! $row || empty( $row->config ) ) {
			return array();
		}

		$config = maybe_unserialize( $row->config );

		if ( is_string( $config ) ) {
			$decoded = json_decode( $config, true );
			if ( json_last_error() === JSON_ERROR_NONE ) {
				$config = $decoded;
			}
		}

		return is_array( $config ) ? $config : array();
	}
}
