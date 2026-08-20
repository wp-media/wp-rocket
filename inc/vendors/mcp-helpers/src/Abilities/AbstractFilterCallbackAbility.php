<?php
/**
 * Shared plumbing for the filter-callback abilities.
 *
 * @package MCPHelpers
 */

namespace MCPHelpers\Abilities;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Base class each filter-callback ability extends.
 *
 * Concrete abilities are thin adapters: they declare their schema/annotations
 * and delegate work to the table, catalogs, and resolver injected into them.
 */
abstract class AbstractFilterCallbackAbility {

	/**
	 * Capability required to use any of these abilities.
	 */
	protected const CAPABILITY = 'manage_options';

	/**
	 * Registers this ability with the Abilities API.
	 *
	 * @return void
	 */
	public function register(): void {
		wp_register_ability( $this->name(), $this->args() );
	}

	/**
	 * Permission callback shared by every ability.
	 *
	 * @return bool
	 */
	public function check_permissions(): bool {
		return current_user_can( self::CAPABILITY );
	}

	/**
	 * The `<vendor>/ability-name` identifier.
	 *
	 * @return string
	 */
	abstract protected function name(): string;

	/**
	 * The argument array passed to wp_register_ability().
	 *
	 * @return array<string, mixed>
	 */
	abstract protected function args(): array;

	/**
	 * JSON-schema fragment describing a single stored entry (for output schemas).
	 *
	 * @return array<string, mixed>
	 */
	protected function entry_schema(): array {
		return [
			'type'       => 'object',
			'properties' => [
				'id'          => [ 'type' => 'integer' ],
				'filter_name' => [ 'type' => 'string' ],
				'callback'    => [
					'type'        => 'object',
					'description' => 'The stored callback reference.',
					'properties'  => [
						'id'   => [ 'type' => 'string' ],
						'args' => [ 'type' => 'object' ],
					],
				],
			],
		];
	}

	/**
	 * Shapes a raw DB row for output, decoding the JSON callback reference into
	 * a structured object.
	 *
	 * @param array{id:int, filter_name:string, callback:string} $row Raw row.
	 * @return array{id:int, filter_name:string, callback:array{id:string, args:object}}
	 */
	protected function shape_entry( array $row ): array {
		$decoded = json_decode( $row['callback'], true );

		$callback = [
			'id'   => is_array( $decoded ) ? (string) ( $decoded['id'] ?? '' ) : '',
			'args' => (object) ( is_array( $decoded ) && isset( $decoded['args'] ) && is_array( $decoded['args'] ) ? $decoded['args'] : [] ),
		];

		return [
			'id'          => $row['id'],
			'filter_name' => $row['filter_name'],
			'callback'    => $callback,
		];
	}
}
