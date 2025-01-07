<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Preload\Controller;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Preload\Database\Queries\Cache;
use WP_Filesystem_Direct;

class PreloadUrl {
	use CheckExcludedTrait;

	/**
	 * Preload queue.
	 *
	 * @var Queue
	 */
	protected $queue;

	/**
	 * Preload database query.
	 *
	 * @var Cache
	 */
	protected $query;

	/**
	 * Configurations options.
	 *
	 * @var Options_Data
	 */
	protected $options;

	/**
	 * Filesystem.
	 *
	 * @var WP_Filesystem_Direct
	 */
	protected $filesystem;

	/**
	 * Instantiate preload controller.
	 *
	 * @param Options_Data         $options configuration options.
	 * @param Queue                $queue preload queue.
	 * @param Cache                $rocket_cache preload database query.
	 * @param WP_Filesystem_Direct $filesystem Filesystem.
	 */
	public function __construct( Options_Data $options, Queue $queue, Cache $rocket_cache, WP_Filesystem_Direct $filesystem ) {
		$this->options    = $options;
		$this->query      = $rocket_cache;
		$this->queue      = $queue;
		$this->filesystem = $filesystem;
	}

	/**
	 * Preload an url.
	 *
	 * @param string $url url to preload.
	 *
	 * @return void
	 */
	public function preload_url( string $url ) {
		$start    = 0;
		$duration = 0;

		$is_mobile = $this->options->get( 'do_caching_mobile_files', false );

		if ( $this->is_already_cached( $url ) && ( ! $is_mobile || $this->is_already_cached( $url, true ) ) ) {
			$this->query->make_status_complete( $url );
			return;
		}

		// Should we perform a duration check?
		$check_duration = ( false === get_transient( 'rocket_preload_check_duration' ) ) ? true : false;

		$requests = [
			[
				'url'       => $url,
				'is_mobile' => false,
				'headers'   => [
					'blocking'   => false,
					'timeout'    => 0.01,
					'user-agent' => 'WP Rocket/Preload',
				],
			],
		];

		if ( $is_mobile ) {
			$requests[] = [
				'url'       => $url,
				'headers'   => [
					'user-agent' => $this->get_mobile_user_agent_prefix(),
				],
				'is_mobile' => true,
			];
		}

		/**
		 * Filters to modify requests done to preload an url.
		 *
		 * @param array $requests Requests that will be done.
		 */
		$requests = wpm_apply_filters_typed( 'array', 'rocket_preload_before_preload_url', $requests );

		$requests = array_filter( $requests );

		foreach ( $requests as $request ) {
			if ( ! isset( $request['url'] ) || ! is_string( $request['url'] ) ) {
				continue;
			}

			$headers = isset( $request['headers'] ) && is_array( $request['headers'] ) ? $request['headers'] : [];

			$headers = array_merge(
				$headers,
				[
					'blocking'  => false,
					'timeout'   => 0.01,
					'sslverify' => apply_filters( 'https_local_ssl_verify', false ), // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
				]
			);

			if ( $check_duration ) {
				$headers['blocking'] = true;
				$headers['timeout']  = 20;
			}

			/**
			 * Filters the arguments for the preload request.
			 *
			 * @param array $headers Request arguments.
			 */
			$headers = wpm_apply_filters_typed(
				'array',
				'rocket_preload_url_request_args',
				$headers
			);

			if ( $check_duration ) {
				$start = microtime( true );
			}

			wp_safe_remote_get(
				user_trailingslashit( $request['url'] ),
				$headers
			);

			if ( $check_duration ) {
				$duration = ( microtime( true ) - $start ); // Duration of the request.
			}

			$default_delay_between = 500000;

			/**
			 * Filter the delay between each preload request.
			 *
			 * @param int $delay_between the defined delay.
			 */
			$delay_between = apply_filters( 'rocket_preload_delay_between_requests', $default_delay_between );
			$delay_between = absint( $delay_between );

			if ( empty( $delay_between ) ) {
				$delay_between = $default_delay_between;
			}

			usleep( $delay_between );

			if ( ! $check_duration ) {
				continue;
			}

			$duration_transient = get_transient( 'rocket_preload_previous_requests_durations' );
			$average_duration   = ( false !== $duration_transient ) ? $duration_transient : 0;

			// Update average duration.
			$average_duration = ( $average_duration <= 0 ) ? $duration : ( $average_duration * 0.7 + $duration * 0.3 );

			set_transient( 'rocket_preload_previous_requests_durations', $average_duration, 5 * MINUTE_IN_SECONDS );

			set_transient( 'rocket_preload_check_duration', $duration, MINUTE_IN_SECONDS ); // Don't check request duration for 1 minute.
			$check_duration = false;
		}
	}

	/**
	 * Get the prefix to prepend to the user agent used for preload to make a HTTP request detected as a mobile device.
	 *
	 * @return string
	 */
	protected function get_mobile_user_agent_prefix() {
		$prefix = 'Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1';

		/**
		 * Filter the prefix to prepend to the user agent used for preload to make a HTTP request detected as a mobile device.
		 *
		 * @param string $prefix The prefix.
		 */
		$new_prefix = wpm_apply_filters_typed( 'string', 'rocket_mobile_preload_user_agent_prefix', $prefix );

		if ( empty( $new_prefix ) ) {
			return $prefix;
		}

		return 'WP Rocket/Preload ' . $new_prefix;
	}

