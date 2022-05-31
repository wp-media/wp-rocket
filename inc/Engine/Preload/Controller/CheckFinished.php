<?php

namespace WP_Rocket\Engine\Preload\Controller;

class CheckFinished
{
	/**
	 * @var Queue
	 */
	protected $queue;

	/**
	 * @param Queue $queue
	 */
	public function __construct(Queue $queue)
	{
		$this->queue = $queue;
	}

	public function checkFinished() {
		if(! $this->queue->has_tasks_remain()) {
			delete_transient('wpr_preload_running');
			return;
		}
		$this->queue->add_job_preload_job_check_finished_async();
	}
}
