<?php

namespace WP_Rocket\Engine\Common\JobManager\Managers;

use WP_Rocket\Logger\LoggerAware;
use WP_Rocket\Logger\LoggerAwareInterface;
use WP_Rocket\Engine\Media\AboveTheFold\Database\Queries\AboveTheFold as ATFQuery;
use WP_Rocket\Engine\Common\Context\ContextInterface;

class AtfManager extends AbstractManager implements ManagerInterface, LoggerAwareInterface {
    use LoggerAware;

    /**
	 * AboveTheFold Query instance.
	 *
	 * @var ATFQuery
	 */
	protected $query;

    /**
     * LCP Context.
     *
     * @var ContextInterface
     */
    protected $context;
    
    /**
	 * Instantiate the class.
	 *
	 * @param ATFQuery    $query AboveTheFold Query instance.
     * @param ContextInterface $atf_context Above The Fold Context.
	 */
    public function __construct( ATFQuery $query, ContextInterface $atf_context ) {
        $this->query = $query;
        $this->atf_context = $atf_context;
    }

     /**
     * Log start process of job.
     *
     * @return void
     */
    public function log_start_process(): void {
        if ( ! $this->is_allowed() ) {
            $this->logger::debug( 'ATF: Stop processing cron iteration because option is disabled.' );
			return;
        }

		$this->logger::debug( 'ATF: Start processing pending jobs inside cron.' );
    }

    /**
     * Get pending jobs from db.
     *
     * @param integer $num_rows Number of rows to grab.
     * @return array
     */
    public function get_pending_jobs( int $num_rows ): array {
		$this->logger::debug( "ATF: Start getting number of {$num_rows} pending jobs." );

		$pending_jobs = $this->query->get_pending_jobs( $num_rows );

		if ( ! $pending_jobs ) {
			$this->logger::debug( 'ATF: No pending jobs are there.' );

			return [];
		}
    }

    /**
     * Check SaaS response.
     *
     * @param array Associative array of data required to perform actions:
     *                       - 'id': Row ID of job.
     *                       - 'job_details': Details related to the job.
     *                       - 'row_details': Details related to the row.
     * @return void
     */
    public function process( array $data ) {
        if ( ! $this->is_allowed() ) {
            return;
        }

		// Everything is fine, save LCP & ATF into DB, change status to completed and reset queue_name and job_id.
		$this->logger::debug( 'ATF: Save LCP and ATF for url: ' . $data['row_details']->url );

        $lcp_atf = [
            'lcp' => $data['job_details']['contents']['above_the_fold_result']['lcp'],
            'viewport' => $data['job_details']['contents']['above_the_fold_result']['images_above_fold'],
        ];

		$this->query->make_job_completed( $data['id'], $lcp_atf );
    }
}