<?php

namespace WP_Rocket\Engine\Preload\Controller;

use WP_Rocket\Engine\Preload\Admin\Settings;
use WP_Rocket\Engine\Preload\Database\Queries\Cache;

class CheckFinished
{
	/**
	 * @var Settings
	 */
	protected $settings;

	/**
	 * @var Queue
	 */
	protected $queue;

	/**
	 * @param Queue $queue
	 */
	public function __construct(Settings $settings, Cache $cache, Queue $queue)
	{
		$this->settings = $settings;
		$this->query = $cache;
		$this->queue = $queue;
	}
	public function checkFinished() {
		if((! $this->queue->has_tasks_remain() && ! $this->query->has_pending_jobs()) || ! $this->settings->is_enabled()) {
			delete_transient('wpr_preload_running');
			return;
		}

		$this->queue->add_job_preload_job_check_finished_async();
	}
}
