<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Cache\Abilities;

use WP_Rocket\Engine\Abilities\AbilitiesInterface;
use WP_Rocket\Engine\Tracking\TrackingTrait;

class ClearUrlCache implements AbilitiesInterface {
	use TrackingTrait;

	/**
	 * Registers the ability to purge the cache for one or more URLs.
	 */
	public function register(): void {
		if ( ! function_exists( 'wp_register_ability' ) ) {
			return;
		}

		wp_register_ability(
			'wp-rocket/clear-url-cache',
			[
				'label'               => __( 'Clear URL cache', 'rocket' ),
				'description'         => _x(
					'Clears WP Rocket cache for specific URL(s), not a full purge. Before calling: list the URL(s), ask "Confirm you want to clear cache for this url?" (use "these" if plural), and wait for a yes in the same turn. On success (success: true, error empty): say the cache was cleared and will regenerate on next visit. On failure: error is an object of url => reason; report each failed URL with its own reason, not a generic message.',
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
							'type'                 => 'object',
							'additionalProperties' => [
								'type' => 'string',
							],
							'description'          => __( 'Object keyed by URL for each URL whose cache could not be purged, with the reason as the value.', 'rocket' ),
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
		$this->track_event(
			'MCP Ability Executed',
			[
				'ability' => 'wp-rocket/clear-url-cache',
				'context' => 'wp_plugin_mcp',
			]
		);

		$urls = $input['url'];
		$urls = is_array( $urls ) ? array_unique( $urls ) : [ $urls ];

		$valid_urls = [];
		$error      = [];
		$site_host  = wp_parse_url( home_url(), PHP_URL_HOST );

		foreach ( $urls as $url ) {
			// Check if url format is valid.
			if ( false === filter_var( $url, FILTER_VALIDATE_URL ) ) {
				$error[ $url ] = __( 'URL format is invalid.', 'rocket' );
				continue;
			}

			// Check if url belongs to site.
			if ( wp_parse_url( $url, PHP_URL_HOST ) !== $site_host ) {
				$error[ $url ] = __( 'URL does not belong to this site.', 'rocket' );
				continue;
			}

			// Check if url is from admin.
			if ( strpos( $url, admin_url() ) === 0 ) {
				$error[ $url ] = __( 'URL is an admin page.', 'rocket' );
				continue;
			}

			$valid_urls[] = $url;
		}

		$cleaned_urls = ! empty( $valid_urls ) ? rocket_clean_files( $valid_urls ) : [];

		foreach ( $cleaned_urls as $url => $cleared ) {
			if ( ! $cleared ) {
				$error[ $url ] = __( 'No cache found for this URL, or a file permission issue prevented cache clearing.', 'rocket' );
			}
		}

		return [
			'success' => empty( $error ),
			'error'   => $error,
		];
	}
}
