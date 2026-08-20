<?php
/**
 * Ability: create a filter-callback entry.
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
 * `mcp-helpers/create-filter-callback`
 *
 * Attaches an approved callback (by id, with optional bound args) to a filter.
 */
class CreateFilterCallbackAbility extends AbstractFilterCallbackAbility {

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
		return 'mcp-helpers/create-filter-callback';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function args(): array {
		return [
			'category'            => 'mcp-helpers-filter-callbacks',
			'label'               => esc_html__( 'Create filter callback', 'mcp-helpers' ),
			'description'         => esc_html__( 'Attaches an approved callback to a filter. The callback_id must exist in the approved-callback catalog (see list-available-callbacks).', 'mcp-helpers' ),
			'input_schema'        => [
				'type'                 => 'object',
				'default'              => [],
				'properties'           => [
					'filter_name' => [
						'type'        => 'string',
						'minLength'   => 1,
						'description' => esc_html__( 'The filter (hook) name to attach the callback to.', 'mcp-helpers' ),
					],
					'callback_id' => [
						'type'        => 'string',
						'minLength'   => 1,
						'description' => esc_html__( 'Id of an approved callback from the callback catalog.', 'mcp-helpers' ),
					],
					'args'        => [
						'type'        => 'object',
						'description' => esc_html__( 'Arguments to bind to the callback, keyed by param name.', 'mcp-helpers' ),
					],
				],
				'required'             => [ 'filter_name', 'callback_id' ],
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
					'idempotent'  => false,
				],
			],
		];
	}

	/**
	 * Executes the ability.
	 *
	 * @param array{filter_name:string, callback_id:string, args?:array<string,mixed>} $input Validated input.
	 * @return array{entry:array<string, mixed>}|\WP_Error
	 */
	public function execute( array $input ) {
		$filter_name = (string) ( $input['filter_name'] ?? '' );
		$callback_id = (string) ( $input['callback_id'] ?? '' );
		$args        = isset( $input['args'] ) && is_array( $input['args'] ) ? $input['args'] : [];

		$reference = new CallbackReference( $callback_id, $args );

		// Enforce the allow-list and required args before persisting.
		$valid = $this->resolver->validate( $reference );
		if ( is_wp_error( $valid ) ) {
			return $valid;
		}

		$id = $this->table->insert( $filter_name, $reference->to_json() );

		if ( is_wp_error( $id ) ) {
			return $id;
		}

		return [ 'entry' => $this->shape_entry( $this->table->get( $id ) ) ];
	}
}
