<?php

namespace WP_Rocket\Engine\Common\JobManager;

use WP_Rocket\Logger\LoggerAware;
use WP_Rocket\Logger\LoggerAwareInterface;
use WP_Rocket\Engine\Common\JobManager\Queue\Queue;
use WP_Rocket\Engine\Common\JobManager\Strategy\Factory\StrategyFactory;
use WP_Rocket\Engine\Common\JobManager\APIHandler\APIClient;
use WP_Rocket\Engine\Common\Clock\WPRClock;
use WP_Rocket\Engine\Common\Utils;

class JobProcessor implements LoggerAwareInterface {
	use LoggerAware;

	/**
	 * Array of Factories.
	 *
	 * @var array
	 */
	private $factories;

	/**
	 * Queue instance.
	 *
	 * @var Queue
	 */
	private $queue;

	/**
	 * Retry Strategy Factory
	 *
	 * @var StrategyFactory
	 */
	protected $strategy_factory;

	/**
	 * APIClient instance
	 *
	 * @var APIClient
	 */
	private $api;

	/**
	 * Clock instance.
	 *
	 * @var WPRClock
	 */
	protected $wpr_clock;

	/**
	 * Instantiate the class.
	 *
	 * @param array           $factories Array of factories.
	 * @param Queue           $queue Queue instance.
	 * @param StrategyFactory $strategy_factory Strategy Factory.
	 * @param APIClient       $api APIClient instance.
	 * @param WPRClock        $clock Clock object instance.
	 */
	public function __construct(
		array $factories,
		Queue $queue,
		StrategyFactory $strategy_factory,
		APIClient $api,
		WPRClock $clock
	) {
		$this->factories        = $factories;
		$this->queue            = $queue;
		$this->strategy_factory = $strategy_factory;
		$this->api              = $api;
		$this->wpr_clock        = $clock;
	}

	/**
	 * Determine if action is allowed.
	 *
	 * @return boolean
	 */
	public function is_allowed(): bool {
		if ( ! $this->factories ) {
			return false;
		}

		$is_allowed = [];

		foreach ( $this->factories as $factory ) {
			$is_allowed[] = $factory->manager()->is_allowed();
		}

		return (bool) array_sum( $is_allowed );
	}

	/**
	 * Process pending jobs inside cron iteration.
	 *
	 * @return void
	 */
	public function process_pending_jobs() {
		/**
		 * Fires at the start of the process pending jobs.
		 *
		 * @param string $current_time Current time.
		 */
		rocket_do_action_and_deprecated(
			'rocket_saas_process_pending_jobs_start',
			[ $this->wpr_clock->current_time( 'mysql', true ) ],
			'3.16',
			'rocket_rucss_process_pending_jobs_start'
		);
		$this->logger::debug( 'RUCSS: Start processing pending jobs inside cron.' );

		if ( ! $this->is_allowed() ) {
			$this->logger::debug( 'Stop processing cron iteration for pending jobs.' );

			return;
		}

		$this->logger::debug( 'Start processing pending jobs inside cron.' );

		// Get some items from the DB with status=pending & job_id isn't empty.

		/**
		 * Filters the pending jobs count.
		 *
		 * @since 3.11
		 *
		 * @param int $rows Number of rows to grab with each CRON iteration.
		 */
		$rows = rocket_apply_filter_and_deprecated(
			'rocket_saas_pending_jobs_cron_rows_count',
			[ 100 ],
			'3.16',
			'rocket_rucss_pending_jobs_cron_rows_count'
		);

		$pending_jobs = $this->get_jobs( $rows, 'pending' );

		if ( ! $pending_jobs ) {
			return;
		}

		foreach ( $pending_jobs as $row ) {
			$current_time = $this->wpr_clock->current_time( 'timestamp', true );
			if ( $row->next_retry_time < $current_time ) {
				$optimization_type = $this->get_optimization_type( $row );
				// Change status to in-progress.
				$this->make_status_inprogress( $row->url, $row->is_mobile, $optimization_type );
				$this->queue->add_job_status_check_async( $row->url, $row->is_mobile, $optimization_type );
			}
		}

		/**
		 * Fires at the end of the process pending jobs.
		 *
		 * @param string $current_time Current time.
		 */
		rocket_do_action_and_deprecated(
			'rocket_saas_process_pending_jobs_end',
			[ $this->wpr_clock->current_time( 'mysql', true ) ],
			'3.16',
			'rocket_rucss_process_pending_jobs_end'
		);
	}

