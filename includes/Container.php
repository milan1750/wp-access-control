<?php
/**
 * Main Plugin Bootstrap Class.
 *
 * @package WP_Platform_Access_Control
 */

namespace WPAC;

defined( 'ABSPATH' ) || exit;

/**
 * Simple Dependency Injection Container.
 *
 * @since 1.0.0
 */
class Container {

	/**
	 * Registered services.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private array $services = array();


	/**
	 * Register a service.
	 *
	 * @since 1.0.0
	 *
	 * @param string $id Service identifier.
	 * @param mixed  $resolver Callable resolver or direct instance.
	 */
	public function set( $id, $resolver ) {
		$this->services[ $id ] = $resolver;
	}


	/**
	 * Get a service instance.
	 *
	 * @since 1.0.0
	 *
	 * @param string $id Service identifier.
	 * @return mixed
	 * @throws \Exception If service not found.
	 */
	public function get( $id ) {
		if ( ! isset( $this->services[ $id ] ) ) {
			throw new \Exception( 'Service ' . esc_html( $id ) . ' not found' );
		}

		// Resolve callable only once (singleton).
		if ( is_callable( $this->services[ $id ] ) ) {
			$this->services[ $id ] = $this->services[ $id ]( $this );
		}

		return $this->services[ $id ];
	}
}
