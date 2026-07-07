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
Confirmation of the exact url or list of urls is required before calling. 
First show the url to be cleared in a list style. Then ask: `Confirm you want to clear cache for this url?` if more than one url, use `these` and wait for a clear yes or no. 
Only call this ability after the user gives an affirmative answer in the same turn.
On success (success is true and error is empty), tell the user the page cache has been cleared and the next visit will regenerate it. If success is false, tell the user the cache could not be purged for the URL(s) listed in error, and that the likely reason is either no cache existed for that URL or a file permission issue prevented deletion; list all affected URLs if there is more than one.',
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
							'type'        => 'array',
							'items'       => [
								'type' => 'string',
							],
							'description' => __( 'URLs whose cache could not be purged successfully.', 'rocket' ),
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

		$result = rocket_clean_files( $urls );
		$error  = array_keys( array_filter( $result, fn( $cleared ) => ! $cleared ) );

		return [
			'success' => empty( $error ),
			'error'   => $error,
		];
	}
}
