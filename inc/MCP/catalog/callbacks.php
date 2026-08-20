<?php
/**
 * WP Rocket approved-callback catalog for MCP Helpers.
 *
 * Contributes WP Rocket's vetted callbacks to the allow-list. The library also
 * ships universally-safe core built-ins (core/return-true, core/return-false,
 * core/return-empty-array, core/return-zero, core/return-null).
 *
 * @package WP_Rocket\MCP
 */

use WP_Rocket\MCP\Callbacks;

defined( 'ABSPATH' ) || exit;

add_filter(
	'mcp_helpers_callback_catalog',
	static function ( array $callbacks ): array {
		$callbacks['rocket/append-to-list'] = [
			'label'       => 'Add to a list',
			'description' => 'Append value(s) to an array-returning filter (exclusions, rejections). '
						. 'Use to exclude a URL, cookie, JS/CSS handle, query string, etc.',
			'params'      => [
				[
					'name'        => 'values',
					'type'        => 'string[]',
					'required'    => true,
					'description' => 'One or more values to add to the list.',
				],
			],
			'factory'     => [ Callbacks::class, 'append_to_list' ],
		];

		$callbacks['rocket/remove-from-list'] = [
			'label'       => 'Remove from a list',
			'description' => 'Remove matching value(s) from an array-returning filter. '
						. 'Use to undo or loosen an exclusion.',
			'params'      => [
				[
					'name'        => 'values',
					'type'        => 'string[]',
					'required'    => true,
					'description' => 'One or more values to remove from the list.',
				],
			],
			'factory'     => [ Callbacks::class, 'remove_from_list' ],
		];

		$callbacks['rocket/return-int'] = [
			'label'       => 'Return a fixed integer',
			'description' => 'Force an integer-valued filter to return a specific number '
						. '(e.g. a timeout or batch size).',
			'params'      => [
				[
					'name'        => 'value',
					'type'        => 'int',
					'required'    => true,
					'description' => 'The integer to return.',
				],
			],
			'factory'     => [ Callbacks::class, 'return_int' ],
		];

		return $callbacks;
	}
);
