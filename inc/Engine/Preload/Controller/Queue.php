<?php

namespace WP_Rocket\Engine\Preload\Controller;

use ActionScheduler_Store;
use WP_Rocket\Engine\Common\Queue\AbstractASQueue;

class Queue extends AbstractASQueue {

	/**
	 * Group from the queue.
	 *
	 * @var string
	 */
	protected $group = 'rocket-preload';


	/**
	 * Add Async load initial sitemap job.
	 *
	 * @return string
	 */
	public function add_job_preload_job_load_initial_sitemap_async() {
		return $this->add_async( 'rocket_preload_job_load_initial_sitemap' );
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

		if ( $this->job_preload_job_check_finished_async_exists() ) {
			return '';
		}

		return $this->schedule_single( time() + MINUTE_IN_SECONDS, 'rocket_preload_job_check_finished', [ time() ] );
	}

	/**
	 * Check if a task job_preload_job_check_finished_async_exists already exists.
	 *
	 * @return bool
	 */
	public function job_preload_job_check_finished_async_exists() {
		if ( ! did_action( 'init' ) || doing_action( 'init' ) ) {
			return true;
		}

		$row_found = $this->search(
			[
				'hook'   => 'rocket_preload_job_check_finished',
				'status' => ActionScheduler_Store::STATUS_PENDING,
			],
			'ids'
		);

		return count( $row_found ) > 0;
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

	/**
	 * Cancel pending jobs.
	 *
	 * @return void
	 */
	public function cancel_pending_jobs() {
		$this->cancel_all( '' );
	}

	/**
	 * Return pending actions inside AS scheduler queue.
	 *
	 * @return array
	 */
	public function get_pending_preload_actions(): array {
		return $this->search(
			[
				'hook'     => 'rocket_preload_job_preload_url',
				'status'   => ActionScheduler_Store::STATUS_PENDING,
				'per_page' => -1,
			]
		);
	}
}
