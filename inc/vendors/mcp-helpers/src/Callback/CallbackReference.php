<?php
/**
 * Value object: a stored reference to an approved callback plus its bound args.
 *
 * @package MCPHelpers
 */

namespace MCPHelpers\Callback;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * What the `callback` column actually stores.
 *
 * Instead of a raw callable string, an entry stores a reference to an approved
 * callback (by its catalog id) together with the arguments to bind. This keeps
 * the stored value both safe (only approved ids can ever run) and expressive
 * (it can carry a value, e.g. the URL to exclude).
 *
 * Serialized form: {"id":"rocket/append-reject-uri","args":{"uri":"/x/(.*)"}}
 */
final class CallbackReference {

	/**
	 * Approved-callback id (its key in the callback catalog).
	 *
	 * @var string
	 */
	private $id;

	/**
	 * Arguments to bind, keyed by param name.
	 *
	 * @var array<string, mixed>
	 */
	private $args;

	/**
	 * @param string               $id   Approved-callback id.
	 * @param array<string, mixed> $args Bound arguments.
	 */
	public function __construct( string $id, array $args = [] ) {
		$this->id   = $id;
		$this->args = $args;
	}

	/**
	 * Builds a reference from a decoded array, validating its shape.
	 *
	 * @param array<string, mixed> $data Decoded data.
	 * @return self|\WP_Error
	 */
	public static function from_array( array $data ) {
		$id = isset( $data['id'] ) ? trim( (string) $data['id'] ) : '';

		if ( '' === $id ) {
			return new \WP_Error( 'mcp_helpers_invalid_reference', __( 'A callback reference must have an id.', 'mcp-helpers' ) );
		}

		$args = [];
		if ( isset( $data['args'] ) ) {
			if ( ! is_array( $data['args'] ) ) {
				return new \WP_Error( 'mcp_helpers_invalid_reference', __( 'Callback args must be an object.', 'mcp-helpers' ) );
			}
			$args = $data['args'];
		}

		return new self( $id, $args );
	}

	/**
	 * Builds a reference from its JSON string form.
	 *
	 * @param string $json JSON string.
	 * @return self|\WP_Error
	 */
	public static function from_json( string $json ) {
		$data = json_decode( $json, true );

		if ( ! is_array( $data ) ) {
			return new \WP_Error( 'mcp_helpers_invalid_reference', __( 'The stored callback is not valid JSON.', 'mcp-helpers' ) );
		}

		return self::from_array( $data );
	}

	/**
	 * @return string
	 */
	public function id(): string {
		return $this->id;
	}

	/**
	 * @return array<string, mixed>
	 */
	public function args(): array {
		return $this->args;
	}

	/**
	 * @return array{id:string, args:array<string, mixed>}
	 */
	public function to_array(): array {
		return [
			'id'   => $this->id,
			'args' => (object) $this->args, // Encode as {} not [] when empty.
		];
	}

	/**
	 * @return string JSON string suitable for storage.
	 */
	public function to_json(): string {
		return (string) wp_json_encode(
			[
				'id'   => $this->id,
				'args' => (object) $this->args,
			]
		);
	}
}
