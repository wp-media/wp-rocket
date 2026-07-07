<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Cache\Abilities;

use WP_Rocket\Engine\Abilities\AbilitiesInterface;
use WP_Rocket\Engine\Tracking\TrackingTrait;

class PurgeUrl implements AbilitiesInterface {
	use TrackingTrait;

	/**
	 * Registers the ability to purge the cache for one or more URLs.
	 */
	public function register(): void {
		if ( ! function_exists( 'wp_register_ability' ) ) {
			return;
		}

		wp_register_ability(
			'wp-rocket/purge-url-cache',
			[
				'label'               => __( 'Purge URL cache', 'rocket' ),
				'description'         => _x(
					'Clears the WP Rocket cache for one or more URLs. Each URL must be a valid URI.
Use this when the user wants to force fresh content for specific pages without purging the entire cache.
Confirm the exact URL or list of URLs with the user before calling. On success, tell the user the page cache has been cleared and the next visit will regenerate it. If a URL was not cached, tell the user that URL was not cached and no action was needed for it.',
					'Ability description',
					'rocket'
					),
				'category'            => 'wp-rocket-cache',
				'input_schema'        => [
					'type'       => 'object',
					'properties' => [
						'url' => [
							'anyOf'       => [
								[
									'type'   => 'string',
									'format' => 'uri',
								],
								[
									'type'     => 'array',
									'items'    => [
										'type'   => 'string',
										'format' => 'uri',
									],
									'minItems' => 1,
								],
							],
							'description' => __( 'A single URL or an array of URLs whose cache should be purged.', 'rocket' ),
						],
					],
					'required'   => [ 'url' ],
				],
				'output_schema'       => [
					'type'       => 'object',
					'properties' => [
						'success' => [
							'type'        => 'boolean',
							'description' => __( 'Indicates whether the url cache was succesfully purged', 'rocket' ),
						],
						'error'   => [
							'type'        => 'string',
							'description' => __( 'Error message if the url cache could not be purged', 'rocket' ),
						],
					],
				],
				'execute_callback'    => [ $this, 'execute' ],
				'permission_callback' => [ $this, 'check_permissions' ],
				'meta'                => [
					'mcp'          => [
						'public' => true,
					],
					'show_in_rest' => true,
					'annotations'  => [
						'readonly'    => false,
						'destructive' => false,
						'idempotent'  => false,
					],
				],
			]
		);
	}

	/**
	 * Checks if the current user has permission to execute this ability.
	 *
	 * @return bool
	 */
	public function check_permissions(): bool {
		return current_user_can( 'rocket_manage_options' );
	}

	/**
	 * Executes the ability to purge the cache for one or more URLs.
	 *
	 * @param array $input The input data containing a single URL string or an array of URL strings to purge.
	 *
	 * @return array
	 */
	public function execute( $input = null ): array {
		$this->track_event( 'MCP Ability Executed', [ 'ability' => 'wp-rocket/purge-url-cache' ] );

		$urls = $input['url'];
		$urls = is_array( $urls ) ? array_unique( $urls ) : $urls;

		rocket_clean_files( $urls );

		return [
			'success' => true,
			'error'   => '',
		];
	}
}
