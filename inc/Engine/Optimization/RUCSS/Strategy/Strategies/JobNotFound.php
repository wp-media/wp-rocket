<?php

namespace WP_Rocket\Engine\Optimization\RUCSS\Strategy\Strategies;

use WP_Rocket\Engine\Optimization\RUCSS\Database\Queries\UsedCSS as UsedCSS_Query;

/**
 * Class managing the retry process of RUCSS whenever a job isn't found in the SaaS.
 */
class JobNotFound implements StrategyInterface {
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
		// In case the job has not been found on the SaaS, the plugin must retry once, but not more.

		$this->add_url_to_the_queue( $row_details->url, (bool) $row_details->is_mobile );
	}

	/**
	 * Send the request to add url into the queue.
	 *
	 * @param string $url page URL.
	 * @param bool   $is_mobile page is for mobile.
	 *
	 * @return void
	 */
	public function add_url_to_the_queue( string $url, bool $is_mobile ) {
		$used_css_row = $this->used_css_query->get_row( $url, $is_mobile );
		if ( empty( $used_css_row ) ) {
			$this->used_css_query->create_new_job( $url, '', '', $is_mobile );
			return;
		}
		$this->used_css_query->reset_job( (int) $used_css_row->id );
	}
}
