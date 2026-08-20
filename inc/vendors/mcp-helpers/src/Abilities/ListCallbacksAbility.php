<?php
/**
 * Ability: list the approved callbacks (discovery).
 *
 * @package MCPHelpers
 */

namespace MCPHelpers\Abilities;

use MCPHelpers\Catalog\CallbackCatalog;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * `mcp-helpers/list-available-callbacks`
 *
 * The other half of discovery: which approved callbacks may be attached, and
 * what args each expects. An agent composes a filter (from list-available-filters)
 * with one of these when calling create-filter-callback.
 */
class ListCallbacksAbility extends AbstractFilterCallbackAbility {

	/**
	 * @var CallbackCatalog
	 */
	private $catalog;

	/**
	 * @param CallbackCatalog $catalog Approved-callback allow-list.
	 */
	public function __construct( CallbackCatalog $catalog ) {
		$this->catalog = $catalog;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function name(): string {
		return 'mcp-helpers/list-available-callbacks';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function args(): array {
		return [
			'category'            => 'mcp-helpers-filter-callbacks',
			'label'               => esc_html__( 'List available callbacks', 'mcp-helpers' ),
			'description'         => esc_html__( 'Lists the approved callbacks that may be attached to a filter, with the args each expects.', 'mcp-helpers' ),
			'input_schema'        => [
				'type'                 => 'object',
				'default'              => [],
				'properties'           => [],
				'additionalProperties' => false,
			],
			'output_schema'       => [
				'type'       => 'object',
				'properties' => [
					'callbacks' => [ 'type' => 'array', 'items' => [ 'type' => 'object' ] ],
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
	 * @param array<string, mixed> $input Validated input (none).
	 * @return array{callbacks:array<int, array<string, mixed>>}
	 */
	public function execute( array $input ): array {
		unset( $input );

		return [ 'callbacks' => $this->catalog->to_public_list() ];
	}
}
