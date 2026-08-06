<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Preload\Abilities;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Abilities\AbilitiesInterface;
use WP_Rocket\Engine\Preload\Database\Queries\Cache as CacheQuery;
use WP_Rocket\Engine\Tracking\TrackingTrait;

class CheckCacheHealth implements AbilitiesInterface {
	use TrackingTrait;

	/**
	 * Options data instance.
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Preload cache query instance.
	 *
	 * @var CacheQuery
	 */
	private $query;

	/**
	 * Constructor.
	 *
	 * @param Options_Data $options Options data instance.
	 * @param CacheQuery   $query   Preload cache query instance.
	 */
	public function __construct( Options_Data $options, CacheQuery $query ) {
		$this->options = $options;
		$this->query   = $query;
	}

	/**
	 * Registers the ability to get a sitewide Preload cache health summary.
	 *
	 * @return void
	 */
	public function register(): void {
		if ( ! function_exists( 'wp_register_ability' ) ) {
			return;
		}

		wp_register_ability(
			'wp-rocket/get-cache-health',
			[
				'label'               => __( 'Get WP Rocket cache health', 'rocket' ),
				'description'         => _x(
					'Returns sitewide Preload queue counts (pending, in-progress, completed, failed) and an estimated time remaining until preloading finishes.
The estimate is always a projection (is_estimate is always true), based on the configured maximum batch size and cron interval, not a live measurement. Use this to answer "is my cache warm" or "how long until preload finishes".
If tracking_enabled is false, tell the user Preload tracking is disabled rather than presenting a stale or empty estimate as current.',
					'Ability description',
					'rocket'
					),
				'category'            => 'wp-rocket-cache',
				'output_schema'       => [
					'type'       => 'object',
					'properties' => [
						'counts'           => [
							'type'       => 'object',
							'properties' => [
								'pending'     => [ 'type' => 'integer' ],
								'in-progress' => [ 'type' => 'integer' ],
								'completed'   => [ 'type' => 'integer' ],
								'failed'      => [ 'type' => 'integer' ],
							],
						],
						'tracking_enabled' => [
							'type' => 'boolean',
						],
						'estimate'         => [
							'type'       => 'object',
							'properties' => [
								'is_estimate'           => [ 'type' => 'boolean' ],
								'pending_urls'          => [ 'type' => 'integer' ],
								'batch_size'            => [ 'type' => 'integer' ],
								'cron_interval_seconds' => [ 'type' => 'integer' ],
								'mobile_cache_active'   => [ 'type' => 'boolean' ],
								'estimated_seconds_remaining' => [ 'type' => [ 'integer', 'null' ] ],
								'estimated_completion_human' => [ 'type' => [ 'string', 'null' ] ],
								'method'                => [ 'type' => 'string' ],
							],
						],
					],
				],
				'execute_callback'    => [ $this, 'execute' ],
				'permission_callback' => [ $this, 'check_permissions' ],
				'meta'                => [
					'show_in_rest' => true,
					'mcp'          => [
						'public' => true,
					],
					'annotations'  => [
						'readonly'    => true,
						'destructive' => false,
						'idempotent'  => true,
					],
				],
			]
		);
	}

	/**
	 * Checks if the current user has permission to get the cache health summary.
	 *
	 * @return bool
	 */
	public function check_permissions(): bool {
		return current_user_can( 'rocket_manage_options' );
	}

	/**
	 * Executes the ability to get a sitewide Preload cache health summary.
	 *
	 * @return array
	 */
	public function execute(): array {
		$this->track_event(
			'MCP Ability Executed',
			[
				'ability' => 'wp-rocket/get-cache-health',
				'context' => 'wp_plugin_mcp',
			]
		);

		$counts           = $this->query->get_status_counts();
		$tracking_enabled = (bool) $this->options->get( 'manual_preload', 0 );
		$mobile_active    = $this->options->get( 'do_caching_mobile_files', 0 ) && $this->options->get( 'cache_mobile', 0 );
		$pending          = $counts['pending'] ?? 0;

		/**
		 * Filters the maximum number of preload jobs processed per cron tick.
		 *
		 * This is the same filter used by the Preload cron job itself to cap its per-tick
		 * batch size; it is not a live measurement of actual throughput.
		 */
		$batch_size = (int) wpm_apply_filters_typed( 'integer', 'rocket_preload_cache_pending_jobs_cron_rows_count', 45 );

		/**
		 * Filters the interval, in seconds, between Preload cron runs.
		 */
		$cron_interval = (int) wpm_apply_filters_typed( 'integer', 'rocket_preload_pending_jobs_cron_interval', MINUTE_IN_SECONDS );

		$estimate = [
			'is_estimate'                 => true,
			'pending_urls'                => $pending,
			'batch_size'                  => $batch_size,
			'cron_interval_seconds'       => $cron_interval,
			'mobile_cache_active'         => $mobile_active,
			'estimated_seconds_remaining' => null,
			'estimated_completion_human'  => null,
			'method'                      => '',
		];

		if ( ! $tracking_enabled ) {
			$estimate['method'] = __( 'Estimate unavailable: Preload tracking is disabled. Enable "Preload Cache" in Settings > WP Rocket > Preload to get a completion estimate.', 'rocket' );

			return [
				'counts'           => $counts,
				'tracking_enabled' => false,
				'estimate'         => $estimate,
			];
		}

		if ( $pending <= 0 ) {
			$estimate['method'] = __( 'No pending URLs to preload.', 'rocket' );

			return [
				'counts'           => $counts,
				'tracking_enabled' => true,
				'estimate'         => $estimate,
			];
		}

		$ticks             = (int) ceil( $pending / max( 1, $batch_size ) );
		$estimated_seconds = $ticks * $cron_interval;

		$method = sprintf(
			/* translators: 1: number of pending URLs, 2: configured maximum batch size, 3: cron interval in seconds */
			__( 'Estimate = ceil(%1$d pending URLs / %2$d max URLs per cron tick) x %3$d seconds per tick. %2$d is the configured maximum batch size (rocket_preload_cache_pending_jobs_cron_rows_count filter), not a live measurement of actual throughput.', 'rocket' ),
			$pending,
			$batch_size,
			$cron_interval
		);

		if ( $mobile_active ) {
			$estimated_seconds *= 2;
			$method            .= ' ' . __( 'Doubled because a separate desktop and mobile cache file is generated sequentially for each URL (do_caching_mobile_files is enabled).', 'rocket' );
		}

		$estimate['estimated_seconds_remaining'] = $estimated_seconds;
		$estimate['estimated_completion_human']  = sprintf(
			/* translators: %s: human-readable duration, e.g. "12 minutes" */
			__( '~%s', 'rocket' ),
			human_time_diff( 0, $estimated_seconds )
		);
		$estimate['method'] = $method;

		return [
			'counts'           => $counts,
			'tracking_enabled' => true,
			'estimate'         => $estimate,
		];
	}
}
