<?php

namespace WP_Rocket\Engine\Common\JobManager;

use WP_Rocket\Engine\Common\JobManager\Interfaces\ManagerInterface;
use WP_Rocket\Engine\Common\Queue\QueueInterface;
use WP_Rocket\Engine\Optimization\RUCSS\Strategy\Factory\StrategyFactory;
use WP_Rocket\Admin\Options_Data;

class JobProcessor implements LoggerAwareInterface {
    use LoggerAware;

    /**
     * RUCSS Job Manager.
     *
     * @var ManagerInterface
     */
    private $rucss_manager;

    /**
     * LCP Job Manager.
     *
     * @var ManagerInterface
     */
    private $atf_manager;

    /**
     * Queue instance.
     *
     * @var QueueInterface
     */
    private $queue;

    /**
     * Retry Strategy Factory
     *
     * @var StrategyFactory
     */
    protected $strategy_factory;

	/**
	 * Plugin options instance.
	 *
	 * @var Options_Data
	 */
	protected $options;


    /**
     * Instantiate the class.
     *
     * @param ManagerInterface $rucss_manager RUCSS Job Manager.
     * @param ManagerInterface $lcp_manager LCP Job Manager.
     */
    public function __construct(
        ManagerInterface $rucss_manager,
        ManagerInterface $atf_manager,
        QueueInterface $queue,
        StrategyFactory $strategy_factory,
		Options_Data $options
    ) {
        $this->rucss_manager = $rucss_manager;
        $this->atf_manager = $atf_manager;
        $this->queue = $queue;
        $this->strategy_factory = $strategy_factory;
		$this->options = $options;
    }

	/**
     * Option contexts.
     *
     * @return array
     */
    public function context(): array {
        return $context = [
            'rucss' => $this->rucss_manager->is_allowed(),
            'atf' => $this->atf_manager->is_allowed(),
        ];
    }

    /**
     * Process pending jobs inside cron iteration.
     *
     * @return void
     */
    public function process_pending_jobs() {
		$context = $this->context();

        if ( ! $context['rucss'] && ! $context['atf'] ) {
            $this->logger::debug( 'RUCSS/ATF: Stop processing cron iteration because both options are disabled.' );

            return;
        }

        $this->rucss_manager->log_start_process();
        $this->atf_manager->log_start_process();

        // Get some items from the DB with status=pending & job_id isn't empty.

        /**
         * Filters the pending jobs count.
         *
         * @since 3.11
         *
         * @param int $rows Number of rows to grab with each CRON iteration.
         */
        $rows = apply_filters( 'rocket_rucss_pending_jobs_cron_rows_count', 100 );

        $pending_jobs = $this->get_pending_jobs( $context, $rows );

        if ( ! $pending_jobs ) {
            return;
        }

        foreach ( $pending_jobs as $row ) {
            $current_time = $this->wpr_clock->current_time( 'timestamp', true );
            if ( strtotime( $row->next_retry_time ) < $current_time ) {
                
                // Change status to in-progress.
                $this->make_status_inprogress( $context, $row->url, $row->is_mobile );
                $this->queue->add_job_status_check_async( $row->url, $row->is_mobile );
            }
        }
    }

    /**
     * Check job status by DB row ID.
     *
     * @param string $url Url from DB row.
	 * @param boolean $is_mobile Is mobile from DB row.
     *
     * @return void
     */
    public function check_job_status( string $url, bool $is_mobile ) {
		$context = $this->context();

        $row_details = $this->get_single_job( $context, $url, $is_mobile );
        if ( ! $row_details ) {
            $this->logger::debug( 'RUCSS/ATF: Url not found for is_mobile -  ' . (int) $is_mobile );

            // Nothing in DB, bailout.
            return;
        }

        // Send the request to get the job status from SaaS.
        $job_details = $this->api->get_queue_job_status( $row_details->job_id, $row_details->queue_name, $this->is_home( $row_details->url ) );

        /**
         * Filters the rocket min rucss css result size.
         *
         * @since 3.13.3
         *
         * @param int min size.
         */
        $min_rucss_size = apply_filters( 'rocket_min_rucss_size', 150 );
        if ( ! is_numeric( $min_rucss_size ) ) {
            $min_rucss_size = 150;
        }

        if ( $this->rucss_manager->is_allowed() && isset( $job_details['contents']['shakedCSS_size'] ) && intval( $job_details['contents']['shakedCSS_size'] ) < $min_rucss_size ) {
            $message = 'RUCSS: shakedCSS size is less than ' . $min_rucss_size;
            $this->logger::error( $message );
            $this->used_css_query->make_status_failed( $id, '500', $message );
            return;
        }

        if (
            200 !== (int) $job_details['code']
        ) {
            $this->logger::debug( 'RUCSS/ATF: Job status failed for url: ' . $row_details->url, $job_details );
            $this->strategy_factory->manage( $row_details, $job_details );

            return;
        }
        /**
         * Unlock preload URL.
         *
         * @param string $url URL to unlock
         */
        do_action( 'rocket_preload_unlock_url', $row_details->url );

        $job_data = [
            'id' => $row_details->id,
            'job_details' => $job_details,
            'row_details' => $row_details,
        ];

        $this->rucss_manager->process( $job_data );
        $this->lcp_manager->process( $job_data );
    }

