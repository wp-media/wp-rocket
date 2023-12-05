<?php

namespace WP_Rocket\Engine\Common\JobManager\Managers;

use WP_Rocket\Logger\LoggerAware;
use WP_Rocket\Logger\LoggerAwareInterface;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Queries\UsedCSS as UsedCSS_Query;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\Filesystem;
use WP_Rocket\Engine\Common\Context\ContextInterface;

class RUCSSManager extends AbstractManager implements ManagerInterface, LoggerAwareInterface {
    use LoggerAware;
    use CSSTrait;

    /**
	 * UsedCss Query instance.
	 *
	 * @var UsedCSS_Query
	 */
	protected $query;

    /**
	 * Filesystem instance
	 *
	 * @var Filesystem
	 */
	private $filesystem;

	/**
     * RUCSS Context.
     *
     * @var ContextInterface
     */
    protected $context;

    /**
	 * Instantiate the class.
	 *
	 * @param UsedCSS_Query    $query Usedcss Query instance.
     * @param Filesystem       $filesystem Filesystem instance.
	 * @param ContextInterface $rucss_context RUCSS Context.
	 */
    public function __construct(
		UsedCSS_Query $query,
		Filesystem $filesystem,
		ContextInterface $context
	) {
        $this->query = $query;
        $this->filesystem = $filesystem;
		$this->context = $context;
    }

	 /**
     * Log start process of job.
     *
     * @return void
     */
	public function log_start_process(): void {
        if ( ! $this->is_allowed() ) {
            $this->logger::debug( 'RUCSS: Stop processing cron iteration because option is disabled.' );

			return;
        }

		$this->logger::debug( 'RUCSS: Start processing pending jobs inside cron.' );
    }

    /**
     * Get pending jobs from db.
     *
     * @param integer $num_rows Number of rows to grab.
     * @return array
     */
    public function get_pending_jobs( int $num_rows ): array {
		$this->logger::debug( "RUCSS: Start getting number of {$num_rows} pending jobs." );

		$pending_jobs = $this->query->get_pending_jobs( $num_rows );

		if ( ! $pending_jobs ) {
			$this->logger::debug( 'RUCSS: No pending jobs are there.' );

			return [];
		}
    }

	 /**
	  * Process SaaS response.
	  *
	  * @param array $job_details Details related to the job..
	  * @param object $row_details Details related to the row.
	  * @return void
	  */
    public function process( array $job_details, $row_details ): void {
		if ( ! $this->is_allowed() ) {
			return;
		}

        $css = $this->apply_font_display_swap( $job_details['contents']['rucss_result']['shakedCSS'] );

		/**
		 * RUCSS hash.
		 *
		 * @param string $hash RUCSS hash.
		 * @param string $css RUCSS content.
		 * @param UsedCSSRow $row_details Job details.
		 */
		$hash = (string) apply_filters( 'rocket_rucss_hash',  md5( $css ), $css, $row_details );

		if ( ! $this->filesystem->write_used_css( $hash, $css ) ) {
			$message = 'RUCSS: Could not write used CSS to the filesystem: ' . $row_details->url;
			$this->logger::error( $message );
			$this->query->make_status_failed( $row_details->url, $row_details->is_mobile, '', $job_details['message'] );

			return;
		}

		// Everything is fine, save the usedcss into DB, change status to completed and reset queue_name and job_id.
		$this->logger::debug( 'RUCSS: Save used CSS for url: ' . $row_details->url );
		$this->query->make_status_completed( $row_details->url, $row_details->is_mobile, $hash );

		/**
		 * Fires after successfully saving the used CSS for an URL
		 *
		 * @param string $url URL used to generated the used CSS.
		 * @param array  $job_details Result of the request to get the job status from SaaS.
		 */
		do_action( 'rocket_rucss_complete_job_status', $row_details->url, $job_details );
    }
}