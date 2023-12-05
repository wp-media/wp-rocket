<?php

namespace WP_Rocket\Engine\Optimization\RUCSS\Strategy\Strategies;

use WP_Rocket\Engine\Common\JobManager\Interfaces\ManagerInterface;

/**
 * Class managing the retry process of RUCSS whenever a job isn't found in the SaaS.
 */
class JobSetFail implements StrategyInterface {
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
		/**
		 * Unlock preload URL.
		 *
		 * @param string $url URL to unlock
		 */
		do_action( 'rocket_preload_unlock_url', $row_details->url );

		if ( $this->rucss_manager->is_allowed() ) {
			$this->rucss_manager->make_status_failed( $row_details->url, $row_details->is_mobile, strval( $job_details['code'] ), $job_details['message'] );
		}

		if ( $this->atf_manager->is_allowed() ) {
			$this->atf_manager->make_status_failed( $row_details->url, $row_details->is_mobile, strval( $job_details['code'] ), $job_details['message'] );
		}
	}
}