	/**
	 * Check job status by DB row ID.
	 *
	 * @param string  $url Url from DB row.
	 * @param boolean $is_mobile Is mobile from DB row.
	 * @param string  $optimization_type The type of optimization request to send.
	 *
	 * @return void
	 */
	public function check_job_status( string $url, bool $is_mobile, string $optimization_type ) {

		$row_details = $this->get_single_job( $url, $is_mobile, $optimization_type );
		if ( ! is_object( $row_details ) ) {
			$this->logger::debug( 'Url - ' . $url . ' not found for is_mobile -  ' . (int) $is_mobile );
			// Nothing in DB, bailout.
			return;
		}

		// Send the request to get the job status from SaaS.
		$job_details = $this->api->get_queue_job_status( $row_details->job_id, $row_details->queue_name, Utils::is_home( $row_details->url ) );

		foreach ( $this->factories as $factory ) {
			$factory->manager()->validate_and_fail( $job_details, $row_details, $optimization_type );
		}

		if (
			200 !== (int) $job_details['code']
		) {
			$this->logger::debug( 'Job status failed for url: ' . $row_details->url, $job_details );
			$this->decide_strategy( $row_details, $job_details, $optimization_type );

			return;
		}
		/**
		 * Unlock preload URL.
		 *
		 * @param string $url URL to unlock
		 */
		do_action( 'rocket_preload_unlock_url', $row_details->url );

		foreach ( $this->factories as $factory ) {
			$factory->manager()->process( $job_details, $row_details, $optimization_type );
		}

		/**
		 * Fires after successfully Processing the SaaS jobs.
		 *
		 * @param string $current_time Current time.
		 */
		rocket_do_action_and_deprecated(
			'rocket_saas_check_job_status_end',
			[ $this->wpr_clock->current_time( 'mysql', true ) ],
			'3.16',
			'rocket_rucss_check_job_status_end'
		);

		/**
		 * Fires after successfully processing the SaaS jobs.
		 *
		 * @param string $url Optimized Url.
		 * @param array  $job_details Result of the request to get the job status from SaaS.
		 */
		rocket_do_action_and_deprecated(
			'rocket_saas_complete_job_status',
			[ $row_details->url, $job_details ],
			'3.16',
			'rocket_rucss_complete_job_status'
		);
	}

	/**
	 * Process on submit jobs.
	 *
	 * @return void
	 */
	public function process_on_submit_jobs() {
		$this->logger::debug( 'Start processing on submit jobs for adding jobs to queue.' );

		/**
		 * Fires at the start of the process on submit jobs.
		 *
		 * @param string $current_time Current time.
		 */
		rocket_do_action_and_deprecated(
			'rocket_saas_process_on_submit_jobs_start',
			[ $this->wpr_clock->current_time( 'mysql', true ) ],
			'3.16',
			'rocket_rucss_process_on_submit_jobs_start'
		);

		if ( ! $this->is_allowed() ) {
			$this->logger::debug( 'Stop processing cron iteration for to-submit jobs.' );

			return;
		}

		/**
		 * Pending rows cont.
		 *
		 * @param int $count Number of rows.
		 */
		$pending_job = rocket_apply_filter_and_deprecated(
			'rocket_saas_pending_jobs_cron_rows_count',
			[ 100 ],
			'3.16',
			'rocket_rucss_pending_jobs_cron_rows_count'
		);

		/**
		 * Maximum processing rows.
		 *
		 * @param int $max Max processing rows.
		 */
		$max_pending_rows = (int) rocket_apply_filter_and_deprecated(
			'rocket_saas_max_pending_jobs',
			[ 3 * $pending_job, $pending_job ],
			'3.16',
			'rocket_rucss_max_pending_jobs'
		);

		$rows = $this->get_jobs( $max_pending_rows, 'submit' );

		if ( ! $rows ) {
			return;
		}

		foreach ( $rows as $row ) {
			$optimization_type = $this->get_optimization_type( $row );
			$response          = $this->send_api( $row->url, (bool) $row->is_mobile, $optimization_type );

			if ( false === $response || ! isset( $response['contents'], $response['contents']['jobId'], $response['contents']['queueName'] ) ) {

				$this->make_status_failed( $row->url, $row->is_mobile, '', '', $optimization_type );
				continue;
			}

			/**
			 * Lock preload URL.
			 *
			 * @param string $url URL to lock
			 */
			do_action( 'rocket_preload_lock_url', $row->url );

			$this->make_status_pending(
				$row->url,
				$response['contents']['jobId'],
				$response['contents']['queueName'],
				(bool) $row->is_mobile,
				$optimization_type
			);
		}

		$this->logger::debug( 'End processing on submit jobs for adding jobs to queue.' );
		/**
		 * Fires at the end of the process pending jobs.
		 *
		 * @param string $current_time Current time.
		 */
		rocket_do_action_and_deprecated(
			'rocket_saas_process_on_submit_jobs_end',
			[ $this->wpr_clock->current_time( 'mysql', true ) ],
			'3.16',
			'rocket_rucss_process_on_submit_jobs_end'
		);
	}