    /**
     * Process on submit jobs.
     *
     * @return void
     */
    public function process_on_submit_jobs() {
		$context = $this->context();

        if ( ! $context['rucss'] && ! $context['atf'] ) {
            $this->logger::debug( 'RUCSS/ATF: Stop processing cron iteration because both options are disabled.' );

            return;
        }

        /**
         * Pending rows cont.
         *
         * @param int $count Number of rows.
         */
        $pending_job = (int) apply_filters( 'rocket_rucss_pending_jobs_cron_rows_count', 100 );

        /**
         * Maximum processing rows.
         *
         * @param int $max Max processing rows.
         */
        $max_pending_rows = (int) apply_filters( 'rocket_rucss_max_pending_jobs', 3 * $pending_job, $pending_job );
        $rows             = $this->get_on_submit_jobs( $max_pending_rows );

        foreach ( $rows as $row ) {
            $response = $this->send_api( $row->url, (bool) $row->is_mobile );
            if ( false === $response || ! isset( $response['contents'], $response['contents']['jobId'], $response['contents']['queueName'] ) ) {

                $this->make_status_failed( $context, $row->url, $row->is_mobile, '', '' );
                continue;
            }

            /**
             * Lock preload URL.
             *
             * @param string $url URL to lock
             */
            do_action( 'rocket_preload_lock_url', $row->url );

            $this->make_status_pending(
                $context, 
                $row->url,
                $response['contents']['jobId'], 
                $response['contents']['queueName'], 
                (bool) $row->is_mobile
            );
        }
    }

