<?php
/**
 * Reads the filter-callback table and wires each callback onto its filter.
 *
 * @package MCPHelpers
 */

namespace MCPHelpers;

use MCPHelpers\Callback\CallbackReference;
use MCPHelpers\Callback\CallbackResolver;
use MCPHelpers\Catalog\FilterCatalog;
use MCPHelpers\Table\FilterCallbackTable;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Loads every stored "filter name -> callback reference" mapping, resolves each
 * reference into a real callable via the approved-callback allow-list, and
 * registers it with WordPress. Later `apply_filters( $filter_name, ... )` calls
 * then run the resolved callback.
 */
class FilterApplier {

	/**
	 * Table gateway.
	 *
	 * @var FilterCallbackTable
	 */
	private $table;

	/**
	 * Resolves references into callables.
	 *
	 * @var CallbackResolver
	 */
	private $resolver;

	/**
	 * Filter catalog, used to derive how many args each callback should accept.
	 *
	 * @var FilterCatalog
	 */
	private $filters;

	/**
	 * Hook priority for every registered callback.
	 *
	 * @var int
	 */
	private $priority;

	/**
	 * @param FilterCallbackTable $table    Table gateway.
	 * @param CallbackResolver    $resolver Reference resolver (allow-list aware).
	 * @param FilterCatalog       $filters  Filter catalog.
	 * @param int                 $priority Hook priority for every registered callback.
	 */
	public function __construct(
		FilterCallbackTable $table,
		CallbackResolver $resolver,
		FilterCatalog $filters,
		int $priority = 10
	) {
		$this->table    = $table;
		$this->resolver = $resolver;
		$this->filters  = $filters;
		$this->priority = $priority;
	}

	/**
	 * Reads all entries and registers each resolved callback on its filter.
	 *
	 * Entries whose reference is malformed or points at a callback no longer in
	 * the allow-list are skipped rather than fatally erroring, so a stale row
	 * cannot take the site down.
	 *
	 * @return int Number of callbacks actually registered.
	 */
	public function apply(): int {
		$registered = 0;

		foreach ( $this->table->all() as $entry ) {
			$reference = CallbackReference::from_json( $entry['callback'] );

			if ( is_wp_error( $reference ) ) {
				continue;
			}

			$callback = $this->resolver->resolve( $reference );

			if ( is_wp_error( $callback ) ) {
				continue;
			}

			add_filter(
				$entry['filter_name'],
				$callback,
				$this->priority,
				$this->accepted_args_for( $entry['filter_name'] )
			);

			++$registered;
		}

		return $registered;
	}

	/**
	 * Number of args to pass to the callback, derived from the filter catalog
	 * entry's declared params (defaulting to 1 when unknown).
	 *
	 * @param string $filter_name Filter name.
	 * @return int
	 */
	private function accepted_args_for( string $filter_name ): int {
		$entry = $this->filters->get( $filter_name );

		if ( null === $entry ) {
			return 1;
		}

		return max( 1, count( $entry['params'] ) );
	}
}