	/**
	 * Send the job to the API.
	 *
	 * @param string $url URL to work on.
	 * @param bool   $is_mobile Is the page for mobile.
	 * @param string $optimization_type The type of optimization request to send.
	 * @return array|false
	 */
	protected function send_api( string $url, bool $is_mobile, string $optimization_type ) {
		$config = [
			'is_mobile' => $is_mobile,
			'is_home'   => Utils::is_home( $url ),
		];

		$config = $this->set_request_params( $config, $optimization_type );

		$add_to_queue_response = $this->api->add_to_queue( $url, $config );

		if ( 200 !== $add_to_queue_response['code'] ) {
			$this->logger::error(
				'Error when contacting the SaaS API.',
				[
					'SaaS error',
					'url'     => $url,
					'code'    => $add_to_queue_response['code'],
					'message' => $add_to_queue_response['message'],
				]
			);

			return false;
		}

		return $add_to_queue_response;
	}

	/**
	 * Set request parameters
	 *
	 * @param array  $config Array of request parameters.
	 * @param string $optimization_type The type of optimization applied for the current job.
	 * @return array
	 */
	public function set_request_params( array $config, string $optimization_type ): array {
		list($updated_config, $optimization_list, $request_param) = [ [], [], [] ];

		foreach ( $this->factories as $factory ) {
			if ( $optimization_type === $factory->manager()->get_optimization_type() ) {
				$config = array_merge( $config, $factory->manager()->set_request_param() );

				return $config;
			}

			$request_param = $factory->manager()->set_request_param();

			$optimization_list = array_merge( $optimization_list, $request_param['optimization_list'] );
			$updated_config    = array_merge( $request_param, $updated_config );
		}

		if ( ! $updated_config ) {
			$updated_config['optimization_list'] = $optimization_list;
		}

		return $updated_config;
	}