	/**
	 * Process pending jobs inside CRON iteration.
	 *
	 * @return void
	 */
	public function process_pending_jobs() {
		$pending_actions = $this->queue->get_pending_preload_actions();

		// Retrieve batch size limits and request timing estimation.
		/**
		 * Get the number of pending cron job
		 *
		 * @param int $args Maximum number of job size.
		 */
		$max_batch_size = ( (int) apply_filters( 'rocket_preload_cache_pending_jobs_cron_rows_count', 45 ) ) - count( $pending_actions );

		/**
		 * Get the number of in progress cron job
		 *
		 * @param int $args Minimum number of job size.
		 */
		$min_batch_size = ( (int) apply_filters( 'rocket_preload_cache_min_in_progress_jobs_count', 5 ) );

		$average_duration = get_transient( 'rocket_preload_previous_requests_durations' );

		/**
		 * Estimate batch size based on request duration.
		 * In case no estimation or there is an issue with the value use $min_batch_size.
		*/
		$next_batch_size = (int) ( ! $average_duration ? $min_batch_size : round( -5 * $average_duration + 55 ) );

		// Limit next_batch_size.
		$next_batch_size = max( $next_batch_size, $min_batch_size ); // Not lower than 5.
		$next_batch_size = min( $next_batch_size, $max_batch_size ); // Not higher than 45.
		$next_batch_size = max( $next_batch_size, 0 ); // Not lower than 0.

		// Get all in-progress jobs with request sent and no results.
		/**
		 * Set the delay before an in-progress row is considered as outdated.
		 *
		 * @param int $delay delay.
		 * @return int
		 */
		$delay = (int) apply_filters(
			'rocket_preload_outdated',
			/**
			 * Set the max number of rows in batches.
			 *
			 * @param int $count number of rows in batches.
			 * @return int
			 */
			(int) ( $max_batch_size / 15 )
		);
		$stuck_rows = $this->query->get_outdated_in_progress_jobs( $delay );

		// Make sure the request has been sent for those jobs.
		$stuck_rows = array_filter(
			$stuck_rows,
			function ( $row ) use ( $pending_actions ) {
				foreach ( $pending_actions as $action ) {
					if ( count( $action->get_args() ) > 0 && $row->url === $action->get_args()[0] ) {
						return false;
					}
				}
				return true;
			}
			);

		// Make those hanging jobs failed.
		foreach ( $stuck_rows as $row ) {
			$this->query->make_status_failed( (int) $row->id );
		}

		// Add new jobs in progress.
		$rows = $this->query->get_pending_jobs( $next_batch_size );
		foreach ( $rows as $row ) {

			if ( $this->is_excluded_by_filter( $row->url ) ) {
				$this->query->delete_by_url( $row->url );
				continue;
			}

			$this->query->make_status_inprogress( (int) $row->id );
			$this->queue->add_job_preload_job_preload_url_async( $row->url );

		}
	}

	/**
	 * Check if the cache file for $item already exists.
	 *
	 * @param  string $url The URL to preload.
	 * @param  bool   $is_mobile is mobile text.
	 *
	 * @return bool
	 */
	public function is_already_cached( string $url, bool $is_mobile = false ) {
		static $https;

		if ( ! isset( $https ) ) {
			$https = is_ssl() && $this->options->get( 'cache_ssl' ) ? '-https' : '';
		}

		$url = get_rocket_parse_url( $url );

		/** This filter is documented in inc/functions/htaccess.php */
		if ( apply_filters( 'rocket_url_no_dots', false ) ) {
			$url['host'] = str_replace( '.', '_', $url['host'] );
		}

		$url['path'] = trailingslashit( $url['path'] );

		if ( '' !== $url['query'] ) {
			$url['query'] = '#' . $url['query'] . '/';
		}

		$mobile = $is_mobile ? '-mobile' : '';

		$file_cache_path = rocket_get_constant( 'WP_ROCKET_CACHE_PATH' ) . $url['host'] . strtolower( $url['path'] . $url['query'] ) . 'index' . $mobile . $https . '.html';

		if ( ! $this->options->get( 'cache_webp', false ) ) {
			return $this->filesystem->exists( $file_cache_path );
		}

		$webp_path    = rocket_get_constant( 'WP_ROCKET_CACHE_PATH' ) . $url['host'] . strtolower( $url['path'] . $url['query'] ) . 'index' . $mobile . $https . '-webp.html';
		$no_webp_path = rocket_get_constant( 'WP_ROCKET_CACHE_PATH' ) . $url['host'] . strtolower( $url['path'] . $url['query'] ) . '.no-webp';

		return $this->filesystem->exists( $webp_path ) || ( $this->filesystem->exists( $no_webp_path ) && $this->filesystem->exists( $file_cache_path ) );
	}
}
