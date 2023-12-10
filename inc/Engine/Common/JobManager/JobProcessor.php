<?php

namespace WP_Rocket\Engine\Common\JobManager;

use WP_Rocket\Logger\LoggerAware;
use WP_Rocket\Logger\LoggerAwareInterface;
use WP_Rocket\Engine\Common\JobManager\Managers\ManagerInterface;
use WP_Rocket\Engine\Common\Queue\QueueInterface;
use WP_Rocket\Engine\Common\JobManager\Strategy\Factory\StrategyFactory;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Common\JobManager\APIHandler\APIClient;
use WP_Rocket\Engine\Common\Clock\WPRClock;

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
     * @param ManagerInterface $rucss_manager RUCSS Job Manager.
     * @param ManagerInterface $lcp_manager LCP Job Manager.
	 * @param QueueInterface   $queue Queue instance.
	 * @param StrategyFactory  $strategy_factory Strategy Factory used for RUCSS retry process.
	 * @param Options_Data     $options Options instance.
	 * @param APIClient        $api APIClient instance.
	 * @param WPRClock         $clock Clock object instance.
     */
    public function __construct(
        ManagerInterface $rucss_manager,
        ManagerInterface $atf_manager,
        QueueInterface $queue,
        StrategyFactory $strategy_factory,
		Options_Data $options,
		APIClient $api,
		WPRClock $clock
    ) {
        $this->rucss_manager = $rucss_manager;
        $this->atf_manager = $atf_manager;
        $this->queue = $queue;
        $this->strategy_factory = $strategy_factory;
		$this->options = $options;
		$this->api = $api;
		$this->wpr_clock = $clock;
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

        $this->logger::debug( 'Start processing pending jobs inside cron.' );

        // Get some items from the DB with status=pending & job_id isn't empty.

        /**
         * Filters the pending jobs count.
         *
         * @since 3.11
         *
         * @param int $rows Number of rows to grab with each CRON iteration.
         */
        $rows = apply_filters( 'rocket_rucss_pending_jobs_cron_rows_count', 100 );

        $pending_jobs = $this->get_jobs( $context, $rows, 'pending' );

        if ( ! $pending_jobs ) {
            return;
        }

        foreach ( $pending_jobs as $row ) {
            $current_time = $this->wpr_clock->current_time( 'timestamp', true );
            if (  $row->next_retry_time < $current_time ) {
                $optimization_type = $this->get_optimization_type( $row );
                // Change status to in-progress.
                $this->make_status_inprogress( $row->url, $row->is_mobile, $optimization_type );
                $this->queue->add_job_status_check_async( $row->url, $row->is_mobile, $optimization_type );
            }
        }
    }

    /**
     * Check job status by DB row ID.
     *
     * @param string $url Url from DB row.
	 * @param boolean $is_mobile Is mobile from DB row.
	 * @param string $optimization_type The type of optimization request to send.
     *
     * @return void
     */
    public function check_job_status( string $url, bool $is_mobile, string $optimization_type ) {

		$is_shakedCSS_valid = true;
		$context = $this->context();

        $row_details = $this->get_single_job( $url, $is_mobile, $optimization_type );
        if ( ! $row_details ) {
            $this->logger::debug( 'RUCSS/ATF: Url not found for is_mobile -  ' . (int) $is_mobile );

            // Nothing in DB, bailout.
            return;
        }

        // Send the request to get the job status from SaaS.
        $job_details = $this->api->get_queue_job_status( $row_details->job_id, $row_details->queue_name, $this->is_home( $row_details->url ) );

		// If optimization type is for both rucss and atf or exclusive to only rucss.
		if ( 'all' === $optimization_type || 'rucss' === $optimization_type ) {
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
				$this->rucss_manager->make_status_failed( $row_details->url, $row_details->is_mobile, '500', $message );

				if ( 'rucss' === $optimization_type ) {
					return;
				}

				$is_shakedCSS_valid = false;
			}
		}
        
        if (
            200 !== (int) $job_details['code']
        ) {
            $this->logger::debug( 'RUCSS/ATF: Job status failed for url: ' . $row_details->url, $job_details );
			$this->decide_strategy( $row_details, $job_details, $optimization_type );

            return;
        }
        /**
         * Unlock preload URL.
         *
         * @param string $url URL to unlock
         */
        do_action( 'rocket_preload_unlock_url', $row_details->url );

		if ( $is_shakedCSS_valid ) {
			$this->rucss_manager->process( $job_details, $row_details, $optimization_type );
		}
        
        $this->atf_manager->process( $job_details, $row_details, $optimization_type );
    }

	/**
	 * Handle job status by DB row ID during upgrade from versions < 3.16.
	 *
	 * @param integer $row_id DB Row ID.
	 * @return void
	 */
	public function handle_old_rucss_job( int $row_id ): void {
		$row = $this->rucss_manager->query()->get_row_by_id( $row_id );

		if ( ! $row ) {
			return;
		}

		$this->check_job_status( $row->url, $row->is_mobile, 'rucss' );
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
        $rows             = $this->get_jobs( $context, $max_pending_rows, 'submit' );

		if ( ! $rows ) {
			return;
		}

        foreach ( $rows as $row ) {
			$optimization_type = $this->get_optimization_type( $row );
            $response = $this->send_api( $row->url, (bool) $row->is_mobile, $optimization_type );

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

		$config = $this->set_request_params( $config, $optimization_type );

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
		
		$this->atf_manager->clear_failed_jobs( $value, $unit );
	}

	/**
	 * Set request parameters
	 *
	 * @param array $config Array of request parameters.
	 * @param string $optimization_type The type of optimization applied for the current job.
	 * @return array
	 */
	private function set_request_params( array $config, string $optimization_type ): array {
		switch ( $optimization_type ) {
			case 'rucss':
				$config['optimization_list'][] = 'rucss';
				break;
			case 'atf':
				$config['optimization_list'][] = 'lcp';
				$config['optimization_list'][] = 'above_fold';
				break;
			default:
				$config['optimization_list'][] = 'rucss';
				$config['optimization_list'][] = 'lcp';
				$config['optimization_list'][] = 'above_fold';
		}

		return $config;
	}

	/**
	 * Check if current page is the home page.
	 *
	 * @param string $url Current page url.
	 *
	 * @return bool
	 */
	private function is_home( string $url ): bool {
		/**
		 * Filters the home url.
		 *
		 * @since 3.11.4
		 *
		 * @param string  $home_url home url.
		 * @param string  $url url of current page.
		 */
		$home_url = apply_filters( 'rocket_rucss_is_home_url', home_url(), $url );
		return untrailingslashit( $url ) === untrailingslashit( $home_url );
	}
	

    /**
     * Change the status to be in-progress.
     *
     * @param string $url Url from DB row.
     * @param boolean $is_mobile Is mobile from DB row.
	 * @param string $optimization_type The type of optimization applied for the current job.
     * @return void
     */
    private function make_status_inprogress( string $url, bool $is_mobile, string $optimization_type ): void {
		$this->rucss_manager->make_status_inprogress( $url, $is_mobile, $optimization_type );
		$this->atf_manager->make_status_inprogress( $url, $is_mobile, $optimization_type );
    }

	/**
     * Get single job.
     *
     * @param string $url Url from DB row.
     * @param boolean $is_mobile Is mobile from DB row.
	 * @param string $optimization_type The type of optimization applied for the current job.
	 * 
     * @return bool|object
     */
    private function get_single_job( string $url, bool $is_mobile, string $optimization_type ) {
		$job = [];

		switch ( $optimization_type ) {
			case 'rucss':
				$this->logger::debug( 'RUCSS: Start checking job status for url: ' . $url );
				$job = $this->rucss_manager->get_single_job( $url, $is_mobile );
				break;
			case 'atf':
				$this->logger::debug( 'ATF: Start checking job status for url: ' . $url );
            	$job = $this->atf_manager->get_single_job( $url, $is_mobile );
				break;
			default:
				$this->logger::debug( 'RUCSS/ATF: Start checking job status for url: ' . $url );
				$job = $this->rucss_manager->get_single_job( $url, $is_mobile );
		}

		if ( ! $job ) {
			return [];
		}

		return $job;
    }

	/**
	 * Decide jobs to get.
	 *
	 * @param array $context Context.
	 * @param integer $num_rows Number of rows to grab with each CRON iteration.
	 * @param string $type Type of job to get.
	 * @return array
	 */
	public function get_jobs( array $context, int $num_rows, string $type ): array {
		$allowed_types = [ 'pending', 'submit' ];

		if ( ! in_array( $type, $allowed_types ) ) {
			return [];
		}

		$rucss_jobs = $atf_jobs = [];

		switch ( $type ) {
			case 'pending':
				if ( $context['rucss'] ) {
					$rucss_jobs = $this->rucss_manager->get_pending_jobs( $num_rows );
				}
		
				if ( $context['atf'] ) {
					$atf_jobs = $this->atf_manager->get_pending_jobs( $num_rows );
				}
				break;
			case 'submit':
				if ( $context['rucss'] ) {
					$rucss_jobs = $this->rucss_manager->get_on_submit_jobs( $num_rows );
				}
		
				if ( $context['atf'] ) {
					$atf_jobs = $this->atf_manager->get_on_submit_jobs( $num_rows );
				}
				break;
		}

		$rows = array_merge( $rucss_jobs, $atf_jobs );

		if ( ! $rows ) {
			return [];
		}

		// Get distinct rows.
		return $this->get_distinct( $rows );
	}

	/**
	 * Get rows common to rucss & atf.
	 *
	 * @param array $rows Merged DB Rows of rucss & atf.
	 * @return array
	 */
	private function get_common_jobs( array $rows ): array {
		$occurrences = $duplicates = [];

		foreach ( $rows as $row ) {
			$key = $row->url . '|' . ( (bool) $row->is_mobile ?? 'null' );
		
			if ( ! isset( $occurrences[ $key ] ) ) {
				$occurrences[ $key ] = 1;
				
				continue;
			}
			
			$occurrences[ $key ]++;
		
			if ( $occurrences[ $key ] === 2 ) {
				// Add new is_common property to the object and add object to duplicate.
				$row->is_common = true;
				$duplicates[] = $row;
			}
		}

		return $duplicates;
	}

	/**
	 * Get distinct rows merged from both rucss & atf.
	 *
	 * @param array $rows Merged DB Rows of rucss & atf.
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
			foreach( $common_rows as $common_row ){
				if ( $row->url === $common_row->url && (bool) $row->is_mobile === (bool) $common_row->is_mobile ) {
					// Remove the common row that is without the new is_common property.
					unset( $rows[ $index ] );
				}
			}
			
			$index++;
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

		if ( ! isset( $row->is_common ) ) {
			$optimization_type = isset( $row->lcp ) ? 'atf' : 'rucss';
		}

		return $optimization_type;
	}

	/**
	 * Decide with job strategy to apply based on the optimization type.
	 *
	 * @param object $row_details DB Row of job.
	 * @param array $job_details Job details from the API.
	 * @param string $optimization_type The type of optimization applied for the current job.
	 * @return void
	 */
	private function decide_strategy( $row_details, array $job_details, string $optimization_type ): void {
		switch ( $optimization_type ) {
			case 'rucss':
				$this->strategy_factory->manage( $row_details, $job_details, $this->rucss_manager );
				break;
			case 'atf':
				$this->strategy_factory->manage( $row_details, $job_details, $this->atf_manager );
				break;
			default:
				$this->strategy_factory->manage( $row_details, $job_details, $this->rucss_manager );
				$this->strategy_factory->manage( $row_details, $job_details, $this->atf_manager );
		}
	}

    /**
     * Change the job status to be failed.
     *
     * @param string $url Url from DB row.
     * @param boolean $is_mobile Is mobile from DB row.
     * @param string $error_code error code.
     * @param string $error_message error message.
	 * @param string $optimization_type The type of optimization applied for the current job.
     * @return void
     */
    private function make_status_failed( string $url, bool $is_mobile, string $error_code, string $error_message, $optimization_type ): void {
		$this->rucss_manager->make_status_failed( $url, $is_mobile, $error_code, $error_message, $optimization_type );
		$this->atf_manager->make_status_failed( $url, $is_mobile, $error_code, $error_message, $optimization_type );
    }

    /**
     * Change the job status to be pending.
     *
     * @param string $url Url from DB row.
     * @param string $job_id API job_id.
     * @param string $queue_name API Queue name.
     * @param boolean $is_mobile if the request is for mobile page.
	 * @param string $optimization_type The type of optimization applied for the current job.
     * @return void
     */
    private function make_status_pending( string $url, string $job_id, string $queue_name, bool $is_mobile, string $optimization_type ): void {
		$this->rucss_manager->make_status_pending( $url, $job_id, $queue_name, $is_mobile, $optimization_type );
		$this->atf_manager->make_status_pending( $url, $job_id, $queue_name, $is_mobile, $optimization_type );
    }
}
