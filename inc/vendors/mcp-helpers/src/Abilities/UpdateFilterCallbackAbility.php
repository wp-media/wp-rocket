<?php
/**
 * Ability: update a filter-callback entry.
 *
 * @package MCPHelpers
 */

namespace MCPHelpers\Abilities;

use MCPHelpers\Callback\CallbackReference;
use MCPHelpers\Callback\CallbackResolver;
use MCPHelpers\Table\FilterCallbackTable;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * `mcp-helpers/update-filter-callback`
 *
 * Changes the filter name and/or the approved callback (and its args) of an
 * existing entry.
 */
class UpdateFilterCallbackAbility extends AbstractFilterCallbackAbility {

	/**
	 * @var FilterCallbackTable
	 */
	private $table;

	/**
	 * @var CallbackResolver
	 */
	private $resolver;

	/**
	 * @param FilterCallbackTable $table    Table gateway.
	 * @param CallbackResolver    $resolver Allow-list-aware resolver.
	 */
	public function __construct( FilterCallbackTable $table, CallbackResolver $resolver ) {
		$this->table    = $table;
		$this->resolver = $resolver;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function name(): string {
		return 'mcp-helpers/update-filter-callback';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function args(): array {
		return [
			'category'            => 'mcp-helpers-filter-callbacks',
			'label'               => esc_html__( 'Update filter callback', 'mcp-helpers' ),
			'description'         => esc_html__( 'Updates the filter name and/or the approved callback (with args) of an existing entry.', 'mcp-helpers' ),
			'input_schema'        => [
				'type'                 => 'object',
				'default'              => [],
				'properties'           => [
					'id'          => [
						'type'        => 'integer',
						'minimum'     => 1,
						'description' => esc_html__( 'Id of the entry to update.', 'mcp-helpers' ),
					],
					'filter_name' => [
						'type'        => 'string',
						'minLength'   => 1,
						'description' => esc_html__( 'New filter (hook) name.', 'mcp-helpers' ),
					],
					'callback_id' => [
						'type'        => 'string',
						'minLength'   => 1,
						'description' => esc_html__( 'New approved-callback id. Provide with (optional) args to change the callback.', 'mcp-helpers' ),
					],
					'args'        => [
						'type'        => 'object',
						'description' => esc_html__( 'Arguments to bind to the new callback. Only used when callback_id is provided.', 'mcp-helpers' ),
					],
				],
				'required'             => [ 'id' ],
				'additionalProperties' => false,
			],
			'output_schema'       => [
				'type'       => 'object',
				'properties' => [
					'entry' => $this->entry_schema(),
				],
			],
			'permission_callback' => [ $this, 'check_permissions' ],
			'execute_callback'    => [ $this, 'execute' ],
			'meta'                => [
				'show_in_rest' => true,
				'mcp'          => [ 'public' => true ],
				'annotations'  => [
					'readonly'    => false,
					'destructive' => false,
					'idempotent'  => true,
				],
			],
		];
	}

	/**
	 * Executes the ability.
	 *
	 * @param array{id:int, filter_name?:string, callback_id?:string, args?:array<string,mixed>} $input Validated input.
	 * @return array{entry:array<string, mixed>}|\WP_Error
	 */
	public function execute( array $input ) {
		$id = absint( $input['id'] ?? 0 );

		$fields = [];

		if ( array_key_exists( 'filter_name', $input ) ) {
			$fields['filter_name'] = (string) $input['filter_name'];
		}

		if ( array_key_exists( 'callback_id', $input ) ) {
			$args      = isset( $input['args'] ) && is_array( $input['args'] ) ? $input['args'] : [];
			$reference = new CallbackReference( (string) $input['callback_id'], $args );

			$valid = $this->resolver->validate( $reference );
			if ( is_wp_error( $valid ) ) {
				return $valid;
			}

			$fields['callback'] = $reference->to_json();
		}

		$result = $this->table->update( $id, $fields );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return [ 'entry' => $this->shape_entry( $this->table->get( $id ) ) ];
	}
}
