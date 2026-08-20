<?php
/**
 * Resolves a stored callback reference into a real, callable filter callback.
 *
 * @package MCPHelpers
 */

namespace MCPHelpers\Callback;

use MCPHelpers\Catalog\CallbackCatalog;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Turns a {@see CallbackReference} into an actual callable, enforcing the
 * allow-list and validating bound args against the approved callback's params.
 */
class CallbackResolver {

	/**
	 * Allow-list of approved callbacks.
	 *
	 * @var CallbackCatalog
	 */
	private $catalog;

	/**
	 * @param CallbackCatalog $catalog Allow-list.
	 */
	public function __construct( CallbackCatalog $catalog ) {
		$this->catalog = $catalog;
	}

	/**
	 * Validates that a reference points at an approved callback and that its
	 * required args are present. Does not build the callable.
	 *
	 * @param CallbackReference $reference Reference to validate.
	 * @return true|\WP_Error
	 */
	public function validate( CallbackReference $reference ) {
		$entry = $this->catalog->get( $reference->id() );

		if ( null === $entry ) {
			return new \WP_Error(
				'mcp_helpers_callback_not_approved',
				sprintf(
					/* translators: %s: callback id. */
					__( 'The callback "%s" is not in the approved-callback catalog.', 'mcp-helpers' ),
					$reference->id()
				)
			);
		}

		$args = $reference->args();

		foreach ( $entry['params'] as $param ) {
			$name     = (string) ( $param['name'] ?? '' );
			$required = ! empty( $param['required'] );

			if ( '' !== $name && $required && ! array_key_exists( $name, $args ) ) {
				return new \WP_Error(
					'mcp_helpers_missing_arg',
					sprintf(
						/* translators: 1: arg name, 2: callback id. */
						__( 'Missing required argument "%1$s" for callback "%2$s".', 'mcp-helpers' ),
						$name,
						$reference->id()
					)
				);
			}
		}

		return true;
	}

	/**
	 * Resolves a reference into a filter callback.
	 *
	 * @param CallbackReference $reference Reference to resolve.
	 * @return callable|\WP_Error
	 */
	public function resolve( CallbackReference $reference ) {
		$valid = $this->validate( $reference );

		if ( is_wp_error( $valid ) ) {
			return $valid;
		}

		$entry    = $this->catalog->get( $reference->id() );
		$callback = ( $entry['factory'] )( $reference->args() );

		if ( ! is_callable( $callback ) ) {
			return new \WP_Error(
				'mcp_helpers_unresolvable_callback',
				sprintf(
					/* translators: %s: callback id. */
					__( 'The callback "%s" did not produce a callable.', 'mcp-helpers' ),
					$reference->id()
				)
			);
		}

		return $callback;
	}
}
