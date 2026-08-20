<?php
/**
 * Ability: delete a filter-callback entry.
 *
 * @package MCPHelpers
 */

namespace MCPHelpers\Abilities;

use MCPHelpers\Table\FilterCallbackTable;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * `mcp-helpers/delete-filter-callback`
 *
 * Permanently removes an entry by id.
 */
class DeleteFilterCallbackAbility extends AbstractFilterCallbackAbility {

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
		return 'mcp-helpers/delete-filter-callback';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function args(): array {
		return [
			'category'            => 'mcp-helpers-filter-callbacks',
			'label'               => esc_html__( 'Delete filter callback', 'mcp-helpers' ),
			'description'         => esc_html__( 'Permanently deletes a filter-to-callback mapping by id.', 'mcp-helpers' ),
			'input_schema'        => [
				'type'                 => 'object',
				'default'              => [],
				'properties'           => [
					'id' => [
						'type'        => 'integer',
						'minimum'     => 1,
						'description' => esc_html__( 'Id of the entry to delete.', 'mcp-helpers' ),
					],
				],
				'required'             => [ 'id' ],
				'additionalProperties' => false,
			],
			'output_schema'       => [
				'type'       => 'object',
				'properties' => [
					'deleted' => [ 'type' => 'boolean' ],
					'id'      => [ 'type' => 'integer' ],
				],
			],
			'permission_callback' => [ $this, 'check_permissions' ],
			'execute_callback'    => [ $this, 'execute' ],
			'meta'                => [
				'show_in_rest' => true,
				'mcp'          => [ 'public' => true ],
				'annotations'  => [
					'readonly'    => false,
					'destructive' => true,
					'idempotent'  => true,
				],
			],
		];
	}

	/**
	 * Executes the ability.
	 *
	 * @param array{id:int} $input Validated input.
	 * @return array{deleted:bool, id:int}|\WP_Error
	 */
	public function execute( array $input ) {
		$id = absint( $input['id'] ?? 0 );

		$result = $this->table->delete( $id );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return [
			'deleted' => true,
			'id'      => $id,
		];
	}
}
