<?php
/**
 * Scope Model.
 *
 * @package WPAC
 * @since 1.0.0
 */

namespace WPAC\Models;

defined( 'ABSPATH' ) || exit;

/**
 * Represents a single scope.
 */
class Scope {

	/**
	 * Scope ID.
	 *
	 * @var int
	 */
	public int $id;

	/**
	 * Scope name.
	 *
	 * @var string
	 */
	public string $name;

	/**
	 * Scope slug.
	 *
	 * @var string
	 */
	public string $slug;

	/**
	 * Scope type.
	 *
	 * @var string
	 */
	public string $type;

	/**
	 * Scope configuration.
	 *
	 * @var array|null
	 */
	public ?array $config;

	/**
	 * Scope creation timestamp.
	 *
	 * @var string
	 */
	public string $created_at;

	/**
	 * Constructor.
	 *
	 * @param array $data Optional data to initialize the model.
	 */
	public function __construct( array $data = array() ) {

		$this->id   = isset( $data['id'] ) ? (int) $data['id'] : 0;
		$this->name = $data['name'] ?? '';
		$this->slug = $data['slug'] ?? '';
		$this->type = $data['type'] ?? '';

		// Decode JSON config safely.
		if ( isset( $data['config'] ) ) {
			$this->config = is_array( $data['config'] )
				? $data['config']
				: (array) json_decode( $data['config'], true );
		} else {
			$this->config = array();
		}

		$this->created_at = $data['created_at'] ?? current_time( 'mysql' );
	}

	/**
	 * Convert model to database array.
	 *
	 * @return array
	 */
	public function to_array(): array {
		return array(
			'name'   => $this->name,
			'slug'   => $this->slug,
			'type'   => $this->type,
			'config' => wp_json_encode( $this->config ),
		);
	}
}
