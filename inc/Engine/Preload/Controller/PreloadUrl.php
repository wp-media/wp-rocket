<?php

namespace WP_Rocket\Engine\Preload\Controller;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Preload\Database\Queries\Cache;
use WP_Rocket\Engine\Preload\Database\Queries\RocketCache;
use WP_Filesystem_Direct;

class PreloadUrl {

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
	 * @return void
	 */
	public function preload_url( string $url ) {
		if ( $this->is_already_cached( $url ) ) {
			$this->query->make_status_complete( $url );
			return;
		}

		wp_remote_get(
			user_trailingslashit( $url ),
			[
				'blocking' => false,
				'timeout'  => 0.01,
			]
			);
		if ( $this->options->get( 'cache_mobile', false ) ) {
			wp_remote_get(
				user_trailingslashit( $url ),
				[
					'blocking'   => false,
					'timeout'    => 0.01,
					'user-agent' => $this->get_mobile_user_agent_prefix(),
				]
				);
		}
	}

	/**
	 * Get the prefix to prepend to the user agent used for preload to make a HTTP request detected as a mobile device.
	 *
	 * @return string
	 */
	public function get_mobile_user_agent_prefix() {
		$prefix = 'Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1';

		/**
		 * Filter the prefix to prepend to the user agent used for preload to make a HTTP request detected as a mobile device.
		 *
		 * @param string $prefix The prefix.
		 */
		$new_prefix = apply_filters( 'rocket_mobile_preload_user_agent_prefix', $prefix );

		if ( empty( $new_prefix ) || ! is_string( $new_prefix ) ) {
			return $prefix;
		}

		return $new_prefix;
	}

	/**
	 * Process pending jobs inside CRON iteration.
	 *
	 * @return void
	 */
	public function process_pending_jobs() {
		$count = apply_filters( 'rocket_preload_cache_pending_jobs_cron_rows_count', 100 );
		$rows  = $this->query->get_pending_jobs( $count );
		foreach ( $rows as $row ) {
			$this->query->make_status_inprogress( $row->id );
			$this->queue->add_job_preload_job_preload_url_async( $row->url );
		}
	}

	/**
	 * Check if the cache file for $item already exists.
	 *
	 * @param  string $url The URL to preload.
	 * @return bool
	 */
	public function is_already_cached( string $url ) {
		static $https;

		if ( ! isset( $https ) ) {
			$https = is_ssl() && get_rocket_option( 'cache_ssl' ) ? '-https' : '';
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

		$mobile          = '';
		$file_cache_path = rocket_get_constant( 'WP_ROCKET_CACHE_PATH' ) . $url['host'] . strtolower( $url['path'] . $url['query'] ) . 'index' . $mobile . $https . '.html';

		return $this->filesystem->exists( $file_cache_path );
	}
}
