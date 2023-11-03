<?php

namespace WP_Rocket\Engine\Optimization\RUCSS\Strategy\Strategies;

use WP_Rocket\Engine\Optimization\RUCSS\Database\Queries\UsedCSS as UsedCSS_Query;

/**
 * Class managing the retry process of RUCSS whenever a job isn't found in the SaaS.
 */
class JobSetFail implements StrategyInterface {
	/**
	 * UsedCss Query instance.
	 *
	 * @var UsedCSS_Query
	 */
	protected $used_css_query;

	/**
	 * Strategy Constructor.
	 *
	 * @param UsedCSS_Query $used_css_query DB Table.
	 */
	public function __construct( UsedCSS_Query $used_css_query ) {
		$this->used_css_query = $used_css_query;
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

		$this->used_css_query->make_status_failed( $row_details->id, strval( $job_details['code'] ), $job_details['message'] );
	}
}
