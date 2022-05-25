<?php

namespace WP_Rocket\Engine\Preload\Controller;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Preload\Database\Queries\RocketCache;

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
	 * @var RocketCache
	 */
	protected $query;

	/**
	 * Configurations options.
	 *
	 * @var Options_Data
	 */
	protected $options;

	/**
	 * Instantiate preload controller.
	 *
	 * @param Options_Data $options configuration options.
	 * @param Queue        $queue preload queue.
	 * @param RocketCache  $rocket_cache preload database query.
	 */
	public function __construct( Options_Data $options, Queue $queue, RocketCache $rocket_cache ) {
		$this->options = $options;
		$this->query   = $rocket_cache;
		$this->queue   = $queue;
	}

	/**
	 * Preload an url.
	 *
	 * @param string $url url to preload.
	 * @return void
	 */
	public function preload_url( string $url ) {
		wp_remote_get(
			$url,
			[
				'blocking' => false,
				'timeout'  => 0.01,
			]
			);
		if ( $this->options->get( 'cache_mobile', false ) ) {
			wp_remote_get(
				$url,
				[
					'blocking'   => false,
					'timeout'    => 0.01,
					'user-agent' => $this->get_mobile_user_agent_prefix(),
				]
				);
		}
		$this->query->make_status_complete( $url );
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
}
