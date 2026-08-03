<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Cache\Abilities;

use WP_Rocket\Engine\Abilities\AbilitiesInterface;
use WP_Rocket\Engine\Tracking\TrackingTrait;

class ClearWebsiteCache implements AbilitiesInterface {
	use TrackingTrait;

	/**
	 * Registers the ability to clear website cache.
	 */
	public function register(): void {
		if ( ! function_exists( 'wp_register_ability' ) ) {
			return;
		}

		wp_register_ability(
			'wp-rocket/clear-website-cache',
			[
				'label'               => __( 'Clear website cache', 'rocket' ),
				'description'         => _x(
					'Clears the full WP Rocket cache for the entire website. Use this when the user asks to clear, purge, or refresh all cached pages for the site. This is a site-wide destructive cache action. Do not use it for a single page; use clear-url-cache instead. Confirmation is required before calling',
					'Ability description',
					'rocket'
					),
				'category'            => 'wp-rocket-cache',
				'input_schema'        => [],
				'output_schema'       => [
					'type'       => 'object',
					'properties' => [
						'success' => [
							'type'        => 'boolean',
							'description' => __( 'Indicates whether the website cache was successfully cleared', 'rocket' ),
						],
						'error'   => [
							'type'        => 'string',
							'description' => __( 'Reason the cache could not be cleared, empty on success.', 'rocket' ),
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
	 * Executes the ability to clear website cache.
	 *
	 * @return array
	 */
	public function execute(): array {
		$this->track_event(
			'MCP Ability Executed',
			[
				'ability' => 'wp-rocket/clear-website-cache',
			]
		);

		if ( rocket_is_importing() ) {
			return [
				'success' => false,
				'error'   => __( 'Unable to clear cache: a content import is currently in progress.', 'rocket' ),
			];
		}

		$result = rocket_clean_domain();

		if ( false === $result ) {
			return [
				'success' => false,
				'error'   => __( 'Unable to clear cache: no cacheable URLs were found for this site.', 'rocket' ),
			];
		}

		return [
			'success' => true,
			'error'   => '',
		];
	}
}
