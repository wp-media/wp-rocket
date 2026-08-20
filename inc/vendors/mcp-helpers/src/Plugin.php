<?php
/**
 * Wires the library's pieces onto WordPress hooks.
 *
 * @package MCPHelpers
 */

namespace MCPHelpers;

use MCPHelpers\Abilities\CreateFilterCallbackAbility;
use MCPHelpers\Abilities\DeleteFilterCallbackAbility;
use MCPHelpers\Abilities\ListCallbacksAbility;
use MCPHelpers\Abilities\ListFiltersAbility;
use MCPHelpers\Abilities\ReadFilterCallbacksAbility;
use MCPHelpers\Abilities\UpdateFilterCallbackAbility;
use MCPHelpers\Callback\CallbackResolver;
use MCPHelpers\Catalog\CallbackCatalog;
use MCPHelpers\Catalog\FilterCatalog;
use MCPHelpers\Table\FilterCallbackTable;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Central wiring for the library.
 */
class Plugin {

	/**
	 * Ability category slug.
	 */
	private const CATEGORY = 'mcp-helpers-filter-callbacks';

	/**
	 * @var FilterCallbackTable
	 */
	private $table;

	/**
	 * @var FilterCatalog
	 */
	private $filter_catalog;

	/**
	 * @var CallbackCatalog
	 */
	private $callback_catalog;

	/**
	 * @var CallbackResolver
	 */
	private $resolver;

	public function __construct() {
		$this->table            = new FilterCallbackTable();
		$this->filter_catalog   = new FilterCatalog();
		$this->callback_catalog = new CallbackCatalog();
		$this->resolver         = new CallbackResolver( $this->callback_catalog );
	}

	/**
	 * Registers all hooks.
	 *
	 * @return void
	 */
	public function init(): void {
		// Register the ability category, then the abilities (WP 6.9+ only).
		add_action( 'wp_abilities_api_categories_init', [ $this, 'register_category' ] );
		add_action( 'wp_abilities_api_init', [ $this, 'register_abilities' ] );

		/**
		 * Filters the hook on which stored callbacks are wired onto their filters.
		 *
		 * @param string $hook Hook name. Default 'init'.
		 */
		$apply_hook = (string) apply_filters( 'mcp_helpers_apply_hook', 'init' );

		add_action(
			$apply_hook,
			function () {
				( new FilterApplier( $this->table, $this->resolver, $this->filter_catalog ) )->apply();
			},
			// Priority 1 so callbacks are attached before most consumers run.
			1
		);
	}

	/**
	 * Registers the ability category.
	 *
	 * @return void
	 */
	public function register_category(): void {
		if ( ! function_exists( 'wp_register_ability_category' ) ) {
			return;
		}

		wp_register_ability_category(
			self::CATEGORY,
			[
				'label'       => esc_html__( 'Filter callbacks', 'mcp-helpers' ),
				'description' => esc_html__( 'Discover the plugin\'s filters and manage stored filter-to-callback mappings.', 'mcp-helpers' ),
			]
		);
	}

	/**
	 * Registers the abilities: two discovery + four CRUD.
	 *
	 * @return void
	 */
	public function register_abilities(): void {
		if ( ! function_exists( 'wp_register_ability' ) ) {
			return;
		}

		$abilities = [
			// Discovery.
			new ListFiltersAbility( $this->filter_catalog ),
			new ListCallbacksAbility( $this->callback_catalog ),
			// CRUD.
			new ReadFilterCallbacksAbility( $this->table ),
			new CreateFilterCallbackAbility( $this->table, $this->resolver ),
			new UpdateFilterCallbackAbility( $this->table, $this->resolver ),
			new DeleteFilterCallbackAbility( $this->table ),
		];

		foreach ( $abilities as $ability ) {
			$ability->register();
		}
	}

	/**
	 * Exposes the table gateway (e.g. for the host plugin's activation hook).
	 *
	 * @return FilterCallbackTable
	 */
	public function table(): FilterCallbackTable {
		return $this->table;
	}
}