    /**
     * Send the job to the API.
     *
     * @param string $url URL to work on.
     * @param bool   $is_mobile Is the page for mobile.
     * @return array|false
     */
    protected function send_api( string $url, bool $is_mobile ) {
        /**
         * Filters the RUCSS safelist
         *
         * @since 3.11
         *
         * @param array $safelist Array of safelist values.
         */
        $safelist = apply_filters( 'rocket_rucss_safelist', $this->options->get( 'remove_unused_css_safelist', [] ) );

        /**
         * Filters the styles attributes to be skipped (blocked) by RUCSS.
         *
         * @since 3.14
         *
         * @param array $skipped_attr Array of safelist values.
         */
        $skipped_attr = apply_filters( 'rocket_rucss_skip_styles_with_attr', [] );
        $skipped_attr = ( is_array( $skipped_attr ) ) ? $skipped_attr : [];

        $config = [
            'treeshake'      => 1,
            'rucss_safelist' => $safelist,
            'skip_attr'      => $skipped_attr,
            'is_mobile'      => $is_mobile,
            'is_home'        => $this->is_home( $url ),
        ];

		$config = $this->set_request_params( $config );

        $add_to_queue_response = $this->api->add_to_queue( $url, $config );
        if ( 200 !== $add_to_queue_response['code'] ) {
            $this->logger::error(
                'Error when contacting the RUCSS API.',
                [
                    'rucss error',
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
	 * Clear failed urls.
	 *
	 * @return void
	 */
	public function clear_failed_urls(): void {
		$context = $this->context();

		/**
		 * Delay before failed rucss jobs are deleted.
		 *
		 * @param string $delay delay before failed rucss jobs are deleted.
		 */
		$delay = (string) apply_filters( 'rocket_delay_remove_rucss_failed_jobs', '3 days' );

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

		if ( $context['rucss'] ) {
			$failed_urls = $this->rucss_manager->clear_failed_jobs( $value, $unit );

			/**
			 * Fires after clearing failed urls.
			 *
			 * @param array $urls Failed urls.
			 */
			do_action( 'rocket_rucss_after_clearing_failed_url', $failed_urls );
		}
		
		if ( $context['atf'] ) {
			$this->atf_manager->clear_failed_jobs( $value, $unit );
		}
	}

	/**
	 * Set request parameters
	 *
	 * @param array $config Array of request parameters.
	 * @return array
	 */
	private function set_request_params( array $config ): array {
		$context = $this->context();

		if ( $context['rucss'] ) {
			$config['optimization_list'][] = 'rucss';
		}

		if ( $context['atf'] ) {
			$config['optimization_list'][] = 'lcp';
			$config['optimization_list'][] = 'above_fold';
			$config['above_fold_time'] = 500;
		}

		return $config;
	}

    /**
     * Get pending jobs based on enabled option.
     *
	 * @param array $context Context.
     * @param integer $num_rows Number of rows to grab with each CRON iteration.
     * @return array
     */
    private function get_pending_jobs( array $context, int $num_rows ): array {
        if ( ! $context['rucss'] && $context['atf'] ) {
            $this->logger::debug( "ATF: Start getting number of {$rows} pending jobs." );
            return $this->atf_manager->get_pending_jobs( $num_rows );
        }

        $this->logger::debug( "RUCSS: Start getting number of {$rows} pending jobs." );
        return $this->rucss_manager->get_pending_jobs( $num_rows );
    }
	

    /**
     * Change the status to be in-progress.
     *
     * @param array $context Context.
     * @param string $url Url from DB row.
     * @param boolean $is_mobile Is mobile from DB row.
     * @return void
     */
    private function make_status_inprogress( array $context, string $url, bool $is_mobile ): void {
        if ( $context['rucss'] ) {
            $this->logger::debug( "RUCSS: Send the job for url {$url} to Async task to check its job status." );

            $this->rucss_manager
                ->query()
                ->make_status_inprogress( $url, $is_mobile );
        }

        if ( $context['atf'] ) {
            $this->logger::debug( "ATF: Send the job for url {$url} to Async task to check its job status." );

            $this->atf_manager
                ->query()
                ->make_status_inprogress( $url, $is_mobile );
        }
    }

	/**
     * Get single job.
     *
	 * @param array $context Context.
     * @param string $url Url from DB row.
     * @param boolean $is_mobile Is mobile from DB row.
     * @return bool|object
     */
    private function get_single_job( array $context, string $url, bool $is_mobile ) {
        if ( ! $context['rucss'] && $context['atf'] ) {
            $this->logger::debug( 'ATF: Start checking job status for url: ' . $url );

            return $this->atf_manager
                    ->query()
                    ->get_row( $url, $is_mobile );
        }

        $this->logger::debug( 'RUCSS: Start checking job status for url: ' . $url );
        return $this->rucss_manager
                ->query()
                ->get_row( $url, $is_mobile );
    }

	/**
     * Get on submit jobs based on enabled option.
     *
	 * @param array $context Context.
     * @param integer $num_rows Number of rows to grab with each CRON iteration.
     * @return array
     */
    private function get_on_submit_jobs( array $context, int $num_rows ): array {
        if ( ! $context['rucss'] && $context['atf'] ) {
            return $this->atf_manager->get_on_submit_jobs( $num_rows );
        }

        return $this->rucss_manager->get_on_submit_jobs( $num_rows );
    }

    /**
     * Change the job status to be failed.
     *
     * @param array $context Context.
     * @param string $url Url from DB row.
     * @param boolean $is_mobile Is mobile from DB row.
     * @param string $error_code error code.
     * @param string $error_message error message.
     * @return void
     */
    private function make_status_failed( array $context, string $url, bool $is_mobile, string $error_code, string $error_message ): void {
        if ( $context['rucss'] ) {
            $this->rucss_manager
                ->query()
                ->make_status_failed( $url, $is_mobile, $error_code, $error_message );
        }

        if ( $context['atf'] ) {
            $this->atf_manager
                ->query()
                ->make_status_failed( $url, $is_mobile, $error_code, $error_message );
        }
    }

    /**
     * Change the job status to be pending.
     *
     * @param array $context Context.
     * @param string $url Url from DB row.
     * @param string $job_id API job_id.
     * @param string $queue_name API Queue name.
     * @param boolean $is_mobile if the request is for mobile page.
     * @return void
     */
    private function make_status_pending(array $context, string $url, string $job_id, string $queue_name, bool $is_mobile ): void {
        if ( $context['rucss'] ) {
                $this->rucss_manager
                    ->query()
                    ->make_status_pending( $url, $job_id, $queue_name, $is_mobile );
        }

        if ( $context['atf'] ) {
            $this->atf_manager
                    ->query()
                    ->make_status_pending( $url, $job_id, $queue_name, $is_mobile );
        }
    }
}
