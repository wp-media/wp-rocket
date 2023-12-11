<?php

namespace WP_Rocket\Engine\Common\JobManager\Strategy\Strategies;

use WP_Rocket\Engine\Common\JobManager\Managers\ManagerInterface;

/**
 * Class managing the retry process whenever a job isn't found in the SaaS.
 */
class JobSetFail implements StrategyInterface {
	/**
	 * Job Manager.
	 *
	 * @var ManagerInterface
	 */
	private $manager;

	/**
	 * Strategy Constructor.
	 *
	 * @param ManagerInterface $manager Job Manager.
	 */
	public function __construct( ManagerInterface $manager ) {
		$this->manager = $manager;
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

		$this->manager->make_status_failed( $row_details->url, $row_details->is_mobile, strval( $job_details['code'] ), $job_details['message'] );
	}
}
