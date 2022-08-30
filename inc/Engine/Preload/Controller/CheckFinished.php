<?php

namespace WP_Rocket\Engine\Preload\Controller;

use WP_Rocket\Engine\Preload\Admin\Settings;
use WP_Rocket\Engine\Preload\Database\Queries\Cache;

class CheckFinished {

	/**
	 * Preload settings.
	 *
	 * @var Settings
	 */
	protected $settings;

	/**
	 * Preload queue.
	 *
	 * @var Queue
	 */
	protected $queue;

	/**
	 * Db query.
	 *
	 * @var Cache
	 */
	protected $query;

	/**
	 * Instantiate class.
	 *
	 * @param Settings $settings Preload settings.
	 * @param Cache    $cache Db query.
	 * @param Queue    $queue Preload queue.
	 */
	public function __construct( Settings $settings, Cache $cache, Queue $queue ) {
		$this->settings = $settings;
		$this->query    = $cache;
		$this->queue    = $queue;
	}

	/**
	 * Check if the preload is finished.
	 *
	 * @return void
	 */
	public function check_finished() {
		if ( ( ! $this->queue->has_remaining_tasks() && ! $this->query->has_pending_jobs() ) || ! $this->settings->is_enabled() ) {
			delete_transient( 'wpr_preload_running' );
			return;
		}

		$this->queue->add_job_preload_job_check_finished_async();
	}
}
