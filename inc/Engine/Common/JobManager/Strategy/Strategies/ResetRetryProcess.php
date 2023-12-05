<?php

namespace WP_Rocket\Engine\Optimization\RUCSS\Strategy\Strategies;

use WP_Rocket\Engine\Common\JobManager\Interfaces\ManagerInterface;

/**
 * Class managing the retry process of RUCSS whenever a job isn't found in the SaaS.
 */
class ResetRetryProcess implements StrategyInterface {
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
	 * Strategy Constructor.
	 *
	 * @param ManagerInterface $rucss_manager RUCSS Job Manager.
     * @param ManagerInterface $lcp_manager LCP Job Manager.
	 */
	public function __construct( ManagerInterface $rucss_manager, ManagerInterface $atf_manager ) {
		$this->rucss_manager = $rucss_manager;
        $this->atf_manager = $atf_manager;
	}


	/**
	 * Execute the strategy process.
	 *
	 * @param object $row_details Row details of the job.
	 * @param array  $job_details Job details from the API.
	 *
	 * @return void
	 */
	public function execute( object $row_details, array $job_details ): void {
		if ( $this->rucss_manager->is_allowed() ) {
			$this->rucss_manager->add_url_to_the_queue( $row_details->url, $row_details->is_mobile );
		}

		if ( $this->atf_manager->is_allowed() ) {
			$this->atf_manager->add_url_to_the_queue( $row_details->url, $row_details->is_mobile );
		}
	}
}
