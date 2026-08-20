<?php
/**
 * Ability: read filter-callback entries.
 *
 * @package MCPHelpers
 */

namespace MCPHelpers\Abilities;

use MCPHelpers\Table\FilterCallbackTable;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * `mcp-helpers/read-filter-callbacks`
 *
 * Returns a single entry (when `id` is given) or all entries.
 */
class ReadFilterCallbacksAbility extends AbstractFilterCallbackAbility {

	/**
	 * @var FilterCallbackTable
	 */
	private $table;

	/**
	 * @param FilterCallbackTable $table Table gateway.
	 */
	public function __construct( FilterCallbackTable $table ) {
		$this->table = $table;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function name(): string {
		return 'mcp-helpers/read-filter-callbacks';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function args(): array {
		return [
			'category'            => 'mcp-helpers-filter-callbacks',
			'label'               => esc_html__( 'Read filter callbacks', 'mcp-helpers' ),
			'description'         => esc_html__( 'Lists stored filter-to-callback mappings, or a single one by id.', 'mcp-helpers' ),
			'input_schema'        => [
				'type'                 => 'object',
				'default'              => [],
				'properties'           => [
					'id' => [
						'type'        => 'integer',
						'minimum'     => 1,
						'description' => esc_html__( 'Optional entry id. Omit to list all entries.', 'mcp-helpers' ),
					],
				],
				'additionalProperties' => false,
			],
			'output_schema'       => [
				'type'       => 'object',
				'properties' => [
					'entries' => [
						'type'  => 'array',
						'items' => $this->entry_schema(),
					],
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
	 * @param array{id?:int} $input Validated input.
	 * @return array{entries:array<int, array<string, mixed>>}
	 */
	public function execute( array $input ): array {
		if ( isset( $input['id'] ) ) {
			$entry = $this->table->get( absint( $input['id'] ) );

			return [ 'entries' => $entry ? [ $this->shape_entry( $entry ) ] : [] ];
		}

		return [ 'entries' => array_map( [ $this, 'shape_entry' ], $this->table->all() ) ];
	}
}
