<?php
/**
 * Catalog of approved callbacks (the allow-list).
 *
 * @package MCPHelpers
 */

namespace MCPHelpers\Catalog;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The allow-list of callbacks an entry may reference.
 *
 * The library ships a handful of universally-safe built-ins (WordPress core's
 * `__return_*` helpers). The implementer plugin contributes the rest through the
 * `mcp_helpers_callback_catalog` filter — it is the authority on which of its own
 * functions are safe and intended to be used as filter callbacks.
 *
 * An entry's `factory` is the contract: a `callable(array $args): callable` that
 * binds the reference's args and returns the *actual* filter callback. Built-ins
 * ignore their args and return a core function name.
 */
class CallbackCatalog {

	/**
	 * Filter plugins hook to contribute approved callbacks.
	 */
	public const HOOK = 'mcp_helpers_callback_catalog';

	/**
	 * Memoized, normalized entries keyed by id.
	 *
	 * @var array<string, array<string, mixed>>|null
	 */
	private $entries = null;

	/**
	 * All approved callbacks, keyed by id.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public function all(): array {
		if ( null !== $this->entries ) {
			return $this->entries;
		}

		$entries = $this->builtins();

		/**
		 * Filters the catalog of approved callbacks.
		 *
		 * @param array<string, array<string, mixed>> $entries Entries keyed by id.
		 */
		$entries = apply_filters( self::HOOK, $entries );

		$this->entries = $this->normalize( is_array( $entries ) ? $entries : [] );

		return $this->entries;
	}

	/**
	 * A single entry by id, or null.
	 *
	 * @param string $id Callback id.
	 * @return array<string, mixed>|null
	 */
	public function get( string $id ): ?array {
		$all = $this->all();

		return $all[ $id ] ?? null;
	}

	/**
	 * Whether an id is in the allow-list.
	 *
	 * @param string $id Callback id.
	 * @return bool
	 */
	public function has( string $id ): bool {
		return null !== $this->get( $id );
	}

	/**
	 * Public-facing view of the catalog (no `factory` closure), for the
	 * discovery ability.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function to_public_list(): array {
		$list = [];

		foreach ( $this->all() as $id => $entry ) {
			$list[] = [
				'id'          => $id,
				'label'       => (string) ( $entry['label'] ?? $id ),
				'description' => (string) ( $entry['description'] ?? '' ),
				'params'      => array_values( $entry['params'] ?? [] ),
			];
		}

		return $list;
	}

	/**
	 * Universally-safe built-ins provided by the library.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	private function builtins(): array {
		$constant = static function ( string $fn ): callable {
			return static function () use ( $fn ): callable {
				return $fn;
			};
		};

		return [
			'core/return-true'        => [
				'label'       => __( 'Return true', 'mcp-helpers' ),
				'description' => __( 'Forces the filter to return boolean true.', 'mcp-helpers' ),
				'params'      => [],
				'factory'     => $constant( '__return_true' ),
			],
			'core/return-false'       => [
				'label'       => __( 'Return false', 'mcp-helpers' ),
				'description' => __( 'Forces the filter to return boolean false — the most common conflict fix.', 'mcp-helpers' ),
				'params'      => [],
				'factory'     => $constant( '__return_false' ),
			],
			'core/return-empty-array' => [
				'label'       => __( 'Return empty array', 'mcp-helpers' ),
				'description' => __( 'Forces the filter to return an empty array.', 'mcp-helpers' ),
				'params'      => [],
				'factory'     => $constant( '__return_empty_array' ),
			],
			'core/return-zero'        => [
				'label'       => __( 'Return zero', 'mcp-helpers' ),
				'description' => __( 'Forces the filter to return integer 0.', 'mcp-helpers' ),
				'params'      => [],
				'factory'     => $constant( '__return_zero' ),
			],
			'core/return-null'        => [
				'label'       => __( 'Return null', 'mcp-helpers' ),
				'description' => __( 'Forces the filter to return null.', 'mcp-helpers' ),
				'params'      => [],
				'factory'     => $constant( '__return_null' ),
			],
		];
	}

	/**
	 * Normalizes and drops malformed entries (must have a callable `factory`).
	 *
	 * @param array<string, mixed> $entries Raw entries.
	 * @return array<string, array<string, mixed>>
	 */
	private function normalize( array $entries ): array {
		$normalized = [];

		foreach ( $entries as $id => $entry ) {
			if ( ! is_string( $id ) || '' === $id || ! is_array( $entry ) ) {
				continue;
			}

			if ( ! isset( $entry['factory'] ) || ! is_callable( $entry['factory'] ) ) {
				continue;
			}

			$entry['params']  = isset( $entry['params'] ) && is_array( $entry['params'] ) ? $entry['params'] : [];
			$normalized[ $id ] = $entry;
		}

		return $normalized;
	}
}
