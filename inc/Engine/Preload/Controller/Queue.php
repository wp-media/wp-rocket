<?php

namespace WP_Rocket\Engine\Preload\Controller;

use WP_Rocket\Engine\Common\Queue\AbstractASQueue;

class Queue extends AbstractASQueue {

	/**
	 * Group from the queue.
	 *
	 * @var string
	 */
	protected $group = 'rocket-preload';

	/**
	 * Pending jobs cron hook.
	 *
	 * @var string
	 */
	private $pending_job_cron = 'rocket_preload_pending_job_cron';

	/**
	 * Check if pending jobs cron is scheduled.
	 *
	 * @return bool
	 */
	public function is_pending_jobs_cron_scheduled() {
		return $this->is_scheduled( $this->pending_job_cron );
	}

	/**
	 * Cancel pending jobs cron.
	 *
	 * @return void
	 */
	public function cancel_pending_jobs_cron() {
		$this->cancel_all( $this->pending_job_cron );
	}

	/**
	 * Schedule pending jobs cron.
	 *
	 * @param int $interval Cron interval in seconds.
	 *
	 * @return string
	 */
	public function schedule_pending_jobs_cron( int $interval ) {
		return $this->schedule_recurring( time(), $interval, $this->pending_job_cron );
	}

	/**
	 * Add Async parse sitemap job with url.
	 *
	 * @param string $sitemap_url sitemap url.
	 *
	 * @return string
	 */
	public function add_job_preload_job_parse_sitemap_async( string $sitemap_url ) {
		return $this->add_async(
			'rocket_preload_job_parse_sitemap',
			[
				$sitemap_url,
			]
		);
	}

	/**
	 * Add Async job with DB row ID.
	 *
	 * @param string $sitemap_url DB row ID.
	 *
	 * @return string
	 */
	public function add_job_preload_job_preload_url_async( string $sitemap_url ) {
		return $this->add_async(
			'rocket_preload_job_preload_url',
			[
				$sitemap_url,
			]
		);
	}
  
   /**
   * Add Async preload url job with url.
	 *
	 * @param string $url url to preload.
	 *
	 * @return string
	 */
	public function add_job_preload_job_preload_url_async( string $url ) {
		return $this->add_async(
			'rocket_preload_job_preload_url',
			[
				$url,
			]
		);
	}

	/**
	 * Add a job that check if the preload is finished.
	 *
	 * @return string
	 */
	public function add_job_preload_job_check_finished_async() {
		return $this->add_async( 'rocket_preload_job_check_finished', [] );
	}

	/**
	 * Check if some task is remaining.
	 *
	 * @return bool
	 */
	public function has_remaining_tasks() {
		$parse_sitemap = $this->search(
			[
				'hook'   => 'rocket_preload_job_parse_sitemap',
				'status' => ActionScheduler_Store::STATUS_PENDING,
			],
			'ids'
		);
		$preload_url   = $this->search(
			[
				'hook'   => 'rocket_preload_job_preload_url',
				'status' => ActionScheduler_Store::STATUS_PENDING,
			],
			'ids'
		);

		return count( $parse_sitemap ) > 0 || count( $preload_url ) > 0;
	}
}