	/**
	 * Clear failed urls.
	 *
	 * @return void
	 */
	public function clear_failed_urls(): void {
		/**
		 * Delay before failed saas jobs are deleted.
		 *
		 * @param string $delay delay before failed saas jobs are deleted.
		 */
		$delay = (string) rocket_apply_filter_and_deprecated(
			'rocket_delay_remove_saas_failed_jobs',
			[ '3 days' ],
			'3.16',
			'rocket_delay_remove_rucss_failed_jobs'
		);

		if ( '' === $delay || '0' === $delay ) {
			$delay = '3 days';
		}
		$parts = explode( ' ', $delay );

		$value = 3;
		$unit  = 'days';

		if ( count( $parts ) === 2 && $parts[0] >= 0 ) {
			$value = (float) $parts[0];
			$unit  = $parts[1];
		}

		foreach ( $this->factories as $factory ) {
			if ( $factory->manager()->is_allowed() ) {
				$failed_urls = $factory->manager()->clear_failed_jobs( $value, $unit );

				$hook = 'rocket_' . $factory->manager()->get_optimization_type() . '_after_clearing_failed_url';

				/**
				 * Fires after clearing failed urls.
				 *
				 * @param array $urls Failed urls.
				 */
				do_action( $hook, $failed_urls ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
			}
		}
	}

	/**
	 * Change the status to be in-progress.
	 *
	 * @param string  $url Url from DB row.
	 * @param boolean $is_mobile Is mobile from DB row.
	 * @param string  $optimization_type The type of optimization applied for the current job.
	 * @return void
	 */
	private function make_status_inprogress( string $url, bool $is_mobile, string $optimization_type ): void {
		foreach ( $this->factories as $factory ) {
			$factory->manager()->make_status_inprogress( $url, $is_mobile, $optimization_type );
		}
	}

	/**
	 * Get single job.
	 *
	 * @param string  $url Url from DB row.
	 * @param boolean $is_mobile Is mobile from DB row.
	 * @param string  $optimization_type The type of optimization applied for the current job.
	 *
	 * @return bool|object
	 */
	private function get_single_job( string $url, bool $is_mobile, string $optimization_type ) {
		$job = [];

		foreach ( $this->factories as $factory ) {
			if ( $optimization_type === $factory->manager()->get_optimization_type() ) {
				return $factory->manager()->get_single_job( $url, $is_mobile );
			}
		}

		$job = $this->factories[0]->manager()->get_single_job( $url, $is_mobile );

		return ( ! $job ? [] : $job );
	}

	/**
	 * Decide jobs to get.
	 *
	 * @param integer $num_rows Number of rows to grab with each CRON iteration.
	 * @param string  $type Type of job to get.
	 * @return array
	 */
	public function get_jobs( int $num_rows, string $type ): array {
		$allowed_types = [ 'pending', 'submit' ];

		if ( ! in_array( $type, $allowed_types, true ) ) {
			return [];
		}

		$rows = [];

		switch ( $type ) {
			case 'pending':
				foreach ( $this->factories as $factory ) {
					$rows = array_merge( $rows, $factory->manager()->get_pending_jobs( $num_rows ) );
				}
				break;
			case 'submit':
				foreach ( $this->factories as $factory ) {
					$rows = array_merge( $rows, $factory->manager()->get_on_submit_jobs( $num_rows ) );
				}
				break;
		}

		if ( ! $rows ) {
			return [];
		}

		// Get distinct rows.
		return $this->get_distinct( $rows );
	}

	/**
	 * Get rows common to jobs.
	 *
	 * @param array $rows Merged DB Rows of jobs.
	 * @return array
	 */
	private function get_common_jobs( array $rows ): array {
		list($occurrences, $duplicates) = [ [], [] ];

		foreach ( $rows as $row ) {
			$key = $row->url . '|' . ( isset( $row->is_mobile ) ? (bool) $row->is_mobile : 'null' );

			if ( ! isset( $occurrences[ $key ] ) ) {
				$occurrences[ $key ] = 1;

				continue;
			}

			++$occurrences[ $key ];

			if ( 2 === $occurrences[ $key ] ) {
				// Add new is_common property to the object and add object to duplicate.
				$row->is_common = true;
				$duplicates[]   = $row;
			}
		}

		return $duplicates;
	}

	/**
	 * Get distinct rows merged from both jobs.
	 *
	 * @param array $rows Merged DB Rows of jobs.
	 * @return array
	 */
	private function get_distinct( array $rows ): array {
		// Get jobs common to both optimizations.
		$common_rows = $this->get_common_jobs( $rows );

		if ( ! $common_rows ) {
			return $rows;
		}

		$index = 0;

		foreach ( $rows as $row ) {
			foreach ( $common_rows as $common_row ) {
				if ( $row->url === $common_row->url && (bool) $row->is_mobile === (bool) $common_row->is_mobile ) {
					// Remove the common row that is without the new is_common property.
					unset( $rows[ $index ] );
				}
			}

			++$index;
		}

		return array_merge( $rows, $common_rows );
	}

	/**
	 * Get the optimization type requested.
	 *
	 * @param object $row DB Row.
	 * @return string
	 */
	public function get_optimization_type( $row ): string {
		$optimization_type = 'all';

		if ( isset( $row->is_common ) ) {
			return $optimization_type;
		}

		foreach ( $this->factories as $factory ) {
			$type = $factory->manager()->get_optimization_type_from_row( $row );

			if ( is_string( $type ) ) {
				$optimization_type = $type;
				break;
			}
		}

		return $optimization_type;
	}

	/**
	 * Decide with job strategy to apply based on the optimization type.
	 *
	 * @param object $row_details DB Row of job.
	 * @param array  $job_details Job details from the API.
	 * @param string $optimization_type The type of optimization applied for the current job.
	 * @return void
	 */
	private function decide_strategy( $row_details, array $job_details, string $optimization_type ): void {
		foreach ( $this->factories as $factory ) {
			if ( $optimization_type === $factory->manager()->get_optimization_type() ) {
				$this->strategy_factory->manage( $row_details, $job_details, $factory->manager() );
				break;
			}

			$this->strategy_factory->manage( $row_details, $job_details, $factory->manager() );
		}
	}

	/**
	 * Change the job status to be failed.
	 *
	 * @param string  $url Url from DB row.
	 * @param boolean $is_mobile Is mobile from DB row.
	 * @param string  $error_code error code.
	 * @param string  $error_message error message.
	 * @param string  $optimization_type The type of optimization applied for the current job.
	 * @return void
	 */
	private function make_status_failed( string $url, bool $is_mobile, string $error_code, string $error_message, $optimization_type ): void {
		foreach ( $this->factories as $factory ) {
			$factory->manager()->make_status_failed( $url, $is_mobile, $error_code, $error_message, $optimization_type );
		}
	}

	/**
	 * Change the job status to be pending.
	 *
	 * @param string  $url Url from DB row.
	 * @param string  $job_id API job_id.
	 * @param string  $queue_name API Queue name.
	 * @param boolean $is_mobile if the request is for mobile page.
	 * @param string  $optimization_type The type of optimization applied for the current job.
	 * @return void
	 */
	private function make_status_pending( string $url, string $job_id, string $queue_name, bool $is_mobile, string $optimization_type ): void {
		foreach ( $this->factories as $factory ) {
			$factory->manager()->make_status_pending( $url, $job_id, $queue_name, $is_mobile, $optimization_type );
		}
	}
}
