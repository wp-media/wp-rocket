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
     * The type of optimization applied for the current job.
     *
     * @var string
     */
    protected $optimization_type = 'atf';
    
    /**
	 * Instantiate the class.
	 *
	 * @param ATFQuery    $query AboveTheFold Query instance.
     * @param ContextInterface $context Above The Fold Context.
	 */
    public function __construct( ATFQuery $query, ContextInterface $context ) {
        $this->query = $query;
        $this->context = $context;
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

        return $pending_jobs;
    }

    /**
	  * Process SaaS response.
	  *
	  * @param array $job_details Details related to the job..
	  * @param object $row_details Details related to the row.
      * @param string $optimization_type The type of optimization applied for the current job.
	  * @return void
	  */
    public function process( array $job_details, $row_details, string $optimization_type ): void {
        if ( ! $this->is_allowed( $optimization_type ) ) {
            return;
        }

		// Everything is fine, save LCP & ATF into DB, change status to completed and reset queue_name and job_id.
		$this->logger::debug( 'ATF: Save LCP and ATF for url: ' . $row_details->url );

        $lcp_atf = [
            'lcp' => json_encode( $job_details['contents']['above_the_fold_result']['lcp'], JSON_UNESCAPED_SLASHES ),
            'viewport' =>json_encode( $job_details['contents']['above_the_fold_result']['images_above_fold'], JSON_UNESCAPED_SLASHES ),
        ];

		$this->query->make_job_completed( $row_details->url, $row_details->is_mobile, $lcp_atf );
    }
}