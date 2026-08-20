<?php
/**
 * Ability: list the filters the plugin exposes (discovery).
 *
 * @package MCPHelpers
 */

namespace MCPHelpers\Abilities;

use MCPHelpers\Catalog\FilterCatalog;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * `mcp-helpers/list-available-filters`
 *
 * The discovery side: tells an agent which filters exist, what they do, their
 * signature, and which approved callbacks are compatible — so it can pick the
 * right filter before calling create-filter-callback.
 */
class ListFiltersAbility extends AbstractFilterCallbackAbility {

	/**
	 * @var FilterCatalog
	 */
	private $catalog;

	/**
	 * @param FilterCatalog $catalog Filter catalog.
	 */
	public function __construct( FilterCatalog $catalog ) {
		$this->catalog = $catalog;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function name(): string {
		return 'mcp-helpers/list-available-filters';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function args(): array {
		return [
			'category'            => 'mcp-helpers-filter-callbacks',
			'label'               => esc_html__( 'List available filters', 'mcp-helpers' ),
			'description'         => esc_html__( 'Searches the filters the plugin exposes. Use this first to find which filter to target, then create-filter-callback to attach a callback.', 'mcp-helpers' ),
			'input_schema'        => [
				'type'                 => 'object',
				'default'              => [],
				'properties'           => [
					'search'   => [
						'type'        => 'string',
						'description' => esc_html__( 'Free-text search across name, label, description and keywords.', 'mcp-helpers' ),
					],
					'category' => [
						'type'        => 'string',
						'description' => esc_html__( 'Restrict to a single category.', 'mcp-helpers' ),
					],
					'type'     => [
						'type'        => 'string',
						'enum'        => [ 'filter', 'action' ],
						'description' => esc_html__( 'Restrict to filters or actions.', 'mcp-helpers' ),
					],
					'prefix'   => [
						'type'        => 'string',
						'description' => esc_html__( 'Restrict to hook names starting with this prefix (e.g. "rocket_").', 'mcp-helpers' ),
					],
					'page'     => [
						'type'    => 'integer',
						'minimum' => 1,
						'default' => 1,
					],
					'per_page' => [
						'type'    => 'integer',
						'minimum' => 1,
						'maximum' => 100,
						'default' => 20,
					],
				],
				'additionalProperties' => false,
			],
			'output_schema'       => [
				'type'       => 'object',
				'properties' => [
					'filters'    => [ 'type' => 'array', 'items' => [ 'type' => 'object' ] ],
					'total'      => [ 'type' => 'integer' ],
					'categories' => [ 'type' => 'array', 'items' => [ 'type' => 'string' ] ],
				],
			],
			'permission_callback' => [ $this, 'check_permissions' ],
			'execute_callback'    => [ $this, 'execute' ],
			'meta'                => [
				'show_in_rest' => true,
				'mcp'          => [ 'public' => true ],
				'annotations'  => [
					'readonly'    => true,
					'destructive' => false,
					'idempotent'  => true,
				],
			],
		];
	}

	/**
	 * Executes the ability.
	 *
	 * @param array{search?:string, category?:string, type?:string, prefix?:string, page?:int, per_page?:int} $input Validated input.
	 * @return array{filters:array<int, array<string, mixed>>, total:int, categories:array<int, string>}
	 */
	public function execute( array $input ): array {
		$matches = $this->catalog->search(
			[
				'search'   => (string) ( $input['search'] ?? '' ),
				'category' => (string) ( $input['category'] ?? '' ),
				'type'     => (string) ( $input['type'] ?? '' ),
				'prefix'   => (string) ( $input['prefix'] ?? '' ),
			]
		);

		$total    = count( $matches );
		$page     = max( 1, absint( $input['page'] ?? 1 ) );
		$per_page = min( 100, max( 1, absint( $input['per_page'] ?? 20 ) ) );
		$offset   = ( $page - 1 ) * $per_page;

		return [
			'filters'    => array_slice( $matches, $offset, $per_page ),
			'total'      => $total,
			'categories' => $this->catalog->categories(),
		];
	}
}
