<?php
/**
 * Catalog of filters the implementer plugin exposes.
 *
 * @package MCPHelpers
 */

namespace MCPHelpers\Catalog;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The discoverable surface: which filters/actions the plugin offers, described
 * for an agent (what each does, when to use it, its signature, and which
 * approved callbacks are compatible).
 *
 * The implementer plugin populates it through the `mcp_helpers_filter_catalog`
 * filter. The library only exposes and searches it.
 */
class FilterCatalog {

	/**
	 * Filter plugins hook to contribute their catalog entries.
	 */
	public const HOOK = 'mcp_helpers_filter_catalog';

	/**
	 * Memoized, normalized entries keyed by filter name.
	 *
	 * @var array<string, array<string, mixed>>|null
	 */
	private $entries = null;

	/**
	 * All catalog entries keyed by filter name.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public function all(): array {
		if ( null !== $this->entries ) {
			return $this->entries;
		}

		/**
		 * Filters the catalog of exposed filters.
		 *
		 * @param array<int, array<string, mixed>> $entries List of filter descriptors.
		 */
		$entries = apply_filters( self::HOOK, [] );

		$this->entries = $this->normalize( is_array( $entries ) ? $entries : [] );

		return $this->entries;
	}

	/**
	 * A single entry by filter name, or null.
	 *
	 * @param string $name Filter name.
	 * @return array<string, mixed>|null
	 */
	public function get( string $name ): ?array {
		$all = $this->all();

		return $all[ $name ] ?? null;
	}

	/**
	 * Searches the catalog.
	 *
	 * @param array{search?:string, category?:string, type?:string, prefix?:string} $query Filters.
	 * @return array<int, array<string, mixed>> Matching entries (list form).
	 */
	public function search( array $query = [] ): array {
		$search   = isset( $query['search'] ) ? strtolower( trim( (string) $query['search'] ) ) : '';
		$category = isset( $query['category'] ) ? (string) $query['category'] : '';
		$type     = isset( $query['type'] ) ? (string) $query['type'] : '';
		$prefix   = isset( $query['prefix'] ) ? (string) $query['prefix'] : '';

		$matches = [];

		foreach ( $this->all() as $entry ) {
			if ( '' !== $category && ( $entry['category'] ?? '' ) !== $category ) {
				continue;
			}

			if ( '' !== $type && ( $entry['type'] ?? '' ) !== $type ) {
				continue;
			}

			if ( '' !== $prefix && 0 !== strpos( (string) $entry['name'], $prefix ) ) {
				continue;
			}

			if ( '' !== $search && ! $this->matches_search( $entry, $search ) ) {
				continue;
			}

			$matches[] = $entry;
		}

		return $matches;
	}

	/**
	 * The distinct categories present in the catalog.
	 *
	 * @return array<int, string>
	 */
	public function categories(): array {
		$categories = [];

		foreach ( $this->all() as $entry ) {
			$category = (string) ( $entry['category'] ?? '' );
			if ( '' !== $category ) {
				$categories[ $category ] = true;
			}
		}

		return array_keys( $categories );
	}

	/**
	 * Whether an entry matches a free-text search across its salient fields.
	 *
	 * @param array<string, mixed> $entry  Entry.
	 * @param string               $search Lowercased search term.
	 * @return bool
	 */
	private function matches_search( array $entry, string $search ): bool {
		$haystack = strtolower(
			implode(
				' ',
				[
					(string) ( $entry['name'] ?? '' ),
					(string) ( $entry['label'] ?? '' ),
					(string) ( $entry['description'] ?? '' ),
					(string) ( $entry['category'] ?? '' ),
					implode( ' ', $entry['keywords'] ?? [] ),
				]
			)
		);

		foreach ( preg_split( '/\s+/', $search ) ?: [] as $term ) {
			if ( '' !== $term && false === strpos( $haystack, $term ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Normalizes entries, dropping any without a name and keying by name.
	 *
	 * @param array<int, array<string, mixed>> $entries Raw entries.
	 * @return array<string, array<string, mixed>>
	 */
	private function normalize( array $entries ): array {
		$normalized = [];

		foreach ( $entries as $entry ) {
			if ( ! is_array( $entry ) ) {
				continue;
			}

			$name = isset( $entry['name'] ) ? trim( (string) $entry['name'] ) : '';
			if ( '' === $name ) {
				continue;
			}

			$normalized[ $name ] = [
				'name'                 => $name,
				'type'                 => (string) ( $entry['type'] ?? 'filter' ),
				'label'                => (string) ( $entry['label'] ?? $name ),
				'description'          => (string) ( $entry['description'] ?? '' ),
				'category'             => (string) ( $entry['category'] ?? '' ),
				'keywords'             => array_values( array_map( 'strval', $entry['keywords'] ?? [] ) ),
				'params'               => array_values( $entry['params'] ?? [] ),
				'returns'              => $entry['returns'] ?? null,
				'since'                => (string) ( $entry['since'] ?? '' ),
				'doc_url'              => (string) ( $entry['doc_url'] ?? '' ),
				'example'              => (string) ( $entry['example'] ?? '' ),
				'compatible_callbacks' => array_values( array_map( 'strval', $entry['compatible_callbacks'] ?? [] ) ),
			];
		}

		return $normalized;
	}
}
